<?php

declare(strict_types=1);

$config = [
    // Настройки бэкапа сайта на FTP сервере
    'backupFTP' => [
        // Настройки удаленного сервера
        'remote' => [
            'host' => '', // FTP сервер
            'protocol' => 'ftp', // ftp или sftp
            'path' => '', // путь к папке с бекапами на сервере
            'user' => '', // имя пользователя
            'password' => '', // пароль
        ],
        'local' => [
            'path' => '', // путь к папке куда необходимо скачать бекап
        ],
        'mirror' => [
            'parallel' => 5, // количество одновременных скачиваний
            'delete' => true, // удалить файлы на удаленном сервере после скачивания
        ],
    ],
    // Настройки Telegram сообщений
    'telegram' => [
        'token' => '', // токен бота
        'chatId' => '', // id чата, куда будут отправляться сообщения
        'title' => '', // не обязательный параметр, будет добавляться в начало сообщения
    ],
    // Настройки развертывания сайта на Битриксе
    'bitrixRestore' => [
        // Настройки СУБД
        'database' => [
            'host' => '', // адрес сервера
            'name' => '', // имя базы данных
            'user' => '', // имя пользователя
            'password' => '', // пароль
        ],
        // Параметры архива с бекапом сайта
        'archive' => [
            'password' => '', // пароль от архива если он зашифрован
            'path' => '', // путь к архиву
        ],
        'site' => [
            'documentRoot' => '', // путь к корню сайта
        ],
        'loggingEnabled' => false, // включить расширенный вывод логов
        'unpackEnabled' => true, // разархивировать файлы
        'restoreFilesEnabled' => true, // восстановить файлы сайта
        'restoreDatabaseEnabled' => true, // восстановить базу данных
        'restoreCreditsEnabled' => true, // восстановить в файлах настройки параметров подключения к БД
    ],
];
