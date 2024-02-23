# workerman进程启动器

## 安装

```
composer require ledc/workerman-process
```



## 使用

```
<?php

use Ledc\WorkermanProcess\Process;

require_once __DIR__ . '/vendor/autoload.php';

$process = [
    'websocket' => [
        //使能
        'enable' => true,
        //监听
        'listen' => 'websocket://0.0.0.0:2345',
        //上下文
        'context' => [],
        //worker支持的属性和回调属性
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
        //业务进程：handler类
        'handler' => '',
        //业务进程：handler类的构造函数参数
        'constructor' => [],
    ],
    'http' => [
        //使能
        'enable' => true,
        //监听
        'listen' => 'http://0.0.0.0:2346',
        //上下文
        'context' => [],
        //worker支持的属性
        'properties' => [
            //属性
            'count' => 2,
            // 事件回调
            // onMessage
            'onMessage'      => function ($connection, $data) {
                $connection->send('receive success');
            },
        ],
        //业务进程：handler类
        'handler' => '',
        //业务进程：handler类的构造函数参数
        'constructor' => [],
    ],
];

foreach ($process as $name => $config) {
    Process::start($name, $config);
}

Process::runAll();
```



## 配置详情

配置节点`properties`支持workerman的所有属性和回调属性，详情参考官方文档：[workerman 手册](https://www.workerman.net/doc/workerman/worker.html)

业务handler类在子进程onWorkerStart时实例化，handler类支持workerman的所有回调属性；



## 配置生效情况

| 序号 | 存在handler类 | 继承Workerman\Worker | 实例化逻辑                                                   | 属性properties | handler类constructor |
| ---- | ------------- | -------------------- | ------------------------------------------------------------ | -------------- | -------------------- |
| 1    | 是            | 是                   | new $handler($listen, $context)                              | 生效           | 忽略                 |
| 2    | 否            | 否                   | new Worker($listen, $context)                                | 生效           | 忽略                 |
| 3    | 是            | 否                   | new Worker($listen, $context)<br />子进程onWorkerStart时new $handler() | 生效           | 生效                 |



## 最佳实践

- GatewayWorker，使用第一种方式启动
- 继承Workerman\Worker开发的类，使用第一种方式启动
- 未继承，使用第二种或第三种方式启动