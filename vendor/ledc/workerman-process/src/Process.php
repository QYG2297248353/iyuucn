<?php

namespace Ledc\WorkermanProcess;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/**
 * Workerman进程管理
 */
class Process
{
    /**
     * 初始化
     * - 在主进程设置静态属性等
     * @param array $config
     * @return void
     */
    public static function init(array $config): void
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
        if (isset($config['error_reporting'])) {
            error_reporting($config['error_reporting']);
        }
        if ($timezone = $config['default_timezone'] ?? null) {
            date_default_timezone_set($timezone);
        }

        Worker::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        Worker::$pidFile = $config['pid_file'] ?? '';
        Worker::$stdoutFile = $config['stdout_file'] ?? '/dev/null';
        Worker::$logFile = $config['log_file'] ?? '';
        Worker::$eventLoopClass = $config['event_loop'] ?? '';
        TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Worker::class, 'statusFile')) {
            Worker::$statusFile = $config['status_file'] ?? '';
        }
        if (property_exists(Worker::class, 'stopTimeout')) {
            Worker::$stopTimeout = $config['stop_timeout'] ?? 2;
        }
    }

    /**
     * 启动Worker容器实例
     * @param string $name 进程名称(实例名称)
     * @param array $config 进程配置
     * @return void
     */
    public static function start(string $name, array $config): void
    {
        $enable = $config['enable'] ?? false;
        if (empty($enable)) {
            return;
        }

        $handler = $config['handler'] ?? null;
        $listen = $config['listen'] ?? '';
        $context = $config['context'] ?? [];
        $properties = $config['properties'] ?? [];
        if (class_exists($handler) && is_a($handler, Worker::class, true)) {
            /** @var Worker $worker */
            $worker = new $handler($listen, $context);
            static::setProperties($worker, $name, $properties);
            return;
        }

        $worker = new Worker($listen, $context);
        static::setProperties($worker, $name, $properties);

        if (class_exists($handler)) {
            $worker->onWorkerStart = function ($worker) use ($config) {
                if ($handler = $config['handler'] ?? null) {
                    if (!class_exists($handler)) {
                        echo "process error: class {$handler} not exists\r\n";
                        return;
                    }
                    $constructor = $config['constructor'] ?? [];
                    $instance = $constructor ? (new $handler(... array_values($constructor))) : (new $handler);
                    static::bindCallback($worker, $instance);
                }
            };
        }
    }

    /**
     * 设置属性
     * - 支持workerman的所有属性
     * @param Worker $worker Worker容器实例
     * @param string $name 进程名称
     * @param array $properties Worker容器属性
     * @return void
     */
    public static function setProperties(Worker $worker, string $name, array $properties): void
    {
        $worker->name = $name;
        unset($properties['name']);
        foreach ($properties as $property => $value) {
            $worker->{$property} = $value;
        }
    }

    /**
     * 设置回调属性
     * - 支持workerman的所有回调属性
     * - 把类方法绑定到worker的回调属性上
     * @param Worker $worker Worker容器实例
     * @param object $instance 待绑定的对象
     * @return void
     */
    public static function bindCallback(Worker $worker, object $instance): void
    {
        $callbackMap = [
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop',
            'onWebSocketConnect',
            'onWorkerReload'
        ];
        foreach ($callbackMap as $name) {
            if (method_exists($instance, $name)) {
                $worker->{$name} = [$instance, $name];
            }
        }
        if (method_exists($instance, 'onWorkerStart')) {
            call_user_func([$instance, 'onWorkerStart'], $worker);
        }
    }

    /**
     * 运行所有worker容器
     * @return void
     */
    public static function runAll(): void
    {
        Worker::runAll();
    }
}
