<?php

namespace Ledc\Template\Library;

/**
 * 数据结构.
 */
trait HasData
{
    /**
     * 当前数据.
     */
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 当对不可访问属性调用 isset() 或 empty() 时，__isset() 会被调用.
     */
    public function __isset(int|string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * 当对不可访问属性调用 unset() 时，__unset() 会被调用.
     */
    public function __unset(int|string $name)
    {
        unset($this->data[$name]);
    }

    /**
     * 当访问不可访问属性时调用.
     *
     * @return null|array|string
     */
    public function __get(int|string $name)
    {
        return $this->get($name);
    }

    /**
     * 在给不可访问（protected 或 private）或不存在的属性赋值时，__set() 会被调用。
     */
    public function __set(int|string $key, mixed $value)
    {
        $this->set($key, $value);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * 输出Json数据.
     */
    public function toJson(): string
    {
        $json = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_encode error: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * 获取配置项参数
     * - 支持 . 分割符.
     */
    public function get(int|string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->data;
        }
        $keys = explode('.', $key);
        $value = $this->data;
        foreach ($keys as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }

        return $value;
    }

    /**
     * 设置 $this->data.
     */
    public function set(null|int|string $key, mixed $value): static
    {
        if (null === $key) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }
}
