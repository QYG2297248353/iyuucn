<?php

use Ledc\WorkermanProcess\Process;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/support/bootstrap.php';

// 严格开发模式
ini_set('display_errors', 'on');
error_reporting(E_ALL);

// 永不超时
ini_set('max_execution_time', 0);
set_time_limit(0);

// 内存限制，如果外面设置的内存比 /etc/php/php-cli.ini 大，就不要设置了
if (intval(ini_get("memory_limit")) < 512) {
    ini_set('memory_limit', '512M');
}

if (PHP_SAPI !== 'cli') {
    exit("You must run the CLI environment\n");
}

$config = config('gateway_worker.default', []);
$process = config('gateway_worker.process', []);
Process::init($config);
foreach ($process as $name => $item) {
    Process::start($name, $item);
}
Process::runAll();
