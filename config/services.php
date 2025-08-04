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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vnpay' => [
        'tmn_code'    => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url'         => env('VNPAY_URL'),
        'return_url'  => env('VNPAY_RETURN_URL'),
        'ipn_url'     => env('VNPAY_IPN_URL'),
        'wallet_return_url' => env('VNPAY_WALLET_RETURN_URL'),
        'wallet_ipn_url'    => env('VNPAY_WALLET_IPN_URL'),
    ],
    'zalopay' => [
        'app_id'         => env('ZALOPAY_APP_ID'),
        'key1'           => env('ZALOPAY_KEY1'),
        'key2'           => env('ZALOPAY_KEY2'),
        'callback_url'   => env('ZALOPAY_CALLBACK_URL'),
        'redirect_url'   => env('ZALOPAY_REDIRECT_URL'),
    ],



];
