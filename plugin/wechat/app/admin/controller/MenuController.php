<?php

namespace plugin\wechat\app\admin\controller;

use plugin\wechat\api\HasResponse;
use plugin\wechat\app\service\WechatService;
use support\Request;
use support\Response;
use Throwable;

/**
 * 菜单
 */
class MenuController
{
    use HasResponse;

    /**
     * 菜单首页
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function index(Request $request): Response
    {
        return raw_view('menu/index');
    }

    /**
     * 加载配置
     * - 从微信接口实时拉取
     * @param Request $request
     * @return Response
     */
    public function load(Request $request): Response
    {
        try {
            $api = WechatService::instance()->getClient();
            $response = $api->get('/cgi-bin/get_current_selfmenu_info');
            $res = json_decode($response->getContent(false), true);
            if (isset($res['errmsg'])) {
                return $this->fail('微信接口返回错误:' . $res['errmsg']);
            } elseif ($res['is_menu_open'] === 0) {
                return $this->fail('菜单未开启或加载失败,如果是新创建的公众号,请先直接添加菜单即可');
            } else {
                $menu = $res['selfmenu_info']['button'];
                foreach ($menu as $k => $v) {
                    if (isset($v['sub_button'])) {
                        $menu[$k]['sub_button'] = $v['sub_button']['list'];
                    }
                }
                return $this->success($menu);
            }
        } catch (Throwable $throwable) {
            return $this->fail($throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * 发布菜单
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $menu = $request->post('menu');
        if ($menu === null) {
            return $this->fail('菜单数据不能为空');
        }
        try {
            $response = WechatService::menuCreate($menu);
            $res = json_decode($response->getContent(false), true);
            if (0 === $res['errcode']) {
                return $this->success([], '发布成功');
            } else {
                return $this->fail('微信接口返回错误:' . $res['errmsg']);
            }
        } catch (Throwable $throwable) {
            return $this->fail($throwable->getMessage(), $throwable->getCode());
        }
    }
}
