<?php

return [
    'enable' => true,
    'limit' => [
        'limit' => 20, // 请求次数
        'window_time' => 3600, // 窗口时间，单位：秒
        'status' => 429,  // HTTP 状态码
        'body' => [  // 响应信息
            'errcode' => 429,
            'errmsg' => '请求太多，请稍后重试！',
            'data' => null
        ],
    ],
];
