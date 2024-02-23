<?php

namespace Ledc\Template\Library;

use support\Redis;

/**
 * Redis实现的令牌桶.
 */
trait HasRedisBucketLimit
{
    /**
     * 令牌桶.
     */
    protected string $bucket;

    /**
     * 桶内令牌上限.
     */
    protected int $bucketMaxLimit = 10;

    /**
     * 获取令牌.
     */
    public function getToken(): bool
    {
        return (bool)Redis::lPop($this->bucket);
    }

    /**
     * 重置令牌桶（加满）.
     */
    public function resetToken(): void
    {
        $this->addToken($this->bucketMaxLimit);
    }

    /**
     * 添加令牌.
     *
     * @param int $num 数量
     *
     * @return int 实际加入的数量
     */
    public function addToken(int $num): int
    {
        $num = max(0, $num);
        $current = $this->lengthToken();
        $num = $this->bucketMaxLimit >= ($current + $num) ? $num : $this->bucketMaxLimit - $current;
        if (0 < $num) {
            $token = array_fill(0, $num, 1);
            Redis::rPush($this->bucket, ...$token);
        }

        return $num;
    }

    /**
     * 桶内令牌个数.
     */
    public function lengthToken(): int
    {
        return Redis::lLen($this->bucket) ?: 0;
    }
}
