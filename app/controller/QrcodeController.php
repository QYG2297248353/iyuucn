<?php

namespace app\controller;

use Ledc\Push\UniqidChannel;
use plugin\user\api\Limit;
use plugin\wechat\api\HasResponse;
use plugin\wechat\app\service\WechatQRCodeServices;
use support\Container;
use support\Redis;
use support\Request;
use support\Response;
use Throwable;

/**
 * 微信公众号临时二维码
 */
class QrcodeController
{
    use HasResponse;

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
     * 创建带参数临时二维码
     * - 有效期120秒
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            Limit::perMinute($request->getRealIp(), 2);
            $scene = $request->get('scene', 'iyuu');
            $scene_str = $request->session()->get(UniqidChannel::SESSION_KEY);
            if (empty($scene_str)) {
                return $this->fail('场景值不能为空');
            }

            /** @var WechatQRCodeServices $qrcodeServices */
            $qrcodeServices = Container::get(WechatQRCodeServices::class);
            $response = $qrcodeServices->temporary($scene_str, 120);
            if ($response->isSuccessful()) {
                // 缓存场景和场景值
                Redis::setEx(WechatQRCodeServices::class . ':' . $scene_str, 120, $scene);
                $qrcode_today_number = qrcode_day_number(true);
                return $this->success(array_merge($response->toArray(), compact('qrcode_today_number')));
            }
            return $this->fail($response['errmsg'] ?? '获取二维码失败', $response['errcode'] ?? 400);
        } catch (Throwable $throwable) {
            return $this->fail($throwable->getMessage());
        }
    }
}
