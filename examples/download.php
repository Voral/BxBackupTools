<?php
/**
 * Пример использования пакета для скачивания бекапов Битрикс и развертывания их на сервере. Отправка сообщений в Telegram.
 */
include_once  './vendor/autoload.php';
/** Если используете этот скрипт в качестве шаблона - удалите строку выше и раскомментируйте строку ниже */
// include_once __DIR__ . '/vendor/autoload.php';

use Vasoft\BxBackupTools\Backup\Calculator;
use Vasoft\BxBackupTools\Backup\FTP;
use Vasoft\BxBackupTools\Backup\FTP\Downloader;
use Vasoft\BxBackupTools\Config\Factory;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\SystemCmd;
use Vasoft\BxBackupTools\Informer\DiskSpace;
use Vasoft\BxBackupTools\Tasks\Timer;

use Vasoft\BxBackupTools\Notifier\Telegram;
use Vasoft\BxBackupTools\Restore;
use Vasoft\BxBackupTools\Restore\BitrixRestore;

set_time_limit(0);
ignore_user_abort(true);
ini_set('max_execution_time', '0');
/** Получаем название окружения из первого аргумента командной строки */
$environment = $argv[1] ?? 'local';
/** Загружаем конфигурацию приложения */
$configContainer = (new Factory())->load($environment);
/** @var  FTP\Config $configBackup */
$configBackup = $configContainer->get(FTP\Config::class);
/** Задаем логику хранения бекапов. Для примера берется случай когда мы собираемся развернуть вчерашний бекап */
$configBackup->setRemotePathCalculator(new Calculator\WeeklyIncrementPrevious());
/** Получаем настройки для модуля отправки сообщений, в данном случае это Telegram */
/** @var  Telegram\Config $configSender */
$configSender = $configContainer->get(Telegram\Config::class);
/** Получаем настройки для модуля развертывания */
/** @var  Restore\Config $configRestore */
$configRestore = $configContainer->get(Restore\Config::class);

/** Инициализируем объект для работы с системными командами. Точка расширения где можно переопределить выполнение системных команд */
$cmd = new SystemCmd();

/**
 * Инициализируем приложение. Можно указать несколько действий которые необходимо выполнить.
 * Так же можно добавлять свои действия, которые должны быть унаследованы от \Vasoft\BxBackupTools\Core\Task
 * По сути здесь реализуется pipeline и middleware паттерны.
 * @see \Vasoft\BxBackupTools\Core\Task
 */
$app = new Application([
    new Telegram\Sender($configSender), // отправка сообщения о результатах выполнения скрипта
    new \Vasoft\BxBackupTools\Tasks\Exception(), // обработка исключений
    new DiskSpace(__DIR__, 0), // проверка свободного места на диске
    new Timer(), // Таймер вычисляющий время выполнения скрипта
    new BitrixRestore($cmd, $configRestore), // развертывание скачанного архива
    new Downloader($cmd, $configBackup), // скачивание архива с сервера
]);
/** Создаем контейнер для хранения сообщений, который будут отправлены выбранным Sender'ом */
$messages = new MessageContainer();
/** Добавляем в контейнер первое сообщение */
$messages->add('', ['Restore ' . $environment]);

/** Запускаем выполнение скрипта */
$app->handle($messages);
