<?php

namespace Ledc\Pipeline;

use Psr\Container\ContainerInterface;

/**
 * 容器契约
 */
interface Container extends ContainerInterface
{
    /**
     * 创建类的实例
     * @param string $abstract 类名或者标识
     * @param array $constructor 构造函数的参数
     * @return mixed
     */
    public function make(string $abstract, array $constructor = []);
}
