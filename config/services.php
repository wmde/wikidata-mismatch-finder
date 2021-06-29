<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mediawiki' => [
        'identifier' => env('MEDIAWIKI_OAUTH_CLIENT_ID'), // oauth client id
        'secret' => env('MEDIAWIKI_OAUTH_CLIENT_SECRET'), // oauth client secret

        'callback_uri' => env('MEDIAWIKI_OAUTH_CALLBACK_URL'), // redirect url
        'base_url' => env('MEDIAWIKI_OAUTH_BASE_URL'), // base url of wiki, for example https://meta.wikimedia.org
    ],

];
