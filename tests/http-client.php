<?php

use Workerman\Http\Response;
use Workerman\Worker;

require_once dirname(__DIR__) . '/vendor/autoload.php';

//Worker::$eventLoopClass = \Workerman\Events\Swow::class;
$worker = new Worker();
$worker->onWorkerStart = function () {
    $http = new Workerman\Http\Client();

    $start_time = microtime(true);
    echo '开始时间' . $start_time . PHP_EOL;
    $num = 200;
    $result = [];
    while ($num--) {
        $http->get('https://ledc.cn/index/view', function (Response $response) use ($start_time, $num, &$result) {
            //var_dump($response->getStatusCode());
            //echo $response->getBody();
            $result[] = sprintf('第%d个 | 耗时%s秒 | 状态码%d', $num, microtime(true) - $start_time, $response->getStatusCode());
            if (200 === count($result)) {
                print_r($result);
                echo '请求完成，总耗时' . (microtime(true) - $start_time) . PHP_EOL;
            }
        }, function ($exception) {
            echo $exception;
        });
    }
    $end_time = microtime(true);
    echo '结束时间' . $end_time . PHP_EOL;
};
Worker::runAll();
