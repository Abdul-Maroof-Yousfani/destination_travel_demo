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

    'agency' => [
        'name' => env('AGENCY_NAME'),
        'email' => env('AGENCY_EMAIL'),
    ],

    'flyjinnah_api' => [
        'authenticate' => env('FLYJINNAH_API_AUTHENTICATE'),
        'search' => env('FLYJINNAH_API_SEARCH'),
        'flight_details' => env('FLYJINNAH_API_FLIGHT_DETAILS'),
        'auth_username' => env('FLYJINNAH_API_AUTH_USERNAME'),
        'auth_password' => env('FLYJINNAH_API_AUTH_PASSWORD'),
        'username' => env('FLYJINNAH_API_USERNAME'),
        'password' => env('FLYJINNAH_API_PASSWORD'),
        'agent_code' => env('FLYJINNAH_AGENT_CODE'),
    ],

    'pia_api' => [
        'url' => env('PIA_API_URL'),
        'username' => env('PIA_API_USERNAME'),
        'password' => env('PIA_API_PASSWORD'),
        'email' => env('PIA_API_EMAIL'),
        'name' => env('PIA_API_NAME'),
        'doc_type' => env('PIA_API_DOC_TYPE'),
        'inv_no' => env('PIA_API_INV_NO'),
    ],

    'emirates_api' => [
        'url' => env('EMIRATES_API_URL', 'https://ek.farelogix.com:443/sandbox-uat/oc'),
        'role' => env('EMIRATES_ROLE', 'Ticketing Agent'),
        'agency_name' => env('EMIRATES_AGENCY_NAME', 'travelandtour-ek-dispatch.flxdm'),
        'user' => env('EMIRATES_USER', 'otadestinations'),
        'u' => env('EMIRATES_U', 'otadestinations'),
        'passwordIden' => env('EMIRATES_PASSWORD_IDEN', 'Paktg24580'),
        'agtPassword' => env('EMIRATES_PASSWORD_AGT', 'Paktg24580'),
        'agency_id' => env('EMIRATES_AGENCY_ID', '27301245'),
        'agy' => env('EMIRATES_AGY', '27301245'),
        'subscription_key' => env('EMIRATES_SUBSCRIPTION_KEY', 'ec71e1de4e224e82bac30f5a3c4c2803'),
        'pcc' => env('EMIRATES_PCC', 'ETXO')
    ],

    'airblue' => [
        'url'           => env('AIRBLUE_URL'),
        'service_target'=> env('AIRBLUE_SERVICE_TARGET'),
        'client_id'     => env('AIRBLUE_CLIENT_ID'),
        'client_key'    => env('AIRBLUE_CLIENT_KEY'),
        'agent_id'      => env('AIRBLUE_AGENT_ID'),
        'agent_password'=> env('AIRBLUE_AGENT_PASSWORD'),
        'cert'          => env('AIRBLUE_CERT_PATH'),
        'ssl_key'       => env('AIRBLUE_KEY_PATH'),
    ],
];
