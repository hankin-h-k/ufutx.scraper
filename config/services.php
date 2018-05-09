<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
	'github' => [
        'client_id' => env('GITHUB_KEY', '198b84b724b63fb2f461'), 
        'client_secret' => env('GITHUB_SECRET', '54d9ab8c43785154302313075166af7a4320c3c1'),
        'redirect' => env('GITHUB_REDIRECT_URI', 'http://library.glore/auth/github/callback'),
	],
	'ufutx' => [
        'client_id' => env('UFUTX_KEY', 1), 
        'client_secret' => env('UFUTX_SECRET', 'qqPBwRuVyo3NKPDsD35cw4uO6ULVL7Gsk6CDV2FB'),
        'redirect' => env('UFUTX_REDIRECT_URI', 'http://library.glore/auth/ufutx/callback'),
	],

];
