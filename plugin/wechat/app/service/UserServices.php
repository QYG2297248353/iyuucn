<?php

namespace plugin\wechat\app\service;

use plugin\user\app\model\User;
use plugin\wechat\app\enums\WechatUserTypeEnum;
use Webman\Event\Event;

/**
 * 用户服务
 */
class UserServices
{
    /**
     * 生成唯一的用户名
     * @param WechatUserTypeEnum $userTypeEnum
     * @return string
     */
    public static function generateUniqueUsername(WechatUserTypeEnum $userTypeEnum): string
    {
        do {
            $username = $userTypeEnum->name . uniqid();
            // 用户名唯一性验证
        } while (User::where('username', $username)->first());
        return $username;
    }

    /**
     * @param string $username
     * @param string $password
     * @param WechatUserTypeEnum $userTypeEnum
     * @return User
     */
    public static function insert(string $username, string $password, WechatUserTypeEnum $userTypeEnum): User
    {
        $user = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'nickname' => $username,
            'user_type' => $userTypeEnum->value,
        ];
        $waUser = new User();
        foreach ($user as $key => $value) {
            $waUser->$key = $value;
        }
        $waUser->avatar = '/app/user/default-avatar.png';
        $waUser->save();
        // 发布注册事件
        Event::emit('user.register', $waUser);

        return $waUser;
    }
}
