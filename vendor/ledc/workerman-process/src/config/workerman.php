<?php
// +----------------------------------------------------------------------
// | Workerman示例配置
// +----------------------------------------------------------------------
return [
    /**
     * 默认配置
     */
    'default' => [
        //PHP配置
        'error_reporting' => E_ALL,
        'default_timezone' => 'Asia/Shanghai',
        /**
         * 主进程配置
         */
        'event_loop' => '',
        'stop_timeout' => 2,
        'pid_file' => rtrim(runtime_path(), PHP_EOL) . '/workerman.pid',
        'status_file' => rtrim(runtime_path(), PHP_EOL) . '/workerman.status',
        'stdout_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/stdout.log',
        'log_file' => rtrim(runtime_path(), PHP_EOL) . '/logs/workerman.log',
        'max_package_size' => 10 * 1024 * 1024,
    ],
    /**
     * 进程配置
     */
    'process' => [
        'websocket' => [
            //使能
            'enable' => true,
            //监听
            'listen' => 'websocket://0.0.0.0:2345',
            //上下文
            'context' => [],
            //workerman支持的属性和回调属性
            'properties' => [
                // 事件回调
                // onWorkerStart
                'onWorkerStart'  => function ($worker) {

                },
                // onWorkerReload
                'onWorkerReload' => function ($worker) {

                },
                // onConnect
                'onConnect'      => function ($connection) {

                },
                // onMessage
                'onMessage'      => function ($connection, $data) {
                    $connection->send('receive success');
                },
                // onClose
                'onClose'        => function ($connection) {

                },
                // onError
                'onError'        => function ($connection, $code, $msg) {
                    echo "error [ $code ] $msg\n";
                },
            ],
            //业务：handler类
            'handler' => '',
            //业务：handler类的构造函数参数
            'constructor' => [],
        ],
    ],
];
