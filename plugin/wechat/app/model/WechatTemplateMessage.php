<?php

namespace plugin\wechat\app\model;

use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\model\Base;
use support\Db;
use support\Redis;

/**
 * 微信模板消息
 * @property integer $mid 自增主键(主键)
 * @property integer $uuid 用户id
 * @property string $msgid 第三方消息id
 * @property string $message 消息体
 * @property string $hash 消息读取sha1(openid+time+盐)
 * @property integer $status 消息投递状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class WechatTemplateMessage extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wechat_template_message';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'mid';

    /**
     * 今日发送模板消息的数量
     */
    public const TODAY_SEND_MESSAGE_NUMBER = 'WechatTemplateMessage:todayNumber:{{date}}';

    /**
     * 今日发送模板消息的数量，缓存KEY
     * @param int $timestamp 时间戳
     * @return string
     */
    public static function keyTodayNumber(int $timestamp): string
    {
        return str_replace('{{date}}', date('Y-m-d', $timestamp), WechatTemplateMessage::TODAY_SEND_MESSAGE_NUMBER);
    }

    /**
     * 递增今日发送模板消息的数量
     * @return int
     */
    public static function incrTodayNumber(): int
    {
        $key = self::keyTodayNumber(time());
        static $scriptSha = null;
        if (!$scriptSha) {
            $script = <<<luascript
if redis.call('set', KEYS[1], ARGV[1], "EX", ARGV[2], "NX") then
    return ARGV[1]
else
    return redis.call('incr', KEYS[1])
end
luascript;
            $scriptSha = Redis::script('load', $script);
        }
        return (int)Redis::rawCommand('evalsha', $scriptSha, 1, $key, 1, 86400 * 10);
    }

    /**
     * 生成提取消息的哈希
     * @param int $uuid
     * @param string $openid
     * @return string
     */
    public static function generateHash(int $uuid, string $openid): string
    {
        // 14 + 13 + 13
        return 'IYUU' . $uuid . 'T' . date('YmdHis') . uniqid() . get_rand_token_string(13);
    }

    /**
     * 获取模型
     * @param string $hash 消息读取sha1
     * @return Builder|self|null
     */
    public static function getByHash(string $hash): self|Builder|null
    {
        return static::where('hash', '=', $hash)->first();
    }

    /**
     * 批量清理X天前的数据
     * @param int $day
     * @return int
     */
    public function clearTemplateMessage(int $day = 3): int
    {
        $ago = time() - 86400 * $day;
        return Db::table($this->getTable())->where('created_at', '<', date('Y-m-d H:i:s', $ago))->delete();
    }
}
