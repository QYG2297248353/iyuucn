<?php

use support\Container;
use support\Redis;

/**
 * 今日调用临时二维码接口的次数
 * @param bool $incr
 * @return int
 */
function qrcode_day_number(bool $incr = false): int
{
    $key = 'WechatQRCodeServices:day_' . date('Ymd');
    if (!Redis::exists($key)) {
        Redis::setEx($key, 86400 * 7, 0);
    }
    return $incr ? Redis::incr($key) : Redis::get($key);
}

/**
 * @param int $length
 * @return string
 */
function get_rand_token_string(int $length = 32): string
{
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = "";
    for ($i = 0; $i < $length; $i++) {
        $key .= $str[mt_rand(0, 61)];    //生成php随机数
    }
    return $key;
}

/**
 * 钩子
 * @param string $handler
 * @param $data
 * @return mixed|void
 */
function hook_reply(string $handler, $data)
{
    $path = str_replace('/', '\\', $handler);
    list($controller, $action) = explode('@', $path);
    $controller = Container::get($controller);
    if (method_exists($controller, $action)) {
        try {
            $response = call_user_func([$controller, $action], $data);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException($throwable->getMessage());
        }

        return $response;
    }
    return null;
}
