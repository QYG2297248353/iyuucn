<?php

namespace plugin\wechat\api;

use plugin\admin\api\Menu;
use support\exception\BusinessException;

/**
 * 安装脚本
 */
class Install
{
    /**
     * 安装
     *
     * @param $version
     * @return void
     */
    public static function install($version): void
    {
        // 导入菜单
        if ($menus = static::getMenus()) {
            Menu::import($menus);
        }
    }

    /**
     * 卸载
     *
     * @param $version
     * @return void
     * @throws BusinessException
     */
    public static function uninstall($version): void
    {
        // 2023年9月24日
        throw new BusinessException('当前插件禁止卸载');
        // 删除菜单
        //foreach (static::getMenus() as $menu) {
        //    Menu::delete($menu['key']);
        //}
    }

    /**
     * 更新
     *
     * @param $from_version
     * @param $to_version
     * @param $context
     * @return void
     */
    public static function update($from_version, $to_version, $context = null): void
    {
        // 删除不用的菜单
        if (isset($context['previous_menus'])) {
            static::removeUnnecessaryMenus($context['previous_menus']);
        }
        // 删除所有菜单v1.0.4->v1.0.5需要删除旧菜单重新导入
        foreach (static::getMenus() as $menu) {
            Menu::delete($menu['key']);
        }
        // 导入新菜单
        if ($menus = static::getMenus()) {
            Menu::import($menus);
        }
    }

    /**
     * 更新前数据收集等
     *
     * @param $from_version
     * @param $to_version
     * @return array|array[]
     */
    public static function beforeUpdate($from_version, $to_version): array
    {
        // 在更新之前获得老菜单，通过context传递给 update
        return ['previous_menus' => static::getMenus()];
    }

    /**
     * 获取菜单
     *
     * @return array|mixed
     */
    public static function getMenus(): mixed
    {
        clearstatcache();
        if (is_file($menu_file = dirname(__DIR__) . '/config/menu.php')) {
            $menus = include $menu_file;
            return $menus ?: [];
        }
        return [];
    }

    /**
     * 删除不需要的菜单
     *
     * @param $previous_menus
     * @return void
     */
    public static function removeUnnecessaryMenus($previous_menus): void
    {
        $menus_to_remove = array_diff(Menu::column($previous_menus, 'name'), Menu::column(static::getMenus(), 'name'));
        foreach ($menus_to_remove as $name) {
            Menu::delete($name);
        }
    }
}
