<?php

namespace app\controller;

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
        return view('index/index', [
            'app_key' => config('plugin.ledc.push.app.app_key'),
            'auth' => config('plugin.ledc.push.app.auth'),
            'websocket_port' => parse_url(config('plugin.ledc.push.app.websocket'), PHP_URL_PORT),
            'qrcode_day_number' => qrcode_day_number(),
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
