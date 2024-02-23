<?php

namespace Ledc\RedisQueue\Library;

use Ledc\Redis\Payload;
use Ledc\RedisQueue\ConsumerAbstract;
use support\Container;

/**
 * 异步任务队列
 */
class JobsConsumer extends ConsumerAbstract
{
    /**
     * 异步任务队列名称
     */
    public static function queue(): string
    {
        return 'JobsQueueAsync';
    }

    /**
     * 消费方法
     * - 消费过程中没有抛出异常和Error视为消费成功；否则消费失败,进入重试队列
     * - 也可以通过 $payload 自定义重试间隔和重试次数
     * @param mixed $data 数据
     * @param Payload $payload 队列任务有效载荷
     */
    final public function consume(mixed $data, Payload $payload): void
    {
        $job = $data['job'] ?? '';
        $data = $data['args'] ?? null;
        $constructor = $data['constructor'] ?? [];
        if (empty($job)) {
            return;
        }

        list($class, $method) = self::parseJob($job);
        $instance = $constructor ? Container::make($class, $constructor) : Container::get($class);
        if (method_exists($instance, $method)) {
            if ($data && is_array($data)) {
                $instance->{$method}(... array_values($data));
            } else {
                // null/int/bool/string/空数组
                $instance->{$method}($data);
            }
        }
    }

    /**
     * 将job解析为类和方法
     * @param string $job
     * @return array
     */
    final protected static function parseJob(string $job): array
    {
        $segments = explode('@', $job);
        return 2 === count($segments) ? $segments : [$segments[0], 'execute'];
    }
}
