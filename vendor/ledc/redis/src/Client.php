<?php

namespace Ledc\Redis;

use RuntimeException;

/**
 * Redis异步客户端
 * - 依赖workerman/redis
 * - 代码来源https://github.com/webman-php/redis-queue
 *
 * @link https://www.workerman.net/plugin/12
 * @link https://www.workerman.net/doc/workerman/components/workerman-redis-queue.html
 * @link https://www.workerman.net/doc/workerman/components/workerman-redis.html
 * @mixin RedisQueueClient
 * @method static void send(string $queue, mixed $data, int $delay = 0) 投递消息(异步)
 */
class Client
{
    /**
     * @var RedisQueueClient[]
     */
    protected static array $_connections = [];

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::connection('default')->{$name}(... $arguments);
    }

    /**
     * @param string $name
     * @return RedisQueueClient
     */
    public static function connection(string $name = 'default'): RedisQueueClient
    {
        if (!isset(static::$_connections[$name])) {
            $config = config('redis_queue', []);
            if (!isset($config[$name])) {
                throw new RuntimeException("RedisQueue connection $name not found");
            }
            $host = $config[$name]['host'];
            $options = $config[$name]['options'];
            $client = new RedisQueueClient($host, $options);
            static::$_connections[$name] = $client;
        }
        return static::$_connections[$name];
    }
}
