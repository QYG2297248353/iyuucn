<?php
/**
 * 自定义脚本
 */

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/support/bootstrap.php';

ini_set('display_errors', 'on');
error_reporting(E_ALL);
// 限定运行模式
if (PHP_SAPI !== 'cli') {
    exit("You must run the CLI environment\n");
}
