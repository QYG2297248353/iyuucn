<?php

namespace plugin\wechat\app\enums;

use InvalidArgumentException;

/**
 * 微信模板消息投递状态
 */
enum WechatTemplateMessageStatusEnum: int
{
    /**
     * 默认值
     */
    case default = 0;
    /**
     * 成功
     */
    case success = 1;
    /**
     * 处理中
     */
    case pending = 2;
    /**
     * 失败
     */
    case failed = 3;
    /**
     * 请求错误
     */
    case error = 4;

    /**
     * 获取枚举
     * @param string $name
     * @return self
     */
    public static function create(string $name): self
    {
        return self::from(self::getValue($name));
    }

    /**
     * 检查name
     * @param string $name
     * @return int
     */
    public static function getValue(string $name): int
    {
        $list = self::toArray();
        if (!array_key_exists($name, $list)) {
            throw new InvalidArgumentException('状态不存在');
        }

        return $list[$name];
    }

    /**
     * 枚举条目转为数组
     * - 名 => 值
     * @return array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * @param WechatTemplateMessageStatusEnum $enum
     * @return string
     */
    public static function text(self $enum): string
    {
        return match ($enum) {
            self::default => '默认',
            self::success => '成功',
            self::pending => '处理中',
            self::failed => '失败',
            self::error => '请求错误',
        };
    }

    /**
     * @return array
     */
    public static function select(): array
    {
        $rs = [];
        foreach (self::cases() as $enum) {
            $rs[self::text($enum)] = $enum->value;
        }
        return $rs;
    }
}
