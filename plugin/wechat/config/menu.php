<?php

return [
    [
        'title' => '微信管理',
        'key' => 'wechat',
        'icon' => 'layui-icon-login-wechat',
        'weight' => 300,
        'type' => 0,
        'children' => [
            [
                'title' => '参数配置',
                'key' => 'wechat-config-index',
                'href' => '/app/wechat/admin/config',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '菜单管理',
                'key' => 'wechat-menu-index',
                'href' => '/app/wechat/admin/menu',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '回复规则',
                'key' => 'wechat-reply-index',
                'href' => '/app/wechat/admin/reply',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '微信用户',
                'key' => plugin\wechat\app\admin\controller\WechatUserController::class,
                'icon' => 'layui-icon-username',
                'href' => '/app/wechat/admin/wechat-user/index',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '模板消息',
                'key' => plugin\wechat\app\admin\controller\WechatTemplateMessageController::class,
                'icon' => 'layui-icon-notice',
                'href' => '/app/wechat/admin/wechat-template-message/index',
                'type' => 1,
                'weight' => 0,
            ],
        ]
    ],
];
