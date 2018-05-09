<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
    
        'oss' => [
            'driver' => 'oss',
            'access_id' => env('ALIYUN_OSS_KEY', 'HhMAugGujLx0VHwv'),
            'access_key' => env('ALIYUN_OSS_SECRET', '7MCgNj0fuGprDSb8SxgOgcSv8w8wWR'),
            'bucket' => env('ALIYUN_OSS_BUCKET', 'local-pictures'),
            'endpoint' => env('ALIYUN_OSS_ENDPOINT', 'oss-cn-shenzhen.aliyuncs.com'),
            'cdnDomain' => env('ALIYUN_OSS_CDNDOMAIN', 'local-pictures.img-cn-shenzhen.aliyuncs.com'),
            'ssl' => env('ALIYUN_OSS_SSL', false),
            'isCName' => env('ALIYUN_OSS_ISCNAME', true),
            'debug' => env('ALIYUN_OSS_DEBUG', true),
        ],


    ],

];
