<?php

namespace plugin\wechat\app\controller;

use app\queue\redis\WechatTemplateConsumer;
use app\RedisCache;
use plugin\wechat\app\model\WechatTemplateMessage;
use plugin\wechat\app\model\WechatUser;
use plugin\wechat\app\service\Markdown;
use support\Redis;
use support\Request;
use support\Response;
use Tinywan\LimitTraffic\RateLimiter;

/**
 * 微信模板消息
 */
class TemplateMessageController
{
    /**
     * 默认
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return response(__CLASS__);
    }

    /**
     * 发送
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function send(Request $request, string $token = ''): Response
    {
        $token = $token ?: $request->input('token');

        if (WechatUser::isUserRefuse($token)) {
            return $this->fail('您的微信已设置拒绝接受公众号消息');
        }

        // 检查：防穷举token（同IP 1分钟失败10次，IP封禁30分钟）
        $limit_ip = 'TemplateMessageController:limit_ip:' . $request->getRealIp();
        if (10 <= (int)Redis::get($limit_ip)) {
            return $this->fail('token验证失败', 403);
        }

        $user = RedisCache::get(WechatUser::getUserCacheKey($token), function () use ($token) {
            return WechatUser::getWechatUser($token)?->toArray();
        }, 1800);
        if (empty($user)) {
            // 设置：防穷举token（同IP 1分钟失败10次，IP封禁30分钟）
            if (10 <= $this->redis_incr($limit_ip, 60)) {
                Redis::expire($limit_ip, 1800);
            }
            return $this->fail('token验证失败', 403);
        }
        $text = $request->input('text');
        if (empty($text)) {
            return $this->fail('text不能为空');
        }

        // 接口限流
        if ($result = RateLimiter::traffic()) {
            return new Response($result['status'], [
                'Content-Type' => 'application/json',
                'X-Rate-Limit-Limit' => $result['limit'],
                'X-Rate-Limit-Remaining' => $result['remaining'],
                'X-Rate-Limit-Reset' => $result['reset']
            ], json_encode($result['body']));
        }

        // 投递待发送消息到队列
        $data = [
            'uid' => $user['uuid'],
            'token' => $token,
            'openid' => $user['openid'],
            'template_id' => '',
            'text' => $text,
            'desp' => $request->input('desp'),
            'url' => 'https://' . $request->host() . '/{{hash}}.read'
        ];
        return WechatTemplateConsumer::send($data) ? $this->success() : $this->fail();
    }

    /**
     * 读取
     * @param Request $request
     * @param string $hash
     * @return Response
     */
    public function read(Request $request, string $hash = ''): Response
    {
        $token = $hash ?: $request->get('hash');
        $template = WechatTemplateMessage::getByHash($token);
        if (!$template) {
            return $this->fail('消息有效期3天，过期自动失效！');
        }
        $message = json_decode($template->message);
        $html = '<h1>' . $message->text . '</h1>';
        $html .= $message->desp ? Markdown::convert($message->desp) : '';
        return view('template-message/read', [
            'text' => $message->text,
            'html' => $html
        ]);
    }

    /**
     * 获取成功时候的响应码
     * @return int
     */
    protected function getSuccessCode(): int
    {
        return 0;
    }

    /**
     * 失败响应
     * @param string $errmsg 消息
     * @param int $errcode 响应码
     * @return Response
     */
    final protected function fail(string $errmsg = 'fail', int $errcode = 400): Response
    {
        return $this->json($this->getSuccessCode() === $errcode ? 400 : $errcode, $errmsg);
    }

    /**
     * 成功响应
     * @param array $data 数据
     * @param string $errmsg 消息
     * @return Response
     */
    final protected function success(array $data = [], string $errmsg = 'ok'): Response
    {
        return $this->json($this->getSuccessCode(), $errmsg, $data);
    }

    /**
     * 响应
     * @param int $errcode 响应码
     * @param string $errmsg 消息
     * @param array $data 数据
     * @return Response
     */
    final protected function json(int $errcode, string $errmsg = 'ok', array $data = []): Response
    {
        return json(['errcode' => $errcode, 'errmsg' => $errmsg, 'data' => $data]);
    }

    /**
     * Redis的incr指令，支持设置ttl
     * - 使用lua脚本实现
     * @param string $key 缓存的key
     * @param int $ttl 存活的ttl，单位秒
     * @return int
     */
    protected function redis_incr(string $key, int $ttl = 10): int
    {
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
        return (int)Redis::rawCommand('evalsha', $scriptSha, 1, $key, 1, $ttl);
    }
}
