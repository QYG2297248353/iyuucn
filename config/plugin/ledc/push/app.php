<?php

use Ledc\Push\Pipelines\UniqidPipeline;
use Ledc\Push\Pipelines\WebmanAdmin;

return [
    'enable' => true,
    'websocket' => 'websocket://0.0.0.0:3135',
    'api' => 'http://0.0.0.0:3236',
    'app_key' => 'a2b15c26197c7ab74607cbd81af77a47',
    'app_secret' => env('PUSH_APP_SECRET', ''),
    'channel_hook' => 'http://127.0.0.1:8787/plugin/ledc/push/hook',
    'auth' => '/plugin/ledc/push/auth',
    'pipeline' => [
        [WebmanAdmin::class, 'process'],
        [UniqidPipeline::class, 'process'],
    ],
    // redis集合key：存放在线频道
    'all_channels_key' => 'PusherIyuuCn:ChannelsOnline',
];
