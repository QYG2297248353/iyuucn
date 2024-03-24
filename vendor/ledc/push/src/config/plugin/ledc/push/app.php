<?php

use Ledc\Push\Pipelines\UniqidPipeline;
use Ledc\Push\Pipelines\WebmanAdmin;

return [
    'enable'       => true,
    'websocket'    => 'websocket://0.0.0.0:3131',
    'api'          => 'http://0.0.0.0:3232',
    'app_key'      => 'e5276628ea9b9324f12e08b601daca87',
    'app_secret'   => 'dac506a5d990b3247250b46542077271',
    'channel_hook' => 'http://127.0.0.1:8787/plugin/ledc/push/hook',
    'auth'         => '/plugin/ledc/push/auth',
    'pipeline' => [
        [WebmanAdmin::class, 'process'],
        [UniqidPipeline::class, 'process'],
    ],
];
