<?php

namespace Ledc\Template;

use support\Redis;

/**
 * Redis的setNx指令，支持同时设置ttl
 * - 使用lua脚本实现
 * @param string $key 缓存的key
 * @param string $value 缓存的value
 * @param int $ttl 存活的ttl，单位秒
 * @return bool
 */
function redis_set_nx(string $key, string $value, int $ttl = 10): bool
{
    static $scriptSha = null;
    if (!$scriptSha) {
        $script = <<<luascript
local result = redis.call('SETNX', KEYS[1], ARGV[1]);
if result == 1 then
    return redis.call('expire', KEYS[1], ARGV[2])
else
    return 0
end
luascript;
        $scriptSha = Redis::script('load', $script);
    }
    return (bool)Redis::rawCommand('evalsha', $scriptSha, 1, $key, $value, $ttl);
}

/**
 * Redis的incr指令，支持设置ttl
 * - 使用lua脚本实现
 * @param string $key 缓存的key
 * @param int $ttl 存活的ttl，单位秒
 * @return int
 */
function redis_incr(string $key, int $ttl = 10): int
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

/**
 * 简单的POST请求
 * - 用curl实现.
 *
 * @param string $url 请求地址
 * @param array|object $data 数据包
 * @param bool $isJsonRequest 是否Json请求
 * @param null|int $responseCode 最后的响应代码
 * @param int $curlErrorCode 返回错误代码或在没有错误发生时返回 0 (零)
 * @param string $curlErrorMessage 返回错误信息，或者如果没有任何错误发生就返回 '' (空字符串)
 */
function http_post_request_curl(string $url, array|object $data = [], bool $isJsonRequest = true, int &$responseCode = null, int &$curlErrorCode = 0, string &$curlErrorMessage = ''): bool|string
{
    if ($isJsonRequest) {
        $header = ['Content-Type: application/json; charset=UTF-8'];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        $header = ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8'];
        $data = http_build_query($data);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    if (0 === stripos($url, 'https://')) {
        // false 禁止 cURL 验证对等证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 0 时不检查名称（SSL 对等证书中的公用名称字段或主题备用名称（Subject Alternate Name，简称 SNA）字段是否与提供的主机名匹配）
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // 自动跳转，跟随请求Location
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);         // 递归次数
    // 代理配置
    if (getenv('CURLOPT_PROXY')) {
        curl_setopt($ch, CURLOPT_PROXY, getenv('CURLOPT_PROXY'));
    }
    $response = curl_exec($ch);
    $responseCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErrorCode = curl_errno($ch);
    $curlErrorMessage = curl_error($ch);
    curl_close($ch);

    return $response;
}

/**
 * 简单的POST请求
 * - 用file_get_contents实现.
 *
 * @param string $url 请求地址
 * @param array|object $data 数据包
 * @param bool $isJsonRequest 是否Json请求
 *
 * @return false|string
 */
function http_post_request(string $url, array|object $data, bool $isJsonRequest = false): bool|string
{
    if ($isJsonRequest) {
        $type = 'Content-Type: application/json; charset=UTF-8';
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        $type = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $data = http_build_query($data);
    }
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => $type . "\r\n" . 'Content-Length: ' . strlen($data) . "\r\n",
            'content' => $data,
            'timeout' => 5,
        ],
        // 解决SSL证书验证失败的问题
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];
    $context = stream_context_create($opts);

    return file_get_contents($url, false, $context);
}

/**
 * 获取当前版本commit.
 */
function current_git_commit(string $branch = 'master', bool $short = true): string
{
    if ($hash = file_get_contents(sprintf(base_path() . '/.git/refs/heads/%s', $branch))) {
        $hash = trim($hash);

        return $short ? substr($hash, 0, 7) : $hash;
    }

    return '';
}

/**
 * 获取当前版本时间.
 */
function current_git_filemtime(string $branch = 'master', string $format = 'Y-m-d H:i:s'): string
{
    if ($time = filemtime(sprintf(base_path() . '/.git/refs/heads/%s', $branch))) {
        return date($format, $time);
    }

    return '';
}
