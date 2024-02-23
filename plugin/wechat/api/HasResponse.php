<?php

namespace plugin\wechat\api;

use support\Response;

/**
 * 控制器公共方法
 * - 统一响应格式，生成响应对象
 */
trait HasResponse
{
    /**
     * 获取成功时候的响应码
     * @return int
     */
    protected function getSuccessCode(): int
    {
        return 0;
    }

    /**
     * 失败响应
     * @param string $msg 消息
     * @param int $code 响应码
     * @param array $data
     * @return Response
     */
    final protected function fail(string $msg = 'fail', int $code = 400, array $data = []): Response
    {
        return $this->json($code ?: 400, $msg, $data);
    }

    /**
     * 成功响应
     * @param array $data 数据
     * @param string $msg 消息
     * @return Response
     */
    final protected function success(array $data = [], string $msg = 'ok'): Response
    {
        return $this->json($this->getSuccessCode(), $msg, $data);
    }

    /**
     * 响应
     * @param int $code 响应码
     * @param string $msg 消息
     * @param array $data 数据
     * @return Response
     */
    final protected function json(int $code, string $msg = 'ok', array $data = []): Response
    {
        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
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
