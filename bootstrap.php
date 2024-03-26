<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/bootstrap.php';

// 限定CLI
if (PHP_SAPI !== 'cli') {
    exit("You must run the CLI environment\n");
}
// 时区
date_default_timezone_set('Asia/Shanghai');

// 严格开发模式
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 永不超时
ini_set('max_execution_time', 0);
set_time_limit(0);

// 内存限制，如果外面设置的内存比 /etc/php/php-cli.ini 大，就不要设置了
if (intval(ini_get("memory_limit")) < 1024) {
    ini_set('memory_limit', '1024M');
}
echo microtime(true) . ' 当前时间：' . date('Y-m-d H:i:s') . PHP_EOL;
echo microtime(true) . ' 当前操作系统：' . PHP_OS . PHP_EOL;
echo microtime(true) . ' 当前运行环境：' . PHP_OS_FAMILY . PHP_EOL;
echo microtime(true) . ' 当前接口类型：' . PHP_SAPI . PHP_EOL;
echo microtime(true) . ' PHP二进制文件：' . PHP_BINARY . PHP_EOL;
echo microtime(true) . ' PHP版本号：' . PHP_VERSION . PHP_EOL;
echo microtime(true) . ' 初始化完成！' . PHP_EOL;
