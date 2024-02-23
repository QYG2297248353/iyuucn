<?php

namespace support;

use Ledc\Macroable\Macro;
use support\exception\BusinessException;

/**
 * 请求类
 * @link https://www.workerman.net/doc/webman/request.html
 */
class Request extends \Webman\Http\Request
{
    use Macro;

    /**
     * 设置session_id
     * @param string $session_id
     * @return $this
     */
    public function setSid(string $session_id): self
    {
        $this->sid = $session_id;
        return $this;
    }

    /**
     * 仅允许GET请求
     * @return self
     * @throws BusinessException
     */
    public function isGet(): self
    {
        if ($this->method() !== 'GET') {
            throw new BusinessException('仅允许GET请求');
        }
        return $this;
    }

    /**
     * 仅允许POST请求
     * @return $this
     * @throws BusinessException
     */
    public function isPost(): self
    {
        if ($this->method() !== 'POST') {
            throw new BusinessException('仅允许POST请求');
        }
        return $this;
    }

    /**
     * 获取更多参数
     * @param array $params 入参：示例一 ['a', 'b', 'c'] 示例二 [['a', ''], ['b', false]]
     * @param bool $index_array 是否返回索引数组
     * @return array
     * @author david 2023/2/17
     */
    public function allMore(array $params, bool $index_array = false): array
    {
        return $this->doMore($this->all(), $params, $index_array);
    }

    /**
     * 获取更多参数
     * @param array $params 入参：示例一 ['a', 'b', 'c'] 示例二 [['a', ''], ['b', false]]
     * @param bool $index_array 是否返回索引数组
     * @return array
     * @author david 2023/2/17
     */
    public function getMore(array $params, bool $index_array = false): array
    {
        return $this->doMore($this->get(), $params, $index_array);
    }

    /**
     * 获取更多参数
     * @param array $params 入参：示例一 ['a', 'b', 'c'] 示例二 [['a', ''], ['b', false]]
     * @param bool $index_array 是否返回索引数组
     * @return array
     * @author david 2023/2/17
     */
    public function postMore(array $params, bool $index_array = false): array
    {
        return $this->doMore($this->post(), $params, $index_array);
    }

    /**
     * @param array $source 数据源
     * @param array $params 入参：示例一 ['a', 'b', 'c'] 示例二 [['a', ''], ['b', false]]
     * @param bool $index_array 是否返回索引数组
     * @return array
     */
    protected function doMore(array $source, array $params, bool $index_array): array
    {
        $result = [];
        foreach ($params as $i => $key) {
            $type = null;
            if (is_array($key)) {
                $name = $key[0];
                $default = $key[1] ?? null;
            } else {
                $name = $key;
                $default = null;
            }

            //解析name
            if (strpos($name, '/')) {
                [$name, $type] = explode('/', $name);
            }

            $data = $source[$name] ?? $default;
            if (isset($type) && $data !== $default) {
                // 强制类型转换
                $this->typeCast($data, $type);
            }
            $result[$index_array ? $i : $name] = $data;
        }

        return $result;
    }

    /**
     * 强制类型转换
     * @access public
     * @param mixed $data
     * @param string $type
     */
    protected function typeCast(mixed &$data, string $type): void
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array)$data;
                break;
            // 数字
            case 'd':
                $data = (int)$data;
                break;
            // 浮点
            case 'f':
                $data = (float)$data;
                break;
            // 布尔
            case 'b':
                $data = (boolean)$data;
                break;
            // 字符串
            case 's':
                if (is_scalar($data)) {
                    $data = (string)$data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
                break;
        }
    }
}