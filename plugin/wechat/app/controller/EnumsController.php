<?php

namespace plugin\wechat\app\controller;

use plugin\wechat\api\HasResponse;
use plugin\wechat\app\enums\WechatTemplateMessageStatusEnum;
use plugin\wechat\app\enums\WechatUserTypeEnum;
use support\Request;
use support\Response;

/**
 * 枚举控制器
 */
class EnumsController
{
    use HasResponse;

    /**
     * 用户类型
     * @param Request $request
     * @return Response
     */
    public function userType(Request $request): Response
    {
        return $this->success($this->formatEnum(WechatUserTypeEnum::select()));
    }

    /**
     * 模板消息状态
     * @param Request $request
     * @return Response
     */
    public function templateMessageStatus(Request $request): Response
    {
        return $this->success($this->formatEnum(WechatTemplateMessageStatusEnum::select()));
    }
}
