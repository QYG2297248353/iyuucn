<?php

namespace Ledc\Template\Library;

use think\exception\ValidateException;
use think\Validate;

/**
 * ThinkPHP验证器.
 */
trait HasThinkValidate
{
    /**
     * 验证器助手函数.
     *
     * @param array|string $validate 验证器类名或者验证规则数组
     * @param array $message 错误提示信息
     * @param bool $batch 是否批量验证
     * @param bool $failException 是否抛出异常
     *
     * @throws ValidateException
     *
     * @author David 2022年4月3日
     */
    final protected function validate(array|string $validate = '', array $message = [], bool $batch = false, bool $failException = true): Validate
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (!class_exists($validate)) {
                throw new ValidateException('验证类不存在:' . $validate);
            }
            if (!is_a($validate, Validate::class, true)) {
                throw new ValidateException($validate . '验证类未继承:' . Validate::class);
            }

            /** @var Validate $v */
            $v = new $validate();
        }

        return $v->message($message)->batch($batch)->failException($failException);
    }
}
