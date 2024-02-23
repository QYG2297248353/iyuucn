<?php

namespace Ledc\Template\Middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 语言中间件.
 */
class Lang implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        locale(session('lang', 'zh_CN'));

        return $handler($request);
    }
}
