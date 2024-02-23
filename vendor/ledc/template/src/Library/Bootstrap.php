<?php

namespace Ledc\Template\Library;

use Workerman\Timer;
use Workerman\Worker;

/**
 * 进程启动时onWorkerStart时运行的回调配置.
 *
 * @see https://learnku.com/articles/6657/model-events-and-observer-in-laravel
 */
class Bootstrap implements \Webman\Bootstrap
{
    public static function start(?Worker $worker): void
    {
        if (!class_exists('\Workerman\Lib\Timer')) {
            class_alias(Timer::class, '\Workerman\Lib\Timer');
        }

        // 【新增】依次触发的顺序是：
        // saving -> creating -> created -> saved

        // 【更新】依次触发的顺序是:
        // saving -> updating -> updated -> saved

        // updating 和 updated 会在数据库中的真值修改前后触发。
        // saving 和 saved 则会在 Eloquent 实例的 original 数组真值更改前后触发
    }
}
