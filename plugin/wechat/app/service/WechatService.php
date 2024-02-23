<?php

namespace plugin\wechat\app\service;

use BadMethodCallException;
use Closure;
use EasyWeChat\Kernel\Config;
use EasyWeChat\Kernel\Contracts\Server as ServerInterface;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\OfficialAccount\AccessToken;
use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\OfficialAccount\Message;
use EasyWeChat\OfficialAccount\Server;
use Ledc\Push\Pusher;
use Ledc\Push\PushException;
use plugin\admin\app\model\Option;
use plugin\wechat\app\enums\MsgTypeEnum;
use plugin\wechat\app\enums\MsgTypeEventEnum;
use plugin\wechat\app\enums\WebmanEventEnum;
use support\Cache;
use support\exception\BusinessException;
use support\Log;
use support\Redis;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use Webman\Event\Event;

/**
 * 微信服务
 * @mixin Application
 */
class WechatService
{
    /**
     * 微信公众号配置
     * @return Config
     * @throws InvalidArgumentException
     */
    public static function config(): Config
    {
        $config = Option::where('name', 'wechat_config')->value('value');
        if (empty($config)) {
            throw new \InvalidArgumentException('请先配置微信公众号');
        }
        return new Config(json_decode($config, true));
    }

    /**
     * 获取Application实例
     * @return Application
     * @throws InvalidArgumentException
     */
    public static function instance(): Application
    {
        $app = new Application(static::config());
        $app->setCache(Cache::instance());
        return $app;
    }

    /**
     * access_token的存储与更新
     * @return void
     */
    public static function refreshAccessToken(): void
    {
        try {
            $app = self::instance();
            /** @var AccessToken $accessToken */
            $accessToken = $app->getAccessToken();
            $redis = Redis::connection()->client();
            $key = $accessToken->getKey();
            if (!$redis->exists($key) || $redis->ttl($key) < 300) {
                // 在有效期倒计时5分钟内发起调用会获取新的access_token
                $accessToken->refresh();
            }
        } catch (Throwable $throwable) {
            Log::error(__METHOD__ . '刷新access_token异常：' . $throwable->getMessage());
        }
    }

    /**
     * 微信公众号回调
     * @param Server|ServerInterface $server
     * @return void
     * @throws InvalidArgumentException|Throwable
     * @link https://easywechat.com/6.x/official-account/server.html
     */
    public static function hookServerListener(Server|ServerInterface $server): void
    {
        // 调度事件 david 2023年12月17日
        Event::emit(WebmanEventEnum::wechat_server_push->value, $server);

        /**
         * 注册指定消息类型的消息处理器：处理普通消息
         * - 参数 1 为消息类型，也就是 message 中的 MsgType 字段，例如：text
         * - 参数 2 是中间件
         */
        $server->addMessageListener(MsgTypeEnum::text->value, function (Message $message, Closure $next) {
            return $next($message);
        });

        // 处理普通消息：事件推送
        $server->addMessageListener(MsgTypeEnum::event->value, function (Message $message, Closure $next) {
            return $next($message);
        });

        /**
         * 注册指定消息类型的消息处理器：处理事件消息
         * - 参数 1 为事件类型，也就是 message 中的 Event 字段，例如：subscribe
         * - 参数 2 是中间件
         */
        $server->addEventListener(MsgTypeEventEnum::subscribe->value, [static::class, 'handlerSubscribeEventListener']);

        $server->addEventListener(MsgTypeEventEnum::SCAN->value, [static::class, 'handlerSubscribeEventListener']);

        $server->addEventListener(MsgTypeEventEnum::unsubscribe->value, function (Message $message, Closure $next) {
            WechatUserServices::unsubscribe($message);
            return $next($message);
        });

        /**
         * 注册中间件
         */
        $server->with(function (Message $message, Closure $next) {
            $reply = json_decode(Option::where('name', 'wechat_reply')->value('value'), true);
            if (isset($reply['mode']) && isset($reply['hook']) && in_array($reply['mode'], ['hook', 'mix'])) {
                if ($ret = hook_reply($reply['hook'], $message)) {
                    return $ret;
                }
            }

            if (!isset($reply['mode']) || in_array($reply['mode'], ['simple', 'mix'])) {
                if ($message['MsgType'] == 'event' && $message['Event'] == 'subscribe') {
                    return $reply['follow'] ?? '感谢关注我们';
                } elseif ($message['MsgType'] == 'text') {
                    foreach ($reply['rules'] as $key => $value) {
                        if ($message['Content'] == $key) {
                            return $value;
                        }
                    }
                    return $reply['default'] ?? '请点击菜单';
                }
            }
            return $next($message);
        });
    }

    /**
     * 处理关注事件
     * @param Message $message
     * @param Closure $next
     * @return mixed|string
     * @throws PushException
     * @throws BusinessException
     */
    public static function handlerSubscribeEventListener(Message $message, Closure $next): mixed
    {
        $wechatUser = WechatUserServices::subscribe($message);
        // 用户关注的二维码扫码场景
        $rocketQrScene = WechatUserServices::subscribeScene($message, $wechatUser);
        if ($rocketQrScene) {
            $scene = Redis::get(WechatQRCodeServices::class . ':' . $rocketQrScene->scene_id);
            switch ($scene) {
                case 'iyuu':
                    $data = [
                        'type' => $scene,
                        'token' => $wechatUser->token,
                    ];
                    Pusher::trigger($rocketQrScene->scene_id, 'scan', $data);
                    return '您的token是：' . $wechatUser->token;
                default:
                    break;
            }
        }
        return $next($message);
    }

    /**
     * 【公众号】创建自定义菜单
     * @param array $menu 菜单
     * @param array $match_rule 个性化菜单匹配规则
     * @return Response|ResponseInterface
     * @throws InvalidArgumentException
     * @throws TransportExceptionInterface
     */
    public static function menuCreate(array $menu, array $match_rule = []): ResponseInterface|Response
    {
        $api = self::instance()->getClient();
        if (empty($match_rule)) {
            return $api->postJson('/cgi-bin/menu/create', ['button' => $menu]);
        }

        return $api->postJson('/cgi-bin/menu/addconditional', ['button' => $menu, 'matchrule' => $match_rule]);
    }

    /**
     * 在对象中调用一个不可访问方法时，__call() 会被调用
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __call(string $name, array $arguments)
    {
        $app = static::instance();
        if (is_callable([$app, $name])) {
            return call_user_func_array([$app, $name], $arguments);
        }
        throw new BadMethodCallException('未定义的方法：' . $name);
    }

    /**
     * 在静态上下文中调用一个不可访问方法时，__callStatic() 会被调用
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $app = static::instance();
        if (is_callable([$app, $name])) {
            return call_user_func_array([$app, $name], $arguments);
        }
        throw new BadMethodCallException('未定义的方法：' . $name);
    }
}
