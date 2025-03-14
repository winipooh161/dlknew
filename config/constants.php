<?php

return [
    'default_group_avatar' => 'storage/avatars/group_default.svg',
    // Можно добавить и другие константы, например:
    'default_user_avatar' => 'storage/avatars/user_default.png',

    /*
    |--------------------------------------------------------------------------
    | Chat Constants
    |--------------------------------------------------------------------------
    |
    | These constants are used for the chat system
    |
    */
    'chat' => [
        'message_types' => [
            'text' => 'text',
            'file' => 'file',
            'notification' => 'notification',
            'audio' => 'audio',
            'video' => 'video',
        ],
        'max_file_size' => 10240, // 10MB
        'max_files_per_message' => 5,
        'supported_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'audio/mpeg',
            'video/mp4',
        ],
        'pagination' => [
            'messages_per_page' => 50,
        ],
        'cache' => [
            'user_chats_ttl' => 5, // minutes
            'unread_counts_ttl' => 2, // minutes
        ],
    ]
];
