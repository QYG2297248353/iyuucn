<?php

namespace process;

use Ledc\Push\Pusher;
use plugin\wechat\app\model\WechatTemplateMessage;
use plugin\wechat\app\service\WechatTemplateMessageServices;
use support\Container;
use support\Redis;
use Workerman\Timer;
use Workerman\Worker;

/**
 * 进程：批量保存微信模板消息
 */
class BatchSave
{
    protected static Worker $worker;

    /**
     * 启动后在子进程执行
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker): void
    {
        static::$worker = $worker;
        Timer::add(5, function () {
            /** @var WechatTemplateMessageServices $services */
            $services = Container::get(WechatTemplateMessageServices::class);
            $services->batchSave();

            // 今日模板消息发送数量
            $key = WechatTemplateMessage::keyTodayNumber(time());
            $number = Redis::get($key) ?: 0;
            Pusher::trigger('online_status', 'today_send_number', (int)$number);
        });
    }

    /**
     * 获取Worker实例
     * @return Worker
     */
    public static function getWorker(): Worker
    {
        return self::$worker;
    }
}
