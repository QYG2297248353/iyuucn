<?php

namespace plugin\wechat\app\trait;

use support\Redis;

/**
 * Redis实现的令牌桶
 */
trait HasRedisBucketLimit
{
    /**
     * 令牌桶Key
     */
    protected string $bucketKey;

    /**
     * 桶内令牌上限
     */
    protected int $bucketMaxLimit = 10;

    /**
     * 获取令牌
     */
    public function getToken(): bool
    {
        return (bool)Redis::lPop($this->bucketKey);
    }

    /**
     * 重置令牌桶（加满）
     */
    public function resetToken(): void
    {
        $this->addToken($this->bucketMaxLimit);
    }

    /**
     * 添加令牌
     * @param int $num 数量
     * @return int 实际加入的数量
     */
    public function addToken(int $num): int
    {
        $num = max(0, $num);
        $diff = $this->bucketMaxLimit - $this->lengthToken();
        $num = min($diff, $num);
        if (0 < $num) {
            $token = array_fill(0, $num, 1);
            Redis::rPush($this->bucketKey, ...$token);
        }

        return $num;
    }

    /**
     * 桶内令牌个数
     */
    public function lengthToken(): int
    {
        return Redis::lLen($this->bucketKey) ?: 0;
    }
}
