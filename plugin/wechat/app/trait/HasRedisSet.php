<?php

namespace plugin\wechat\app\trait;

use support\Redis;

/**
 * Redis集合
 */
trait HasRedisSet
{
    /**
     * 集合的key
     * @var string
     */
    protected string $setKey;

    /**
     * 向集合添加一个或多个成员
     * @param string $member
     * @return int
     */
    public function sAdd(string $member): int
    {
        return Redis::sAdd($this->setKey, $member);
    }

    /**
     * 移除集合中一个或多个成员
     * @param string $member
     * @return int
     */
    public function sRem(string $member): int
    {
        return Redis::sRem($this->setKey, $member);
    }

    /**
     * 判断 member 元素是否是集合 key 的成员
     * @param string $member
     * @return bool
     */
    public function sIsMember(string $member): bool
    {
        return Redis::sIsMember($this->setKey, $member);
    }

    /**
     * 获取集合的成员数
     * @return int
     */
    public function sCard(): int
    {
        return Redis::sCard($this->setKey);
    }

    /**
     * 刷新成员（创建或者移除）
     * @param string $member
     * @param callable $fn 返回值：true添加、false移除
     * @return void
     */
    public function refresh(string $member, callable $fn): void
    {
        if (call_user_func($fn, $member, $this)) {
            static::sAdd($member);
        } else {
            static::sRem($member);
        }
    }
}
