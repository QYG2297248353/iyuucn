<?php

namespace plugin\wechat\app\model;

/**
 * 模型观察者：wechat_template_message
 * @usage WechatTemplateMessage::observe(WechatTemplateMessageObserver::class);
 */
class WechatTemplateMessageObserver
{
    /**
     * 监听数据即将创建的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function creating(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据创建后的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function created(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据即将更新的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function updating(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据更新后的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function updated(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据即将保存的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function saving(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据保存后的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function saved(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据即将删除的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function deleting(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据删除后的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function deleted(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据即将从软删除状态恢复的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function restoring(WechatTemplateMessage $model): void
    {
    }

    /**
     * 监听数据从软删除状态恢复后的事件。
     *
     * @param WechatTemplateMessage $model
     * @return void
     */
    public function restored(WechatTemplateMessage $model): void
    {
    }
}
