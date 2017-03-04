<?php
/**
 * 定义系统菜单项..
 *
 * @author ChenBin
 * @version $Id:Menu.php, 1.0 2014-08-28 13:11+100 ChenBin$
 * @package: app\modules\admin\components
 * @since 2014-08-28 13:11
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\modules\admin\components;

use app\base\Menu;
use app\modules\admin\authorization\AccessResource;

class MenuList extends Menu
{
    /**
     * 菜单项目设置
     */
    protected static function _setUp()
    {
        self::$_menus = array(
            array(
                self::TITLE => '管理首页',
                self::CONTROLLER => 'default'
            ),
            array(
                self::TITLE => '页面管理',
                self::CONTROLLER => AccessResource::R_PAGE
            ),
            array(
                self::TITLE => '文章管理',
                self::CONTROLLER => AccessResource::R_CONTENT
            ),
            array(
                self::TITLE => '分类管理',
                self::CONTROLLER => AccessResource::R_CATEGORY,
            ),
            array(
                self::TITLE => '用户管理',
                self::CONTROLLER => AccessResource::R_MEMBER,
            ),
            array(
                self::TITLE => '系统工具',
                self::SUBMENUS => array(
                    array(
                        self::TITLE => '管理员管理',
                        self::CONTROLLER => AccessResource::R_ADMINISTRATOR,
                    ),
                    array(
                        self::TITLE => '管理组管理',
                        self::CONTROLLER => AccessResource::R_ADMIN_GROUP,
                    ),
                    '-',
                    array(
                        self::TITLE => '系统配置',
                        self::CONTROLLER => AccessResource::R_CONFIG,
                    ),
                    '-',
                    array(
                        self::TITLE => '缓存管理',
                        self::CONTROLLER => AccessResource::R_CACHE
                    )
                ),
            )
        );
    }
}