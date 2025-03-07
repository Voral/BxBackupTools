<?php
$config = [
    'backupFTP' => [
        'remote' => [
            'host' => '192.168.1.1',
            'protocol' => 'ftp',
            'path' => '/test',
            'user' => 'myUserFtp',
            'password' => 'myUserFtpPassword',
        ],
        'local' => [
            'path' => '/media/bkp/alex/1/b',
        ],
        'mirror' => [
            'delete' => true,
        ],
    ],
    'telegram' => [
        'token' => 'token123123123',
        'chatId' => '-123123123123',
    ],
    'bitrixRestore' => [
        'database' => [
            'host' => 'localhost',
            'name' => 'myDatabase',
            'user' => 'myUser',
            'password' => 'myUserPassword',
        ],
        'archive' => [
            'password' => 'gnghFrt12',
        ],
        'site' => [
            'documentRoot' => '/media/bkp/alex/1/r',
        ],
    ],
];