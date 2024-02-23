<?php

namespace Ledc\Template\Library;

use support\Redis;

/**
 * Redis简易缓存.
 */
class RedisCache
{
    /**
     * 默认缓存有效期
     */
    public static int $ttl = 600;

    /**
     * 获取.
     */
    public static function get(int|string $key, callable $callback, int $ttl = 0): mixed
    {
        if (strlen($key) < 1) {
            return null;
        }

        try {
            if ($raw = Redis::get($key)) {
                $array = unserialize($raw);

                return $array['value'];
            }
            $value = $callback();
            if (is_null($value)) {
                return null;
            }

            $ttl = $ttl > 0 ? $ttl : static::$ttl;
            Redis::setEx($key, $ttl, serialize([
                'value' => $value,
            ]));

            return $value;
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * 删除.
     */
    public static function remove(int|string $key): ?bool
    {
        if (strlen($key) < 1) {
            return null;
        }

        try {
            $result = Redis::del($key);

            return $result > 0;
        } catch (\Throwable $throwable) {
            return null;
        }
    }
}
