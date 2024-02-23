<?php

namespace plugin\wechat\app\enums;

/**
 * webman事件枚举
 */
enum WebmanEventEnum: string
{
    /**
     * 微信公众号：服务器收到回调消息
     */
    case wechat_server_push = 'wechat.server.push';

    /**
     * 微信公众号：用户关注
     */
    case wechat_subscribe = 'wechat.subscribe';

    /**
     * 微信公众号：用户扫描带参数二维码关注
     */
    case wechat_subscribe_qrscene = 'wechat.subscribe.qrscene';

    /**
     * 微信公众号：用户取消关注
     */
    case wechat_unsubscribe = 'wechat.unsubscribe';
}
