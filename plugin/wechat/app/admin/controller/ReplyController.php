<?php

namespace plugin\wechat\app\admin\controller;

use plugin\admin\app\model\Option;
use plugin\wechat\api\HasResponse;
use support\Request;
use support\Response;

/**
 * 回复规则
 */
class ReplyController
{
    use HasResponse;

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return view('reply/index');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function get(Request $request): Response
    {
        $reply = Option::where('name', 'wechat_reply')->value('value');
        if (empty($reply)) {
            $reply = json_encode(['mode' => 'simple', 'hook' => '', 'follow' => '感谢关注我们', 'default' => '请点击菜单', 'rules' => []], JSON_UNESCAPED_UNICODE);
            $option = new Option();
            $option->name = 'wechat_reply';
            $option->value = $reply;
            $option->save();
        }
        return $this->success(json_decode($reply, true));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function save(Request $request): Response
    {
        $data = $request->post();
        $reply['mode'] = $data['mode'];
        $reply['hook'] = $data['hook'];
        $reply['follow'] = $data['follow'];
        $reply['default'] = $data['default'];
        for ($i = 0; $i < count($data['keyword']); $i++) {
            $reply['rules'][$data['keyword'][$i]] = $data['reply'][$i];
        }
        Option::where('name', 'wechat_reply')->update(['value' => json_encode($reply, JSON_UNESCAPED_UNICODE)]);
        return $this->success();
    }
}
