<?php
include_once './vendor/autoload.php';

use Vasoft\BxBackupTools\Backup\Calculator;
use Vasoft\BxBackupTools\Backup\FTP;
use Vasoft\BxBackupTools\Backup\FTP\Downloader;
use Vasoft\BxBackupTools\Config\Factory;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Tasks\Timer;
use Vasoft\BxBackupTools\Notifier\Console\Sender;
use Vasoft\BxBackupTools\Restore;
use Vasoft\BxBackupTools\Restore\BitrixRestore;

set_time_limit(0);
ignore_user_abort(true);
ini_set('max_execution_time', '0');

$environment = $argv[1] ?? 'local';

$configContainer = (new Factory())->load($environment);
/** @var  FTP\Config $configBackup */
$configBackup = $configContainer->get(FTP\Config::class);
$configBackup->setRemotePathCalculator(new Calculator\WeeklyIncrementPrevious());
/** @var  Restore\Config $configRestore */
$configRestore = $configContainer->get(Restore\Config::class);
$cmd = new \Vasoft\BxBackupTools\Core\SystemCmd();

$app = new Application(
    [
        new Sender(),
        new \Vasoft\BxBackupTools\Tasks\Exception(),
        new Timer(),
        new BitrixRestore($cmd, $configRestore),
//        new Downloader($cmd, $configBackup),
    ]
);
$messages = new MessageContainer();
$messages->add('', ['Restore ' . $environment]);
$app->handle($messages);
