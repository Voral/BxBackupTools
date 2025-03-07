<?php

use Vasoft\BxBackupTools\Backup\Calculator\WeeklyIncrement;
use Vasoft\BxBackupTools\Config;
use Vasoft\BxBackupTools\Backup\FTP;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Notifier\Console\Sender;
use Vasoft\BxBackupTools\Notifier\Telegram;
use Vasoft\BxBackupTools\Tasks\Timer;

include_once 'vendor/autoload.php';
set_time_limit(0);
ignore_user_abort(true);
ini_set('max_execution_time', '0');

$environment = $argv[1] ?? 'local';

$configContainer = (new Config\Factory())->load($environment);
/** @var  FTP\Config $configBackup */
$configBackup = $configContainer->get(FTP\Config::class);
$configBackup->setRemotePathCalculator(new WeeklyIncrement());
/** @var  Telegram\Config $configSender */
$configSender = $configContainer->get(Telegram\Config::class);
$cmd = new \Vasoft\BxBackupTools\Core\SystemCmd();

$app = new Application(
//    new FTP\Uploader($configBackup),
    [
//        new Telegram\Sender($configSender),
        new Sender(),
        new \Vasoft\BxBackupTools\Tasks\Exception(),
        new Timer(),
        new FTP\Uploader($cmd, $configBackup),
    ]
);
$app->handle(new MessageContainer('H:i:s'));
