<?php

return [
    /*
     * ------------------------------------------------------------------------
     * Firebase API Keys
     * ------------------------------------------------------------------------
     *
     * Ключи, необходимые для работы с Firebase Cloud Messaging.
     *
     */
    'api_key' => env('FCM_SERVER_KEY', ''),
    'vapid_key' => env('FCM_VAPID_KEY', ''),
    
    /*
     * ------------------------------------------------------------------------
     * Firebase Credentials
     * ------------------------------------------------------------------------
     *
     * Путь к файлу учетных данных сервисного аккаунта Firebase.
     * Если ваша учетная запись Firebase требует файла JSON с credentials, укажите путь здесь.
     */
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase_credentials.json')),
    ],

    /*
     * ------------------------------------------------------------------------
     * Firebase Database
     * ------------------------------------------------------------------------
     *
     * Настройки для Realtime Database в Firebase.
     */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL', ''),
    ],

    /*
     * ------------------------------------------------------------------------
     * Firebase Dynamic Links
     * ------------------------------------------------------------------------
     *
     * Настройки для Dynamic Links в Firebase.
     */
    'dynamic_links' => [
        'domain_uri_prefix' => env('FIREBASE_DYNAMIC_LINKS_DOMAIN_URI_PREFIX', ''),
        'default_redirect_link' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_REDIRECT', ''),
    ],

    /*
     * ------------------------------------------------------------------------
     * Firebase FCM
     * ------------------------------------------------------------------------
     *
     * Настройки для Firebase Cloud Messaging.
     */
    'fcm' => [
        'default_server_key' => env('FCM_SERVER_KEY'),
        'default_client_key' => env('FCM_CLIENT_KEY', ''),
        'default_vapid_key' => env('FCM_VAPID_KEY', ''),
    ],

    /*
     * ------------------------------------------------------------------------
     * Firebase Messaging
     * ------------------------------------------------------------------------
     *
     * Дополнительные настройки для Messaging.
     */
    'messaging' => [
        'token_lifetime' => 3600,
    ],
];
