<?php

/**
 *  Menu菜单基础类
 *
 * @author ChenBin
 * @version $Id: MenuBase.php, 1.0 2016-09-16 23:15+100 ChenBin$
 * @package: app\base
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\base;

use app\helpers\AuthorizationHelper;
use app\helpers\UrlHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use Yii;

abstract class Menu
{
    const TITLE = 'title';
    const ICON = 'icon';
    const MODULE = 'module';
    const CONTROLLER = 'controller';
    const ACTION = 'action';
    const SUBMENUS = 'submenus';
    const SEPARATOR = '-';
    const PATH = 'path';
    const PARAMS = 'params';
    const TARGET = 'target';
    const DESCRIPTION = 'description';
    const LINK = 'link';
    /**
     * 处理结果的Menus.
     *
     * @static
     * @access private
     * @var array
     */
    protected static $_menus = array();
    /**
     * 初始化函数
     */
    private static function _init()
    {
        static::_setUp();
        if(!is_array(static::$_menus) || count(static::$_menus) === 0) {
            throw new \Exception('菜单项目未初始化!');
        }
    }

    /**
     * 菜单项目设置
     */
    protected static function _setUp(){

    }
    /**
     * 获取处理结果的菜单结构.
     *
     * @return bool|mixed
     */
    public static function getMenus()
    {
        $className = get_called_class();
        /** @var Menu $instance */
        $instance = new $className();
        return $instance->_getMenuItems();
    }

    /**
     * 根据载入的菜单结构构建菜单数组.
     *
     * @param $menus
     * @return array
     */
    protected function _buildStructure(array $menus)
    {
        $structure = array();
        $i = 0;
        foreach ($menus as $menu) {
            if (is_array($menu)) {
                if (empty($menu[self::TITLE])) {
                    continue;
                }
                $title = $menu[self::TITLE];
                $params = array();
                if (!empty($menu[self::PARAMS])) {
                    if (is_array($menu[self::PARAMS])) {
                        $params = $menu[self::PARAMS];
                    } else if (is_string($menu[self::PARAMS])) {
                        $paramPairs = explode('&', $menu[self::PARAMS]);
                        foreach ($paramPairs as $paramItem) {
                            $menuParts = preg_split('/\s*=\s*/', trim($paramItem));
                            $params[$menuParts[0]] = $menuParts[1];
                        }
                    }
                }
                ${self::ICON} = empty($menu[self::ICON]) ? '' : Html::img($menu[self::ICON], $title);
                if(isset($menu[self::LINK])) {
                    if(Url::isRelative($menu[self::LINK])){
                        ${self::LINK} = $this->_createUrl($menu[self::LINK], $params);
                    }else{
                        ${self::LINK} = UrlHelper::attachUrlParams($menu[self::LINK], $params);
                    }
                }else{
                    $module = $controller = $action = $path = null;
                    if (!empty($menu[self::MODULE])) {
                        $module = $menu[self::MODULE];
                    }
                    if (!empty($menu[self::CONTROLLER])) {
                        $controller = $menu[self::CONTROLLER];
                    }
                    if (!empty($menu[self::ACTION])) {
                        $action = $menu[self::ACTION];
                    }
                    if (!empty($menu[self::PATH])) {
                        $path = $menu[self::PATH];
                    }
                    if (!$module && !$controller && !$action) {
                        $link = null;
                        $target = null;
                    } else {
                        $routeItems = array();
                        if (empty($module) && isset(Yii::$app->controller->module)) {
                            $module = Yii::$app->controller->module->id;
                        }
                        if (!empty($module)) {
                            $routeItems[] = $module;
                        }
                        $routeItems[] = $controller;
                        if (!empty($action)) {
                            $routeItems[] = $action;
                        }
                        if (!empty($path)) {
                            $routeItems[] = $path;
                        }
                        if (!$this->_accessCheck($action, $controller, $module)) {
                            continue;
                        }
                        $route = '/' . ltrim(join('/', $routeItems), '/');
                        ${self::LINK} = $this->_createUrl($route, $params);
                    }
                }
                ${self::TARGET} = empty($menu[self::TARGET]) ? '_self' : $menu[self::TARGET];
                ${self::DESCRIPTION} = empty($menu[self::DESCRIPTION]) ? $title : $menu[self::DESCRIPTION];
                $structure[$i] = compact(self::ICON, self::TITLE, self::LINK, self::TARGET, self::DESCRIPTION);
                if (isset($menu[self::SUBMENUS]) && is_array($menu[self::SUBMENUS])) {
                    $subMenus = self::_buildStructure($menu[self::SUBMENUS]);
                    if (!empty($subMenus)) {
                        $structure[$i][self::SUBMENUS] = $subMenus;
                    }
                }
                $i++;
            } else if (is_string($menu) && $menu == self::SEPARATOR) {
                $structure[$i] = $menu;
                $i++;
            }
        }
        return $structure;
    }

    /**
     * 處理授權菜單顯示結構
     */
    /**
     * @param array $structure
     * @return array
     */
    protected function _processMenus(array $structure)
    {
        $menus = array();
        if (count($structure) > 0) {
            $prevItem = null;
            $item = null;
            while (true) {
                $item = array_pop($structure);
                if (is_null($item)) {
                    break;
                }
                if ((is_null($prevItem) || (is_string($prevItem) && $prevItem === self::SEPARATOR))
                    && is_string($item) && $item === self::SEPARATOR
                ) {
                    continue;
                }

                if (is_string($item) && $item === self::SEPARATOR || (!empty($item[self::LINK]) && empty($item[self::SUBMENUS]))) {
                    array_unshift($menus, $item);
                    $prevItem = $item;
                } else {
                    if (isset($item[self::SUBMENUS]) && is_array($item[self::SUBMENUS])) {
                        $subMenus = $this->_processMenus($item[self::SUBMENUS]);
                        if (!empty($subMenus)) {
                            $item[self::SUBMENUS] = $subMenus;
                            array_unshift($menus, $item);
                            $prevItem = $item;
                        }
                    }
                }
            }
            if(isset($menus[0]) && is_string($menus[0]) && $menus[0] === self::SEPARATOR){
                array_shift($menus);
            }
        }
        return $menus;
    }

    /**
     * 创建链接URL.
     *
     * @param string $route
     * @param array $params
     * @return mixed
     */
    protected function _createUrl($route, array $params = [])
    {
        return Url::toRoute([$route, $params]);
    }

    /**
     * 访问权限检查.
     *
     * @param string $action
     * @param null|string $controller
     * @param null|string $module
     * @return boolean
     */
    protected function _accessCheck($action, $controller = null, $module = null)
    {
        if (Yii::$app->controller && method_exists(Yii::$app->controller, 'accessCheck')) {
            return Yii::$app->controller->accessCheck($action, $controller, $module);
        }
        return true;
    }

    /**
     * 获取菜单项
     *
     * @return array
     */
    protected function _getMenuItems()
    {
        self::_init();
        $structure = $this->_buildStructure(self::$_menus);
        return $this->_processMenus($structure);
    }
}