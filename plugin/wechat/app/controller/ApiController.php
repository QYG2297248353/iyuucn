<?php

namespace plugin\wechat\app\controller;

use plugin\wechat\app\service\WechatService;
use support\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

/**
 * 微信公众号服务端接口
 */
class ApiController
{
    /**
     * 微信公众号回调
     * @param Request $request
     * @return string
     */
    public function push(Request $request): string
    {
        try {
            $app = WechatService::instance();
            $symfony_request = new SymfonyRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->rawBody());
            $symfony_request->headers = new HeaderBag($request->header());
            $app->setRequestFromSymfonyRequest($symfony_request);

            $server = $app->getServer();
            WechatService::hookServerListener($server);
            $response = $server->serve();

            return $response->getBody()->getContents();
        } catch (Throwable) {
            return 'success';
        }
    }
}
