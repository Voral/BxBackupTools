<?php

declare(strict_types=1);

$config = [
    'backupFTP' => [
        'remote' => [
            'host' => '',
            'protocol' => '',
            'path' => '',
            'user' => '',
            'password' => '',
        ],
        'local' => [
            'path' => '',
        ],
        'mirror' => [
            'parallel' => 5,
            'delete' => false,
        ],
    ],
];
