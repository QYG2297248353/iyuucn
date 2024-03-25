<?php

namespace app\controller;

use plugin\admin\app\model\User;
use plugin\wechat\app\model\WechatTemplateMessage;
use support\Redis;
use support\Request;
use support\Response;

/**
 * 默认控制器
 */
class IndexController
{
    /**
     * 首页
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // 今日新增用户数
        $today_user_count = User::where('created_at', '>', date('Y-m-d') . ' 00:00:00')->count();
        // 7天内新增用户数
        $day7_user_count = User::where('created_at', '>', date('Y-m-d H:i:s', time() - 7 * 86400))->count();
        // 30天内新增用户数
        $day30_user_count = User::where('created_at', '>', date('Y-m-d H:i:s', time() - 30 * 86400))->count();

        return view('index/index', [
            'app_key' => config('plugin.ledc.push.app.app_key'),
            'auth' => config('plugin.ledc.push.app.auth'),
            'websocket_port' => parse_url(config('plugin.ledc.push.app.websocket'), PHP_URL_PORT),
            'qrcode_day_number' => qrcode_day_number(),
            'today_send_number' => Redis::get(WechatTemplateMessage::keyTodayNumber(time())) ?: 0,
            'today_user_count' => $today_user_count,
            'day7_user_count' => $day7_user_count,
            'day30_user_count' => $day30_user_count,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function view(Request $request): Response
    {
        return view('index/view', ['name' => 'webman']);
    }

    /**
     * 返回请求头
     * @param Request $request
     * @return Response
     */
    public function header(Request $request): Response
    {
        return json($request->header());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function json(Request $request): Response
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }
}
