<?php
/**
 * Пример использования пакета для загрузки бекапа Битрикс на FTP.
 */

use Vasoft\BxBackupTools\Backup\Calculator\WeeklyIncrement;
use Vasoft\BxBackupTools\Config;
use Vasoft\BxBackupTools\Backup\FTP;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\SystemCmd;
use Vasoft\BxBackupTools\Notifier\Telegram;
use Vasoft\BxBackupTools\Tasks\Timer;

include_once 'vendor/autoload.php';
set_time_limit(0);
ignore_user_abort(true);
ini_set('max_execution_time', '0');
/** Получаем название окружения из первого аргумента командной строки */
$environment = $argv[1] ?? 'local';
/** Загружаем конфигурацию приложения */
$configContainer = (new Config\Factory())->load($environment);
/** @var  FTP\Config $configBackup */
$configBackup = $configContainer->get(FTP\Config::class);
/** Задаем логику хранения бекапов: 7 последних */
$configBackup->setRemotePathCalculator(new WeeklyIncrement());
/** Получаем настройки для модуля отправки сообщений, в данном случае это Telegram */
/** @var  Telegram\Config $configSender */
$configSender = $configContainer->get(Telegram\Config::class);
/** Инициализируем объект для работы с системными командами. Точка расширения где можно переопределить выполнение системных команд */
$cmd = new SystemCmd();
/**
 * Инициализируем приложение. Можно указать несколько действий которые необходимо выполнить.
 * Так же можно добавлять свои действия, которые должны быть унаследованы от \Vasoft\BxBackupTools\Core\Task
 * По сути здесь реализуется pipeline и middleware паттерны.
 * @see \Vasoft\BxBackupTools\Core\Task
 */
$app = new Application([
    new Telegram\Sender($configSender),  // отправка сообщения о результатах выполнения скрипта
    new \Vasoft\BxBackupTools\Tasks\Exception(), // обработка исключений
    new Timer(), // Таймер вычисляющий время выполнения скрипта
    new FTP\Uploader($cmd, $configBackup), // Загрузка архива на сервер
]);
/** Запускаем выполнение скрипта */
$app->handle(new MessageContainer());
