<?php

namespace Ledc\Template\Library;

use support\Model;

/**
 * 基础控制器抽象类.
 */
abstract class BaseController
{
    use HasResponse;
    use HasThinkValidate;

    /**
     * 数据模型.
     */
    protected ?Model $model = null;

    /**
     * 无需登录无需鉴权的方法.
     */
    protected array $noNeedLogin = [];

    /**
     * 需要登录无需鉴权的方法.
     */
    protected array $noNeedAuth = [];

    /**
     * 数据模型.
     */
    final protected function getModel(): ?Model
    {
        return $this->model;
    }
}
