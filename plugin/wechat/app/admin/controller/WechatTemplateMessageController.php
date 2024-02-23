<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\controller\Crud;
use plugin\wechat\app\model\WechatTemplateMessage;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 模板消息
 */
class WechatTemplateMessageController extends Crud
{
    /**
     * @var WechatTemplateMessage
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new WechatTemplateMessage();
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('wechat-template-message/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('wechat-template-message/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('wechat-template-message/update');
    }
}
