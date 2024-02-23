<?php

namespace plugin\wechat\app\service;

use plugin\wechat\api\HasRedisList;
use plugin\wechat\app\model\WechatTemplateMessage;
use support\Db;

/**
 * 微信模板消息发送服务
 */
class WechatTemplateMessageServices
{
    use HasRedisList;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->key = 'WechatTemplateMessageServices:batchSave';
    }

    /**
     * 批量保存
     * @param string|null $table
     * @return void
     */
    public function batchSave(string $table = null): void
    {
        $table = $table ?: (new WechatTemplateMessage())->getTable();
        while ($this->length()) {
            $list = [];
            while ($data = $this->pop()) {
                $list[] = $data;
                if (50 <= count($list)) {
                    break;
                }
            }
            if ($list) {
                Db::table($table)->insert($list);
            }
        }
    }
}
