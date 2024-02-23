<?php

namespace plugin\wechat\app\model;

/**
 * 模型观察者：wechat_user
 * @usage WechatUser::observe(WechatUserObserver::class);
 */
class WechatUserObserver
{
    /**
     * 监听数据即将创建的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function creating(WechatUser $model): void
    {
    }

    /**
     * 监听数据创建后的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function created(WechatUser $model): void
    {
    }

    /**
     * 监听数据即将更新的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function updating(WechatUser $model): void
    {
    }

    /**
     * 监听数据更新后的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function updated(WechatUser $model): void
    {
    }

    /**
     * 监听数据即将保存的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function saving(WechatUser $model): void
    {
    }

    /**
     * 监听数据保存后的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function saved(WechatUser $model): void
    {
    }

    /**
     * 监听数据即将删除的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function deleting(WechatUser $model): void
    {
    }

    /**
     * 监听数据删除后的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function deleted(WechatUser $model): void
    {
    }

    /**
     * 监听数据即将从软删除状态恢复的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function restoring(WechatUser $model): void
    {
    }

    /**
     * 监听数据从软删除状态恢复后的事件。
     *
     * @param WechatUser $model
     * @return void
     */
    public function restored(WechatUser $model): void
    {
    }
}
