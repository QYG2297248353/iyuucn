<?php

// +----------------------------------------------------------------------
// | GatewayWorker示例配置
// +----------------------------------------------------------------------

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Ledc\WorkermanProcess\Events;

// 密钥
$gatewaySecret = getenv('GATEWAY_SECRET') ?: '';
// 注册中心监听地址
$registerListenAddress = getenv('GATEWAY_REGISTER_LISTEN_ADDRESS') ?: '127.0.0.1';
// 注册中心地址
$registerAddress = getenv('GATEWAY_REGISTER_ADDRESS') ?: '127.0.0.1';
// 注册中心端口
$registerPort = getenv('GATEWAY_REGISTER_PORT') ?: '1236';
// 本机IP
$lanIp = getenv('SOCKET_BIND_TO_IP') ?: '127.0.0.1';
// 起始端口
$startPort = getenv('GATEWAY_START_PORT') ?: 4000;

if (class_exists(\GatewayWorker\Lib\Gateway::class)) {
    \GatewayWorker\Lib\Gateway::$registerAddress = $registerAddress . ':' . $registerPort;
    \GatewayWorker\Lib\Gateway::$secretKey = $gatewaySecret;
}

return [
    // 默认配置
    'default' => [
        // PHP配置
        'error_reporting' => E_ALL,
        'default_timezone' => 'Asia/Shanghai',
        // 主进程配置
        'event_loop' => '',
        'stop_timeout' => 2,
        'pid_file' => rtrim(runtime_path(), PHP_EOL) . '/gateway_worker.pid',
        'status_file' => rtrim(runtime_path(), PHP_EOL) . '/gateway_worker.status',
        'stdout_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/gateway_worker_stdout.log',
        'log_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/gateway_worker.log',
        'max_package_size' => 10 * 1024 * 1024,
    ],
    // 进程配置
    'process' => [
        // 注册中心进程 https://www.workerman.net/doc/gateway-worker/register.html
        'register' => [
            // 使能
            'enable' => true,
            // 处理类
            'handler' => Register::class,
            // 监听
            'listen' => 'text://' . $registerListenAddress . ':' . $registerPort,
            'properties' => [
                // 通信密钥
                'secretKey' => $gatewaySecret,
            ],
        ],
        // 客户端连接的进程 https://www.workerman.net/doc/gateway-worker/gateway.html
        'gateway' => [
            // 使能
            'enable' => true,
            // 客户端连接的进程
            'handler' => Gateway::class,
            // 应用层协议、监听地址、端口
            'listen' => 'websocket://0.0.0.0:2121',
            'properties' => [
                // 进程数
                'count' => 1,
                // 通信密钥
                'secretKey' => $gatewaySecret,
                // 设置进程收到reload信号后是否reload重启
                'reloadable' => false,
                // 本机ip，分布式部署时使用内网ip
                'lanIp' => $lanIp,
                // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
                // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
                'startPort' => $startPort,
                // 注册中心地址
                'registerAddress' => $registerAddress . ':' . $registerPort,
                // 心跳检测时间间隔
                'pingInterval' => 30,
                // 客户端连续$pingNotResponseLimit次$pingInterval时间内不发送任何数据(包括但不限于心跳数据)则断开链接，并触发onClose
                'pingNotResponseLimit' => 1,
                'onConnect' => function () {
                },
            ],
        ],
        // 业务逻辑进程 https://www.workerman.net/doc/gateway-worker/business-worker.html
        'businessWorker' => [
            // 使能
            'enable' => true,
            // 业务处理类
            'handler' => BusinessWorker::class,
            'properties' => [
                // 事件处理类
                'eventHandler' => Events::class,
                // 进程数
                'count' => 2,
                // 通信密钥
                'secretKey' => $gatewaySecret,
                // 注册中心地址
                'registerAddress' => $registerAddress . ':' . $registerPort,
            ],
        ],
    ],
];
