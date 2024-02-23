<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\model\Option;
use plugin\wechat\api\HasResponse;
use support\Request;
use support\Response;
use Throwable;

/**
 * 配置
 */
class ConfigController
{
    use HasResponse;

    /**
     * 配置模板
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function index(Request $request): Response
    {
        return raw_view('config/index');
    }

    /**
     * 获取配置
     * @param Request $request
     * @return Response
     */
    public function get(Request $request): Response
    {
        $config = Option::where('name', 'wechat_config')->value('value');
        if (empty($config)) {
            $config = json_encode(['token' => get_rand_token_string(), 'use_stable_access_token' => true]);
            $option = new Option();
            $option->name = 'wechat_config';
            $option->value = $config;
            $option->save();
        }
        return $this->success(json_decode($config, true));
    }

    /**
     * 保存配置
     * @param Request $request
     * @return Response
     */
    public function save(Request $request): Response
    {
        $config = $request->post();
        // 获取稳定版接口调用凭据 david
        $config['use_stable_access_token'] = isset($config['use_stable_access_token']);
        Option::where('name', 'wechat_config')->update(['value' => json_encode($config)]);
        return $this->success([], '保存成功');
    }
}
