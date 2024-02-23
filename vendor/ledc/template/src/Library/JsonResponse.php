<?php

namespace Ledc\Template\Library;

use support\Response;
use think\Container;

/**
 * JSON响应.
 */
class JsonResponse
{
    /**
     * 成功响应码
     */
    public const success = 0;

    /**
     * 失败响应码
     */
    public const fail = 1;

    /**
     * 单例
     * - 使用ThinkPHP容器，实现单例.
     */
    public static function getInstance(bool $newInstance = false): static
    {
        return Container::pull(static::class, [], $newInstance);
    }

    public function success(string $msg = 'ok', array $data = []): Response
    {
        return $this->json(static::success, $msg, $data);
    }

    /**
     * 返回格式化json数据.
     */
    public function json(int $code, string $msg = 'ok', array $data = []): Response
    {
        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
    }

    public function fail(string $msg = 'fail', int $code = self::fail, array $data = []): Response
    {
        return $this->json(static::success === $code ? static::fail : $code, $msg, $data);
    }
}
