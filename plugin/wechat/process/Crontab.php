<?php

namespace plugin\wechat\process;

use Ledc\Push\Pusher;
use plugin\wechat\app\model\WechatTemplateMessage;
use plugin\wechat\app\service\WechatService;
use support\Redis;
use Workerman\Timer;
use Workerman\Worker;

/**
 * 进程：定时任务
 */
class Crontab
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
        $this->startAccessToken();
        $this->startClearTemplateMessage();
    }

    /**
     * 微信中控服务器统一获取和刷新access_token
     * @return void
     */
    protected function startAccessToken(): void
    {
        Timer::add(60, function () {
            WechatService::refreshAccessToken();
        });
    }

    /**
     * 批量清理X天前的数据
     * @return void
     */
    protected function startClearTemplateMessage(): void
    {
        Timer::add(3600, function () {
            (new WechatTemplateMessage())->clearTemplateMessage();
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
