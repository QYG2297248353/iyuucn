<?php
/**
 * 路由配置.
 *
 * @see https://www.workerman.net/doc/webman/route.html
 */

use support\Request;
use Webman\Route;

// 加载路由
foreach (glob(app_path('/*/route/route.php')) as $routeFile) {
    require_once $routeFile;
}

// 回退路由
Route::fallback(function (Request $request) {
    $response = 'OPTIONS' === strtoupper($request->method()) ? response('', 204) : json(['code' => 404, 'msg' => '404 not found']);
    $response->withHeaders([
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => '*',
        'Access-Control-Allow-Headers' => '*',
    ]);

    return $response;
});

// 关闭默认路由
// Route::disableDefaultRoute();
