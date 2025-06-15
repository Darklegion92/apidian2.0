<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Google_Client;
use Google_Service_Drive;

class MigrateToGoogleDrive extends Command
{
    protected $signature = 'storage:migrate-to-google';
    protected $description = 'Migra los archivos del storage local a Google Drive';

    protected $driveService;
    protected $rootFolderId;
    protected $folderIds = [];

    public function handle()
    {
        $this->info('Iniciando migración a Google Drive...');

        // Inicializar el servicio de Google Drive
        $this->initializeGoogleDrive(config('filesystems.disks.google.folderId', 'root'));

        // Crear la carpeta app en Google Drive (equivalente a storage/app)
        $appFolderId = $this->createDirectoryInGoogleDrive('app', $this->rootFolderId);
        $this->folderIds['app'] = $appFolderId;

        // Directorios a migrar
        $directories = ['certificates', 'xml', 'zip'];

        // Crear estructura de carpetas en Google Drive
        foreach ($directories as $directory) {
            $this->createDirectoryStructure($directory, $appFolderId);
        }

        // Migrar archivos
        foreach ($directories as $directory) {
            $this->migrateDirectory($directory);
        }

        $this->handlePublic();
        $this->info('Migración completada exitosamente!');

    }

    public function handlePublic()
    {
        $this->info('Iniciando migración a Google Drive...');

        // Inicializar el servicio de Google Drive
        $this->initializeGoogleDrive(config('filesystems.disks.google_public.folderId', 'root'));

        // Crear la carpeta app en Google Drive (equivalente a storage/app)
        $appFolderId = $this->createDirectoryInGoogleDrive('app', $this->rootFolderId);
        $this->folderIds['app'] = $appFolderId;

        // Directorios a migrar
        $directories = ['public'];

        // Crear estructura de carpetas en Google Drive
        foreach ($directories as $directory) {
            $this->createDirectoryStructure($directory, $appFolderId);
        }

        // Migrar archivos
        foreach ($directories as $directory) {
            $this->migrateDirectory($directory);
        }

        $this->info('Migración completada exitosamente!');
    }

    protected function initializeGoogleDrive($folderId)
    {
        $client = new Google_Client();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->refreshToken(config('filesystems.disks.google.refreshToken'));

        $this->driveService = new Google_Service_Drive($client);
        $this->rootFolderId = $folderId;
    }

    protected function createDirectoryStructure($directory, $parentId)
    {
        $localPath = storage_path("app/{$directory}");
        if (!is_dir($localPath)) {
            $this->warn("El directorio {$directory} no existe localmente, saltando...");
            return;
        }

        $this->info("Creando estructura de directorios para: app/{$directory}");
        
        // Crear el directorio principal
        $currentParentId = $this->createDirectoryInGoogleDrive($directory, $parentId);
        $this->folderIds["app/{$directory}"] = $currentParentId;

        // Crear subdirectorios recursivamente
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $relativePath = $this->getRelativePath($item->getPathname());
                $pathParts = explode('/', $relativePath);
                $currentPath = 'app';
                $currentParentId = $this->folderIds['app'];

                foreach ($pathParts as $part) {
                    if ($part !== 'app') {
                        $currentPath .= '/' . $part;
                        if (!isset($this->folderIds[$currentPath])) {
                            $currentParentId = $this->createDirectoryInGoogleDrive($part, $currentParentId);
                            $this->folderIds[$currentPath] = $currentParentId;
                        } else {
                            $currentParentId = $this->folderIds[$currentPath];
                        }
                    }
                }
            }
        }
    }

    protected function createDirectoryInGoogleDrive($name, $parentId)
    {
        try {
            // Escapar caracteres especiales en el nombre
            $escapedName = addslashes($name);
            
            // Buscar si el directorio ya existe
            $query = "name = '{$escapedName}' and mimeType = 'application/vnd.google-apps.folder' and '{$parentId}' in parents and trashed = false";
            $result = $this->driveService->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive'
            ]);

            if (count($result->getFiles()) > 0) {
                $this->info("✓ El directorio {$name} ya existe");
                return $result->getFiles()[0]->getId();
            }

            // Crear el directorio
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId]
            ]);

            $file = $this->driveService->files->create($fileMetadata, [
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);

            $this->info("✓ Directorio {$name} creado exitosamente");
            return $file->id;
        } catch (\Exception $e) {
            $this->error("✗ Error al crear directorio {$name}: " . $e->getMessage());
            return $parentId;
        }
    }

    protected function migrateDirectory($directory)
    {
        $this->info("Migrando directorio: app/{$directory}");

        $localPath = storage_path("app/{$directory}");
        if (!is_dir($localPath)) {
            $this->warn("El directorio {$directory} no existe localmente, saltando...");
            return;
        }

        $files = $this->getAllFiles($localPath);

        foreach ($files as $file) {
            $relativePath = $this->getRelativePath($file);
            $this->info("Migrando archivo: {$relativePath}");

            try {
                $content = file_get_contents($file);
                $parentPath = dirname($relativePath);
                $parentId = $this->folderIds[$parentPath] ?? $this->folderIds['app'];

                $fileMetadata = new \Google_Service_Drive_DriveFile([
                    'name' => basename($relativePath),
                    'parents' => [$parentId]
                ]);

                $uploadedFile = $this->driveService->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => mime_content_type($file),
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                    'supportsAllDrives' => true
                ]);

                $this->info("✓ Archivo migrado exitosamente");
            } catch (\Exception $e) {
                $this->error("✗ Error al migrar archivo {$relativePath}: " . $e->getMessage());
            }
        }
    }

    protected function getAllFiles($directory)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function getRelativePath($path)
    {
        // Convertir la ruta a formato Unix
        $path = str_replace('\\', '/', $path);
        
        // Obtener la parte relativa después de 'storage/app/'
        $parts = explode('storage/app/', $path);
        if (count($parts) > 1) {
            return 'app/' . $parts[1];
        }
        
        return $path;
    }
} 