<?php

namespace Ledc\Push;

use Ledc\Pipeline\Pipeline;
use Ledc\Push\Pipelines\UniqidPipeline;
use Ledc\Push\Pipelines\WebmanAdmin;
use RuntimeException;
use support\Request;
use Throwable;

/**
 * PushApi调用类
 */
class Pusher
{
    /**
     * @var Api|null
     */
    protected static ?Api $api = null;

    /**
     * 触发客户端事件
     * @param string $channels
     * @param string $event
     * @param mixed $data
     * @param string|null $socket_id
     * @return bool
     * @throws PushException
     */
    public static function trigger(string $channels, string $event, mixed $data, string $socket_id = null): bool
    {
        return static::api()->trigger($channels, $event, $data, $socket_id);
    }

    /**
     * @return Api
     */
    public static function api(): Api
    {
        if (null === static::$api) {
            try {
                // webman下可以直接使用config获取配置，非webman环境需要手动写入相应配置
                static::$api = new Api(
                    str_replace('0.0.0.0', '127.0.0.1', config('plugin.ledc.push.app.api')),
                    config('plugin.ledc.push.app.app_key'),
                    config('plugin.ledc.push.app.app_secret')
                );
            } catch (PushException $exception) {
                throw new RuntimeException($exception->getMessage());
            }
        }
        return static::$api;
    }

    /**
     * 判断当前用户是否有权限监听channel_name
     * @param Request $request
     * @return bool
     */
    public static function hasAuthority(Request $request): bool
    {
        try {
            $pipeline = new Pipeline();
            return $pipeline->send($request)
                ->through(config('plugin.ledc.push.app.pipeline', [
                    [WebmanAdmin::class, 'process'],
                    [UniqidPipeline::class, 'process'],
                ]))
                ->then(function (Request $request) {
                    return false;
                });
        } catch (Throwable $throwable) {
            return false;
        }
    }
}
