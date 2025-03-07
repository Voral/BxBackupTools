<?php
$config = [
    'backupFTP' => [
        'remote' => [
            'host' => '',
            'protocol' => 'ftp',
            'path' => '',
            'user' => '',
            'password' => '',
        ],
        'local' => [
            'path' => '',
        ],
        'mirror' => [
            'parallel' => 5,
            'delete' => true,
        ],
    ],
    'telegram' => [
        'token' => '',
        'chatId' => '',
    ],
    'bitrixRestore' => [
        'database' => [
            'host' => '',
            'name' => '',
            'user' => '',
            'password' => '',
        ],
        'archive' => [
            'password' => '',
            'path' => '',
        ],
        'site' => [
            'documentRoot' => '',
        ],
        'loggingEnabled' => false,
        'unpackEnabled' => true,
        'restoreFilesEnabled' => true,
        'restoreDatabaseEnabled' => true,
        'restoreCreditsEnabled' => true,
    ],
];