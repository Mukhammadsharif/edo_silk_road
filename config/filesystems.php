<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'internal' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_INTERNAL_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'ftp_13_old' => [
            'driver'   => 'ftp',
            'host'     => '172.17.110.44',
            'username' => 'ftp_muksid',
            'password' => 'ftp_muksid',
        ],

        'ftp' => [
            'driver'   => 'ftp',
            'host'     => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'root'     => env('FTP_ROOT', '/'),
            'port'     => 21,
            'passive'  => true,
            'ssl'      => false,
            'timeout'  => 30,
        ],

        'ftp_13' => [
            'driver'   => 'ftp',
            'host'     => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            // Своя переменная: диск 'ftp' выше использует FTP_ROOT с другим
            // дефолтом ('/'), поэтому одной переменной их не настроить независимо.
            // FTP_ROOT оставлен как запасной вариант для совместимости.
            //
            // Если FTP-аккаунт логинится сразу внутрь каталога файлов, здесь
            // нужно указать '/', иначе Flysystem падает с
            // UnableToResolveConnectionRoot: /files
            'root'     => env('FTP_FILES_ROOT', env('FTP_ROOT', '/files')),
            'port'     => (int) env('FTP_PORT', 21),
            'passive'  => (bool) env('FTP_PASSIVE', true),
            'ssl'      => (bool) env('FTP_SSL', false),
            'timeout'  => (int) env('FTP_TIMEOUT', 30),

            // Без throw=true Storage::put() при сбое FTP молча вернёт false,
            // код продолжится и создаст запись в document_files для файла,
            // которого на FTP нет: пользователь видит «сохранено», а при
            // открытии получает ошибку. Лучше явная ошибка, чем потеря файла.
            'throw'    => (bool) env('FTP_THROW', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
