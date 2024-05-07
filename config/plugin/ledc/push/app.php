<?php

use Ledc\Push\Pipelines\UniqidPipeline;
use Ledc\Push\Pipelines\WebmanAdmin;

//读取环境变量内服务监听端口
$server_listen_port = getenv('SERVER_LISTEN_PORT');
if (false === $server_listen_port || !ctype_digit($server_listen_port)) {
    $server_listen_port = '8787';
}

return [
    'enable' => true,
    'websocket' => 'websocket://' . env('SERVER_HOST', '127.0.0.1') . ':' . env('SERVER_WEBSOCKET_PORT', '3153'),
    'api' => 'http://0.0.0.0:3236',
    'app_key' => 'a2b15c26197c7ab74607cbd81af77a47',
    'app_secret' => env('PUSH_APP_SECRET', ''),
    'channel_hook' => 'http://127.0.0.1:' . $server_listen_port . '/plugin/ledc/push/hook',
    'auth' => '/plugin/ledc/push/auth',
    'pipeline' => [
        [WebmanAdmin::class, 'process'],
        [UniqidPipeline::class, 'process'],
    ],
    // redis集合key：存放在线频道
    'all_channels_key' => 'PusherIyuuCn:ChannelsOnline',
];
