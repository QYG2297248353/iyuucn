<?php

namespace Ledc\Template\Middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 用户中间件.
 */
class UserMiddleware implements MiddlewareInterface
{
    /**
     * 无需登录的方法
     * - 路由传参数或控制器属性.
     */
    public const noNeedLogin = 'noNeedLogin';

    public function process(Request|\support\Request $request, callable $handler): Response
    {
        $code = 403;
        $msg = '';
        if (!self::canAccess($request, $code, $msg)) {
            $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
        } else {
            $response = 'OPTIONS' == $request->method() ? response('') : $handler($request);
        }

        return $response;
    }

    /**
     * 判断是否需要token.
     */
    protected static function canAccess(Request|\support\Request $request, int &$code = 0, string &$msg = ''): bool
    {
        $controller = $request->controller;
        $action = $request->action;
        $route = $request->route;

        try {
            // 无控制器信息说明是函数调用，函数不属于任何控制器，鉴权操作应该在函数内部完成。
            if ($controller) {
                // 获取控制器鉴权信息
                $class = new \ReflectionClass($controller);
                $properties = $class->getDefaultProperties();
                $noNeedLogin = $properties[self::noNeedLogin] ?? [];
                // 不需要登录
                if (in_array($action, $noNeedLogin) || in_array('*', $noNeedLogin)) {
                    return true;
                }
            } else {
                // 默认路由 $request->route为null，所以需要判断 $request->route 是否为空
                if (!$route) {
                    return true;
                }

                // 路由参数
                if ($route->param(self::noNeedLogin)) {
                    // 指定路由不用登录
                    return true;
                }
            }

            // 需要登录，验证token
            $user = user();
            if (!$user) {
                $msg = '请求缺少token';
                // 401是未登录时固定返回码
                $code = 401;

                return false;
            }

            return true;
        } catch (\ReflectionException $e) {
            $msg = '控制器不存在';
            $code = 404;

            return false;
        } catch (\Throwable $throwable) {
            $msg = $throwable->getMessage();
            $code = 500;

            return false;
        }
    }
}
