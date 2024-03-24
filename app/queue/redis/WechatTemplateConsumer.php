<?php

namespace app\queue\redis;

use EasyWeChat\Kernel\Contracts\AccessToken;
use EasyWeChat\Kernel\Contracts\RefreshableAccessToken;
use Ledc\Redis\Payload;
use Ledc\RedisQueue\ConsumerAbstract;
use Ledc\RedisQueue\Library\Process;
use plugin\wechat\app\enums\WechatTemplateMessageStatusEnum;
use plugin\wechat\app\model\WechatTemplateMessage;
use plugin\wechat\app\service\WechatService;
use plugin\wechat\app\service\WechatTemplateMessageServices;
use RuntimeException;
use support\Container;
use support\Log;
use Throwable;
use Workerman\Timer;

/**
 * 消费者：发送微信模板消息
 */
class WechatTemplateConsumer extends ConsumerAbstract
{
    /**
     * 发送模版消息的接口URL
     */
    public const TEMPLATE_SEND_URL = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';

    /**
     * @var WechatTemplateMessageServices
     */
    protected WechatTemplateMessageServices $services;

    /**
     * 微信accessToken管理类
     * @var RefreshableAccessToken|AccessToken|null
     */
    protected RefreshableAccessToken|AccessToken|null $accessToken;

    /**
     * 构造函数
     */
    public function __construct()
    {
        try {
            $this->services = Container::get(WechatTemplateMessageServices::class);
            $this->saveToDatabase();
            $this->accessToken = WechatService::instance()->getAccessToken();
        } catch (Throwable) {
        }
    }

    /**
     * 入库
     * @return void
     */
    protected function saveToDatabase(): void
    {
        if (0 === Process::worker()?->id) {
            Timer::add(5, function () {
                $this->services->batchSave();
            });
        }
    }

    /**
     * 要消费的队列名
     */
    public static function queue(): string
    {
        return 'wechat_template_consumer';
    }

    /**
     * 连接名，对应 config/redis-queue.php 里的连接
     * @return string
     */
    public static function connection(): string
    {
        return 'default';
    }

    /**
     * 消费方法
     *  - 消费过程中没有抛出异常和Error视为消费成功；否则消费失败,进入重试队列
     * @param mixed $data
     * @param Payload $payload
     * @return void
     */
    public function consume(mixed $data, Payload $payload): void
    {
        if (is_null($this->accessToken)) {
            throw new RuntimeException('队列发送模板消息时accessToken的值为null');
        }

        try {
            $uid = $data['uid'];
            $openid = $data['openid'];
            $template_id = $data['template_id'] ?? '';
            $hash = WechatTemplateMessage::generateHash($uid, $openid);
            $url = str_replace('{{hash}}', $hash, $data['url']);
            $body = $this->defaultTemplate($openid, $data['text'], $url, $template_id);
            $response = $this->post(self::TEMPLATE_SEND_URL . $this->accessToken->getToken(), $body);
            if (is_bool($response)) {
                // 发送失败
                $this->retry($payload);
                return;
            }

            if (isset($response->errmsg) && 'ok' === $response->errmsg) {
                // 仅保留text、desp字段
                $message = array_filter($data, function ($key) {
                    return in_array($key, ['text', 'desp']);
                }, ARRAY_FILTER_USE_KEY);

                $db = [
                    'uuid' => $uid,
                    'msgid' => $response->msgid,
                    'message' => json_encode($message, JSON_UNESCAPED_UNICODE),
                    'hash' => $hash,
                    'status' => WechatTemplateMessageStatusEnum::pending->value,
                ];
                $this->services->push($db);
            } else {
                if (isset($response->errcode)) {
                    if (43004 == $response->errcode) {
                        //用户未关注
                        return;
                    }
                    if (43101 == $response->errcode) {
                        //用户拒绝接受消息
                        return;
                    }
                    Log::warning('发送模版消息失败：' . json_encode($response, JSON_UNESCAPED_UNICODE));
                }
                $this->retry($payload);
            }
        } catch (Throwable $throwable) {
            Log::error('异步发送微信模板消息时异常：' . $throwable->getMessage());
        }
    }

    /**
     * POST请求
     * - Content-Type: application/json; charset=UTF-8
     * @param string $url
     * @param array $params
     * @return bool|object
     */
    protected function post(string $url, array $params): object|bool
    {
        static $ch = null;

        if (null === $ch) {
            $ch = curl_init();
            if (false === $ch) {
                throw new RuntimeException('cURl 初始化失败');
            }
        }
        if (function_exists('curl_reset')) {
            curl_reset($ch);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (0 === stripos($url, 'https://')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $content = curl_exec($ch);
        //curl_close($ch);
        return is_bool($content) ? $content : json_decode($content);
    }

    /**
     * 重试
     * @param Payload $payload
     * @return void
     */
    protected function retry(Payload $payload): void
    {
        $payload->setMaxAttempts(3)->release($payload->attempts ? $payload->attempts * 10 : 10);
    }

    /**
     * 模板消息公共部分
     * @param string $openid 用户openid
     * @param string $templateId 模板ID
     * @param array $data 模板数据
     * @param string $url 跳转的url
     * @return array
     */
    protected function commonStruct(string $openid, string $templateId, array $data = [], string $url = ''): array
    {
        return [
            'touser' => $openid,
            'template_id' => $templateId,
            'data' => $data,
            'url' => $url
        ];
    }

    /**
     * OPENTM207965242    设备通知    IT科技 - IT软件与服务
     * @param string $openid 用户openid
     * @param string $title 标题
     * @param string $url 点击卡片跳转的URL
     * @param string $templateId 模板ID
     * @return array
     */
    protected function defaultTemplate(string $openid = '', string $title = '', string $url = '', string $templateId = ''): array
    {
        $templateId = $templateId ?: '-WVOZjuocgFdQh4iLhGzOed2_793E_7im-q4SO631Ck';
        //通知内容最多显示100字符
        if (empty($title)) {
            $title = '未填写';
        } else {
            $title = strlen($title) <= 40 ? $title : '点「详情」查看';
        }

        /*
         * {{first.DATA}}
         * 通知内容：{{keyword1.DATA}}
         * 通知时间：{{keyword2.DATA}}
         * {{remark.DATA}}
         */
        $data = [
            //'first'	=>	['value' => $title, 'color' => '#0066CC'],
            'keyword1' => ['value' => $title, 'color' => '#339933'],
            'keyword2' => ['value' => date("Y-m-d H:i:s"), 'color' => '#339933'],
            //'remark' =>	['value' => "点「查看详情」看通知内容。本消息的标题、内容及「详情」页面的通知内容均为您自行填写，调用接口发送，并非广告营销内容。退订可点击右下角[功能-取消通知]来关闭或直接取消关注。",'color' => '#7d7d7d'],
        ];
        return $this->commonStruct($openid, $templateId, $data, $url);
    }
}
