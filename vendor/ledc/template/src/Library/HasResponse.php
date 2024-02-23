<?php

namespace Ledc\Template\Library;

use support\Response;

/**
 * 控制器公共方法
 * - 统一响应格式，生成响应对象
 */
trait HasResponse
{
    /**
     * 成功响应.
     *
     * @param string $msg 消息
     * @param array $data 数据
     */
    final protected function success(string $msg = 'ok', array $data = []): Response
    {
        return $this->json($this->getSuccessCode(), $msg, $data);
    }

    /**
     * 响应.
     *
     * @param int $code 响应码
     * @param string $msg 消息
     * @param array $data 数据
     */
    final protected function json(int $code, string $msg = 'ok', array $data = []): Response
    {
        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
    }

    /**
     * 获取成功时候的响应码
     */
    protected function getSuccessCode(): int
    {
        return 0;
    }

    /**
     * 失败响应.
     *
     * @param string $msg 消息
     * @param int $code 响应码
     */
    final protected function fail(string $msg = 'fail', int $code = 1, array $data = []): Response
    {
        return $this->json($this->getSuccessCode() === $code ? 1 : $code, $msg, $data);
    }

    /**
     * 格式化枚举的名值数组
     * @param array $items
     * @return array
     */
    final protected function formatEnum(array $items): array
    {
        $formatted = [];
        foreach ($items as $name => $value) {
            $formatted[] = [
                'name' => $name,
                'value' => $value
            ];
        }
        return $formatted;
    }
}
