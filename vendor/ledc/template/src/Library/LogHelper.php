<?php

namespace Ledc\Template\Library;

use Monolog\Level;
use support\Log;

/**
 * 日志助手
 * - 增加通道的方法，如下
 * - 1.在配置文件config/log.php添加通道
 * - 2.给本类增加注释，格式为：method static void 通道名(string $message, array $context = [], string|Level $level = 'debug')
 * @method static void develop(string $message, array $context = [], string|Level $level = 'debug')
 * @method static void log($level, $message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 */
class LogHelper
{
    /**
     * 在静态上下文中调用一个不可访问方法时，__callStatic() 会被调用
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (self::validLevel($name)) {
            Log::channel()->{$name}(... $arguments);
            return;
        }

        [$message, $context, $level] = $arguments;
        if (!$level instanceof Level) {
            if (empty($level) || !self::validLevel($level)) {
                $level = 'debug';
            }
        }

        Log::channel($name)->log($level, $message, $context ?: []);
    }

    /**
     * 验证日志等级是否有效
     * @param mixed $level
     * @return bool
     */
    protected static function validLevel(mixed $level): bool
    {
        return in_array($level, ['log', 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency']);
    }
}
