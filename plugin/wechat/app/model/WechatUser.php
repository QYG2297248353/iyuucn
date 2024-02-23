<?php

namespace plugin\wechat\app\model;

use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\model\Base;
use plugin\wechat\app\enums\WechatUserTypeEnum;

/**
 * 微信用户
 * @property integer $weid 主键(主键)
 * @property integer $uuid 用户id
 * @property string $unionid 微信开放平台
 * @property string $openid 用户标识
 * @property integer $user_type 用户类型
 * @property string $nickname 昵称
 * @property string $remark 运营者对粉丝的备注
 * @property integer $subscribe 用户是否订阅公众号
 * @property integer $subscribe_time 关注时间
 * @property string $tagid_list 标签ID列表
 * @property string $subscribe_scene 关注的渠道来源
 * @property string $qr_scene 二维码扫码场景
 * @property string $qr_scene_str 二维码扫码场景描述
 * @property integer $sync_time 同步时间
 * @property string $token Token
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class WechatUser extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wechat_user';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'weid';

    /**
     * 获取用户信息缓存键名
     * @param string $token
     * @return string
     */
    public static function getUserCacheKey(string $token): string
    {
        return 'WechatUser:token:' . $token;
    }

    /**
     * 获取微信公众号用户
     * @param string $token
     * @return Builder|WechatUser|null
     */
    public static function getWechatUser(string $token): Builder|WechatUser|null
    {
        return static::where('token', '=', $token)->where('user_type', '=', WechatUserTypeEnum::wechat->value)->first();
    }

    /**
     * 判断微信公众号openid是否存在
     * @param string $openid
     * @return Builder|self|null
     */
    public static function openidWechatExists(string $openid): Builder|WechatUser|null
    {
        return static::where('openid', '=', $openid)->where('user_type', '=', WechatUserTypeEnum::wechat->value)->first();
    }

    /**
     * 判断微信小程序openid是否存在
     * @param string $openid
     * @return Builder|self|null
     */
    public static function openidRoutineExists(string $openid): Builder|WechatUser|null
    {
        return static::where('openid', '=', $openid)->where('user_type', '=', WechatUserTypeEnum::routine->value)->first();
    }

    /**
     * 生成唯一的token
     * @param int $uuid
     * @return string
     */
    public static function generateToken(int $uuid): string
    {
        $str = microtime(true) . uniqid(mt_rand());
        return 'IYUU' . $uuid . 'T' . sha1($str);
    }
}
