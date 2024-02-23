<?php

use Ledc\Push\Pipelines\UniqidPipeline;
use Ledc\Push\Pipelines\WebmanAdmin;

return [
    'enable'       => true,
    'websocket'    => 'websocket://0.0.0.0:3131',
    'api'          => 'http://0.0.0.0:3232',
    'app_key'      => '51317f3f0969d09345aab08492557ded',
    'app_secret'   => '5f1e907e48063c800f696cc55dc115d9',
    'channel_hook' => 'http://127.0.0.1:8787/plugin/ledc/push/hook',
    'auth'         => '/plugin/ledc/push/auth',
    'pipeline' => [
        [WebmanAdmin::class, 'process'],
        [UniqidPipeline::class, 'process'],
    ],
];
