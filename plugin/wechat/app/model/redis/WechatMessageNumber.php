<?php

namespace plugin\wechat\app\model\redis;

use plugin\wechat\app\trait\HasRedisSortedSet;

/**
 * 统计微信模板消息发送数量
 */
class WechatMessageNumber
{
    use HasRedisSortedSet;

    /**
     * 成功数
     * @var string
     */
    protected string $successKey;
    /**
     * 失败数
     * @var string
     */
    protected string $failKey;
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->successKey = 'WechatMessageNumber:Success';
        $this->failKey = 'WechatMessageNumber:Fail';
        $this->sortedSetKey = $this->successKey;
    }

    /**
     * 选择集合KEY
     * @param bool $success
     * @return $this
     */
    public function select(bool $success): static
    {
        $this->sortedSetKey = $success ? $this->successKey : $this->failKey;
        return $this;
    }

    /**
     * 递增
     * @param string $member
     * @return int|string
     */
    public function incr(string $member): int|string
    {
        if ($this->zRank($member)) {
            return $this->zIncrBy(1, $member);
        } else {
            return $this->zAdd(1, $member);
        }
    }
}
