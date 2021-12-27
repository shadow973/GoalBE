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

    'facebook' => [
        'client_id' =>  env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect' => env('fb_callback')
    ],

//    'google' => [
//        'client_id' => '564900905005-8vmk9qndoabmpt25ifvmnpieiebh1ni2.apps.googleusercontent.com',
//        'client_secret' => 'fU142R5Wz00byGxDgquAfh-c',
//        'redirect' => env('google_callback')
//    ],

    // 'facebook' => [
    //     'client_id' => env('FACEBOOK_APP_ID'),
    //     'client_secret' => env('FACEBOOK_APP_SECRET'),
    //     'redirect' => env('FACEBOOK_REDIRECT'),
    // ],

];
