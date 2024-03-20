<?php

//读取环境变量内服务监听端口
$server_listen_port = getenv('SERVER_LISTEN_PORT');
if (false === $server_listen_port || !ctype_digit($server_listen_port)) {
    $server_listen_port = '8787';
}

return [
    'listen' => 'http://0.0.0.0:' . $server_listen_port,
    'transport' => 'tcp',
    'context' => [],
    'name' => 'webman',
    'count' => cpu_count() * 4,
    'user' => '',
    'group' => '',
    'reusePort' => false,
    'event_loop' => '',
    'stop_timeout' => 2,
    'pid_file' => runtime_path() . '/webman.pid',
    'status_file' => runtime_path() . '/webman.status',
    'stdout_file' => runtime_path() . '/logs/stdout.log',
    'log_file' => runtime_path() . '/logs/workerman.log',
    'max_package_size' => 10 * 1024 * 1024
];
