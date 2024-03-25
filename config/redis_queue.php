<?php
/**
 * redis队列配置
 */

return [
    'default' => [
        'host' => 'redis://127.0.0.1:6379',
        'options' => [
            'auth' => null,       // 密码，字符串类型，可选参数
            'db' => env('REDIS_DB', 2),            // 数据库
            'prefix' => '',       // key 前缀
            'max_attempts' => 2, // 消费失败后，重试次数
            'retry_seconds' => 10, // 重试间隔，单位秒
        ]
    ],
];
