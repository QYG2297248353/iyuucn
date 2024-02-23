<?php

namespace Ledc\Template\Library;

use Workerman\Timer;

/**
 * 看门狗.
 */
final class Dog
{
    /**
     * 关注.
     */
    final public static function watch(bool|int $timerId, int $ttl = 30): void
    {
        if ($timerId) {
            Timer::add($ttl, function ($timerId) {
                Timer::del($timerId);
            }, [$timerId], false);
        }
    }
}
