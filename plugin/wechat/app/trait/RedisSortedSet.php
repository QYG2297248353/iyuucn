<?php

namespace plugin\wechat\app\trait;

use support\Redis;

/**
 * Redis有序集合
 */
trait RedisSortedSet
{
    /**
     * @var string
     */
    protected string $sortedSetKey;

    /**
     * 向有序集合添加一个或多个成员，或者更新已存在成员的分数
     * @param int $score
     * @param string $member
     * @return void
     */
    public function zAdd(int $score, string $member): void
    {
        Redis::zAdd($this->sortedSetKey, $score, $member);
    }

    /**
     * 移除有序集合中的一个或多个成员
     * @param string $member
     * @return void
     */
    public function zRem(string $member): void
    {
        Redis::zRem($this->sortedSetKey, $member);
    }

    /**
     * 获取有序集合的成员数
     * @return int
     */
    public function zCard(): int
    {
        return Redis::zCard($this->sortedSetKey);
    }

    /**
     * 返回有序集合中指定成员的索引
     * @param string $member
     * @return int|null
     */
    public function zRank(string $member): ?int
    {
        return Redis::zRank($this->sortedSetKey, $member);
    }
}
