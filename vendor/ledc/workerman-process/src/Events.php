<?php

namespace Ledc\WorkermanProcess;

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);
use Exception;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Lib\Gateway;
use Workerman\Timer;
use Workerman\Worker;

/**
 * 事件处理主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 关闭未认证链接的间隔时间(单位:秒)
     * - 客户端必须在间隔时间内发送认证信息
     */
    const CLOSE_CLIENT_INTERVAL = 30;
    /**
     * 认证定时器的session键名
     */
    const AUTH_TIMER_ID = 'auth_timer_id';

    /**
     * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
     * - https://www.workerman.net/doc/gateway-worker/on-worker-start.html
     *
     * @param Worker $worker
     */
    public static function onWorkerStart(Worker $worker): void
    {
    }

    /**
     * 生成验证字符串
     * @param string $client_id 全局唯一的客户端socket连接标识
     * @param int $timestamp 时间戳
     * @return string MD5散列值
     */
    protected static function createAuthString(string $client_id, int $timestamp): string
    {
        $salt = uniqid() . mt_rand();
        return md5($salt . $client_id . $timestamp . $salt);
    }

    /**
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发的回调函数
     * @param string $client_id 全局唯一的客户端socket连接标识
     * @throws Exception
     */
    public static function onConnect(string $client_id): void
    {
        /**
         * 定时关闭这个链接
         * 阻止关闭连接的方法： 方法一.间隔时间内绑定用户uid； 方法二.间隔时间内主动删除此定时器
         */
        $auth_timer_id = Timer::add(static::CLOSE_CLIENT_INTERVAL, function ($client_id) {
            // 返回client_id绑定的uid，如果client_id没有绑定uid，则返回null
            $uid = Gateway::getUidByClientId($client_id);
            if (empty($uid)) {
                static::onConnectTimeoutNotUid($client_id);
            } else {
                static::onConnectExistUid($client_id);
            }
        }, [$client_id], false);

        /**
         * 保存定时器timerId和连接的auth到用户Session中
         * - timerId用途：管理员登录时可以销毁定时器。
         * - auth用途：外部应用在调用Gateway::bindUid方法前必须验证Auth，防止客户端伪造client_id造成的绑定关系混乱。
         */
        $timestamp = time();
        $auth = self::createAuthString($client_id, $timestamp);
        $session = [
            self::AUTH_TIMER_ID => $auth_timer_id,
            'auth' => $auth
        ];
        Gateway::updateSession($client_id, $session);

        /**
         * 当有客户端连接时，将client_id返回
         */
        $data = [
            'event' => 'init',
            'client_id' => $client_id,
            'timestamp' => $timestamp,
            'auth' => $auth
        ];
        Gateway::sendToCurrentClient(json_encode($data, JSON_UNESCAPED_UNICODE));
        static::onConnectNotify($client_id);
    }

    /**
     * 超时未绑定
     * @param string $client_id
     * @return void
     */
    protected static function onConnectTimeoutNotUid(string $client_id): void
    {
        // 关闭未绑定uid的连接
        Gateway::closeClient($client_id);
    }

    /**
     * 定时器检测到当前链接已绑定UID
     * @param string $client_id
     * @return void
     */
    protected static function onConnectExistUid(string $client_id): void
    {
        // 已绑定，更新session
        $session = Gateway::getSession($client_id);
        unset($session[self::AUTH_TIMER_ID]);
        Gateway::updateSession($client_id, $session);
    }

    /**
     * 向所有在线的客户端推送 -> 在线连接总数
     * @param string $client_id
     * @return void
     * @throws Exception
     */
    protected static function onConnectNotify(string $client_id): void
    {
        $rs = [
            'event' => 'onConnect',
            'online' => Gateway::getAllClientIdCount()
        ];
        Gateway::sendToAll(json_encode($rs, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 当客户端连接上gateway完成websocket握手时触发的回调函数
     * - 此回调只有gateway为websocket协议并且gateway没有设置onWebSocketConnect时才有效
     * - https://www.workerman.net/doc/gateway-worker/on-web-socket-connect.html
     * @param int|string $client_id 全局唯一的客户端socket连接标识
     * @param mixed $data websocket握手时的http头数据，包含get、server等变量
     * @return void
     */
    public static function onWebSocketConnect(string $client_id, $data)
    {
        //
    }

    /**
     * 当客户端发来数据(Gateway进程收到数据)后触发的回调函数
     * - https://www.workerman.net/doc/gateway-worker/on-messsge.html
     * @param int|string $client_id 全局唯一的客户端socket连接标识
     * @param mixed $message 完整的客户端请求数据，数据类型取决于Gateway所使用协议的decode方法的返回值类型
     * @throws Exception
     */
    public static function onMessage(string $client_id, $message): void
    {
        //简易ping、pong
        if ('ping' === $message) {
            Gateway::sendToCurrentClient('pong');
            return;
        }

        $data = json_decode($message, true);
        if (empty($data)) {
            return;
        }

        switch ($data['event'] ?? '') {
            case 'ping':
                Gateway::sendToCurrentClient('{"event":"pong"}');
                break;
            case 'keepalive':
                if (!empty($_SESSION[self::AUTH_TIMER_ID])) {
                    Timer::del($_SESSION[self::AUTH_TIMER_ID]);
                    unset($_SESSION[self::AUTH_TIMER_ID]);
                    Gateway::sendToCurrentClient('{"event":"keepalive","status":"ok"}');
                }
                break;
            default:
                static::onMessageHandler($client_id, $data, $message);
                break;
        }
    }

    /**
     * @param string $client_id
     * @param array $data
     * @param mixed $original_message 完整的客户端请求数据，数据类型取决于Gateway所使用协议的decode方法的返回值类型
     * @return void
     */
    protected static function onMessageHandler(string $client_id, array $data, $original_message): void
    {
        //todo...
    }

    /**
     * 客户端与Gateway进程的连接断开时触发
     * - https://www.workerman.net/doc/gateway-worker/on-close.html
     * @param int|string $client_id 全局唯一的client_id
     * @throws Exception
     */
    public static function onClose(string $client_id): void
    {
        /**
         * 向所有在线的客户端推送 -> 在线连接总数
         */
        $rs = [
            'event' => 'onClose',
            'online' => Gateway::getAllClientIdCount()
        ];
        Gateway::sendToAll(json_encode($rs, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 当businessWorker进程退出时触发
     * - 每个进程生命周期内都只会触发一次（某些情况将不会触发onWorkerStop）
     * - https://www.workerman.net/doc/gateway-worker/on-worker-stop.html
     *
     * @param BusinessWorker $businessWorker
     */
    public static function onWorkerStop(BusinessWorker $businessWorker)
    {
    }

    /**
     * 将client_id与uid绑定
     * @param string $client_id
     * @param string $uid
     * @param string $auth
     * @return void
     * @throws Exception
     */
    public static function bindUid(string $client_id, string $uid, string $auth): bool
    {
        if (empty($auth) || strlen($auth) < 32) {
            throw new Exception('auth验证参数为空');
        }

        $session = Gateway::getSession($client_id);
        $_auth = $session['auth'] ?? $session['uniqid'] ?? '';
        if (empty($_auth)) {
            throw new Exception('session验证参数为空');
        }

        if (hash_equals($auth, $_auth)) {
            Gateway::bindUid($client_id, $uid);
            return true;
        }

        return false;
    }

    /**
     * 将client_id加入组
     * @param string $client_id
     * @param array|array<int>|array<string> $groups
     * @return void
     */
    public static function joinGroup(string $client_id, array $groups): void
    {
        if (empty($client_id) || empty($group)) {
            return;
        }

        foreach ($groups as $group) {
            Gateway::joinGroup($client_id, $group);
        }
    }
}
