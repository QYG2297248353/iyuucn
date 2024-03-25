<?php

namespace process;

use plugin\wechat\app\service\WechatTemplateMessageServices;
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
        $services = new WechatTemplateMessageServices();
        Timer::add(5, function () use ($services) {
            $services->batchSave();
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
