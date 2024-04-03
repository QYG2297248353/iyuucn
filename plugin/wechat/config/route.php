<?php

use plugin\wechat\app\controller\TemplateMessageController;
use Webman\Route;

// 发送模板消息
Route::add(['GET', 'POST'], '/{token:IYUU\d+T[a-f0-9]{40}}.send', [TemplateMessageController::class, 'send'])
    ->middleware([Tinywan\LimitTraffic\Middleware\LimitTrafficMiddleware::class]);

// 读取模板消息
Route::get('/{hash:IYUU\d+T[A-Za-z0-9]{40}}.read', [TemplateMessageController::class, 'read']);
