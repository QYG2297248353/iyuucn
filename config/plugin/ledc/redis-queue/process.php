<?php

return [
    'consumer' => [
        'handler' => Ledc\RedisQueue\Library\Process::class,
        'count' => 8, // 可以设置多进程同时消费
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis',
            // 处理失败队列的回调函数
            'failed_callback' => function ($package, $connection_name) {
            },
            // 启用单进程Job任务
            'single_jobs' => true,
        ]
    ],
];
