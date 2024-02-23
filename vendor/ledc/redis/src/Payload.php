<?php

namespace Ledc\Redis;

/**
 * 队列任务有效载荷
 * @property-read string $id ID
 * @property-read int $time 开始的时间戳
 * @property-read int $delay 是否延迟消息
 * @property-read int $attempts 已重试次数
 * @property-read string $queue 队列名
 * @property-read mixed $data 数据
 */
class Payload
{
    /**
     * 是否释放回队列
     * @var true
     */
    private bool $released = false;

    /**
     * 消费失败后，重试次数
     * @var int
     */
    private int $max_attempts = 1;

    /**
     * 重试间隔的秒数
     * @var int
     */
    private int $retry_delay = 1;

    /**
     * 构造函数
     * @param array $package 队列数据包
     */
    public function __construct(protected readonly array $package)
    {
    }

    /**
     * 获取：消费失败后，重试次数
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->max_attempts;
    }

    /**
     * 设置：消费失败后，重试次数
     * @param int $max_attempts
     * @return self
     */
    public function setMaxAttempts(int $max_attempts): self
    {
        $this->max_attempts = max($this->max_attempts, $max_attempts);
        return $this;
    }

    /**
     * 将任务重新释放回队列.
     * @param int $retry_delay 重试间隔，单位秒
     * @return self
     */
    public function release(int $retry_delay = 0): self
    {
        $this->released = true;
        $this->retry_delay = max($this->retry_delay, $retry_delay);
        return $this;
    }

    /**
     * 确定任务是否释放回队列.
     * @return bool
     */
    public function isReleased(): bool
    {
        return $this->released;
    }

    /**
     * 重试间隔，单位秒
     * @return int
     */
    public function getRetryDelay(): int
    {
        return $this->retry_delay;
    }

    /**
     * 当访问不可访问属性时调用.
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 获取配置项参数
     *  - 支持 . 分割符.
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    final public function get(string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->package;
        }
        $keys = explode('.', $key);
        $value = $this->package;
        foreach ($keys as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }

        return $value;
    }
}
