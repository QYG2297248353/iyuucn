<?php

namespace Ledc\Template\Library;

/**
 * 初始化属性.
 */
trait HasInitialize
{
    /**
     * 初始化属性.
     */
    protected function initialize(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
