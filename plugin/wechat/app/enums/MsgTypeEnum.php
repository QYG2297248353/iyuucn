<?php

namespace plugin\wechat\app\enums;

/**
 * 枚举：微信公众号消息类型
 */
enum MsgTypeEnum: string
{
    /**
     * 文本消息
     */
    case text = 'text';

    /**
     * 图片消息
     */
    case image = 'image';

    /**
     * 语音消息
     */
    case voice = 'voice';

    /**
     * 视频消息
     */
    case video = 'video';

    /**
     * 小视频消息
     */
    case shortvideo = 'shortvideo';

    /**
     * 地理位置消息
     */
    case location = 'location';

    /**
     * 链接消息
     */
    case link = 'link';

    /**
     * 事件推送
     */
    case event = 'event';

    /**
     * @param self $enum
     * @return string
     */
    public static function text(self $enum): string
    {
        return match ($enum) {
            self::text => '文本消息',
            self::image => '图片消息',
            self::voice => '语音消息',
            self::video => '视频消息',
            self::shortvideo => '小视频消息',
            self::location => '地理位置消息',
            self::link => '链接消息',
            self::event => '事件推送',
        };
    }

    /**
     * 名值选择
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

    /**
     * 枚举条目转为数组
     * - 名 => 值
     * @return array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
