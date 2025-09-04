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
        $this->info('Iniciando migración de archivos .xml y .zip a Google Drive...');

        // Extensiones de archivos a migrar
        $allowedExtensions = ['xml', 'zip'];

        // Migrar archivos del directorio app
        $this->migrateFromDirectory('app', $allowedExtensions, config('filesystems.disks.google.folderId', 'root'));
        
        // Migrar archivos del directorio public
        $this->migrateFromDirectory('public', $allowedExtensions, config('filesystems.disks.google_public.folderId', 'root'));

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

    protected function migrateFromDirectory($directoryName, $allowedExtensions, $googleDriveFolderId)
    {
        $this->info("Migrando archivos ." . implode(', .', $allowedExtensions) . " del directorio {$directoryName}...");

        // Inicializar el servicio de Google Drive para este directorio
        $this->initializeGoogleDrive($googleDriveFolderId);

        // Crear la carpeta raíz en Google Drive
        $rootFolderId = $this->createDirectoryInGoogleDrive($directoryName, $this->rootFolderId);
        $this->folderIds = [$directoryName => $rootFolderId]; // Reset folder IDs for this directory

        // Determinar la ruta local
        $localPath = $directoryName === 'public' ? public_path() : storage_path($directoryName);
        
        if (!is_dir($localPath)) {
            $this->warn("El directorio {$directoryName} no existe localmente, saltando...");
            return;
        }

        $files = $this->getAllFiles($localPath);
        $today = now()->startOfDay();

        foreach ($files as $file) {
            // Filtrar solo archivos con las extensiones permitidas
            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                continue;
            }

            // Solo migrar archivos del día actual
            if (filemtime($file) < $today->getTimestamp()) {
                continue;
            }
            
            $relativePath = $this->getRelativePath($file, $directoryName);
            $this->info("Migrando archivo: {$relativePath}");

            try {
                // Crear estructura de directorios si no existe
                $parentPath = dirname($relativePath);
                $this->ensureDirectoryExists($parentPath);

                $content = file_get_contents($file);
                $parentId = $this->folderIds[$parentPath] ?? $this->folderIds[$directoryName];

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

                // Eliminar el archivo local después de la migración exitosa
                if ($uploadedFile && $uploadedFile->id) {
                    unlink($file);
                    $this->info("✓ Archivo .{$fileExtension} migrado y eliminado localmente: {$relativePath}");
                } else {
                    $this->error("✗ Error: No se pudo confirmar la migración del archivo {$relativePath}");
                }
        
            } catch (\Exception $e) {
                $this->error("✗ Error al migrar archivo {$relativePath}: " . $e->getMessage());
                $this->error("  El archivo local se mantiene sin cambios por seguridad");
            }
        }
    }

    protected function ensureDirectoryExists($path)
    {
        if (isset($this->folderIds[$path])) {
            return;
        }

        $pathParts = explode('/', $path);
        $currentPath = '';
        $currentParentId = $this->rootFolderId;

        foreach ($pathParts as $part) {
            if (empty($part)) continue;
            
            $currentPath = $currentPath ? $currentPath . '/' . $part : $part;
            
            if (!isset($this->folderIds[$currentPath])) {
                $currentParentId = $this->createDirectoryInGoogleDrive($part, $currentParentId);
                $this->folderIds[$currentPath] = $currentParentId;
            } else {
                $currentParentId = $this->folderIds[$currentPath];
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

    protected function getRelativePath($path, $directoryName = 'app')
    {
        // Convertir la ruta a formato Unix
        $path = str_replace('\\', '/', $path);
        
        if ($directoryName === 'public') {
            // Para archivos públicos, obtener la ruta relativa después del directorio public
            $publicPath = str_replace('\\', '/', public_path());
            $relativePath = str_replace($publicPath . '/', '', $path);
            return 'public/' . $relativePath;
        } else {
            // Para archivos de storage, obtener la parte relativa después de 'storage/app/'
            $parts = explode('storage/app/', $path);
            if (count($parts) > 1) {
                return 'app/' . $parts[1];
            }
        }
        
        return $path;
    }
} 