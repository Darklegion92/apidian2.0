<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use League\Flysystem\Filesystem;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Storage::extend('google', function ($app, $config) {
            $client = new Google_Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);

            $service = new Google_Service_Drive($client);
            
            // Si no se especifica un folderId, usar la raíz
            $folderId = $config['folderId'] ?? 'root';
            
            $adapter = new GoogleDriveAdapter($service, $folderId, [
                'useDisplayPaths' => false,
                'caseSensitive' => false,
                'pathPrefix' => $config['pathPrefix'] ?? '',
                'rootFolderId' => $folderId,
                'normalizePath' => true,
                'createFolder' => true,
                'useAbsolutePath' => false
            ]);

            return new Filesystem($adapter);
        });
    }
} 