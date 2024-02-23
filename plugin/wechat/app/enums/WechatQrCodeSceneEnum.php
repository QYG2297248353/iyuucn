<?php

namespace plugin\wechat\app\enums;

/**
 * 微信公众号带参数二维码场景
 */
enum WechatQrCodeSceneEnum
{
    /**
     * 获取微信微信公众号模板消息的发送token
     */
    case wechatTemplateMessageToken;

    /**
     * 获取缓存前缀
     * @param WechatQrCodeSceneEnum $enum
     * @return string
     */
    public function cachePrefix(self $enum): string
    {
        return 'WechatQrCodeSceneEnum:' . $enum->name;
    }
}
