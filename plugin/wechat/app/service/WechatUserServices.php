<?php

namespace plugin\wechat\app\service;

use EasyWeChat\OfficialAccount\Message;
use plugin\wechat\app\enums\WebmanEventEnum;
use plugin\wechat\app\enums\WechatUserTypeEnum;
use plugin\wechat\app\model\WechatUser;
use plugin\wechat\app\rockets\RocketQrScene;
use support\exception\BusinessException;
use Throwable;
use Webman\Event\Event;

/**
 * 微信用户服务
 */
class WechatUserServices
{
    /**
     * 微信公众号：关注
     * @param Message $message
     * @return WechatUser
     * @throws BusinessException
     */
    public static function subscribe(Message $message): WechatUser
    {
        $openid = $message->FromUserName;
        $wechatUser = WechatUser::openidWechatExists($openid);
        if ($wechatUser instanceof WechatUser) {
            $wechatUser->subscribe = 1;
        } else {
            $username = UserServices::generateUniqueUsername(WechatUserTypeEnum::wechat);
            $user = UserServices::insert($username, $openid . uniqid(), WechatUserTypeEnum::wechat);
            $wechat = self::wechatUserInfo($openid);
            $wechatUser = new WechatUser();
            $wechatUser->uuid = $user->id;
            if ($unionid = $wechat['unionid'] ?? '') {
                $wechatUser->unionid = $unionid;
            }
            $wechatUser->openid = $openid;
            $wechatUser->user_type = WechatUserTypeEnum::wechat->value;
            $wechatUser->nickname = $username;
            $wechatUser->remark = $wechat['remark'] ?? '';
            $wechatUser->subscribe = 1;
            $wechatUser->subscribe_time = $wechat['subscribe_time'];
            $wechatUser->subscribe_scene = $wechat['subscribe_scene'] ?? '';
            $wechatUser->qr_scene = $wechat['qr_scene'] ?? '';
            $wechatUser->qr_scene_str = $wechat['qr_scene_str'] ?? '';
            $wechatUser->sync_time = time();
            $wechatUser->token = WechatUser::generateToken($user->id);
        }
        $wechatUser->save();
        // 调度事件
        Event::emit(WebmanEventEnum::wechat_subscribe->value, $wechatUser);
        return $wechatUser;
    }

    /**
     * 微信公众号：取消关注
     * @param Message $message
     * @return void
     */
    public static function unsubscribe(Message $message): void
    {
        $openid = $message->FromUserName;
        $wechatUser = WechatUser::openidWechatExists($openid);
        if ($wechatUser instanceof WechatUser) {
            $wechatUser->subscribe = 0;
            $wechatUser->save();
            // 调度事件
            Event::emit(WebmanEventEnum::wechat_unsubscribe->value, $wechatUser);
        }
    }

    /**
     * 用户关注的二维码扫码场景
     * @param Message $message
     * @param WechatUser $wechatUser
     * @return RocketQrScene|null
     */
    public static function subscribeScene(Message $message, WechatUser $wechatUser): ?RocketQrScene
    {
        $rocketQrScene = null;
        //事件KEY值
        $event_key = $message['EventKey'] ?? null;
        //二维码的ticket
        $ticket = $message['Ticket'] ?? null;
        if ($event_key && $ticket) {
            /**
             * 扫描带参数二维码关注
             * - 事件KEY值，qrscene_为前缀,后面为二维码scene_id
             */
            if (str_starts_with($event_key, 'qrscene_')) {
                $scene_id = substr($event_key, strlen('qrscene_'));
            } else {
                $scene_id = $event_key;
            }

            $rocketQrScene = new RocketQrScene($scene_id, $ticket, $message, $wechatUser);
            // 调度事件
            Event::emit(WebmanEventEnum::wechat_subscribe_qrscene->value, $rocketQrScene);
        }
        return $rocketQrScene;
    }

    /**
     * 获取用户基本信息
     * @param string $openid 普通用户的标识，对当前公众号唯一
     * @return array
     * @throws BusinessException
     */
    public static function wechatUserInfo(string $openid): array
    {
        try {
            $app = WechatService::instance();
            $api = $app->getClient();
            $options = [
                'query' => [
                    'openid' => $openid,
                ]
            ];
            $response = $api->get('/cgi-bin/user/info', $options)->throw(false);
            if ($response->isSuccessful()) {
                return $response->toArray();
            }
            $resp = json_decode($response->toJson());
            throw new BusinessException($resp->errmsg ?? '获取用户信息失败', $resp->errcode ?? 400);
        } catch (Throwable $throwable) {
            throw new BusinessException($throwable->getMessage(), $throwable->getCode());
        }
    }
}
