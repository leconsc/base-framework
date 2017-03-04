<?php
/**
 * 系统资源定义
 *
 * @author ChenBin
 * @version $Id:AccessResource.php, 1.0 2017-01-04 13:45+100 ChenBin$
 * @package: app\modules\admin\authorization
 * @since 2017-01-04 13:45
 * @copyright 2017(C)Copyright By SmallWorld, All rights Reserved.
 */
namespace app\modules\admin\authorization;

class AccessResource
{
    const R_PAGE = 'page';
    const R_CONTENT = 'content';
    const R_CATEGORY = 'category';
    const R_MEMBER= 'member';
    const R_ADMINISTRATOR = 'administrator';
    const R_ADMIN_GROUP = 'admingroup';
    const R_CONFIG = 'config';
    const R_CACHE = 'cache';


    /** @var array 各类资源描述 */
    private static $_resources = array(
        AccessResourceGroup::G_CONTENT => array(
            self::R_PAGE => '页面管理',
            self::R_CONTENT => '文章管理',
            self::R_CATEGORY => '文章分类管理'
        ),
        AccessResourceGroup::G_MEMBER => array(
            self::R_MEMBER => '用户管理',
        ),
        AccessResourceGroup::G_SYSTEM => array(
            self::R_ADMIN_GROUP => '管理员组管理',
            self::R_ADMINISTRATOR => '管理员管理',
            self::R_CONFIG => '系统配置',
            self::R_CACHE => '缓存管理'
        )
    );
    /**
     * 根据分组获取系统资源或全部资源定义.
     *
     * @param null $group
     * @return array|bool
     */
    public static function getResources($group = null)
    {
        if(is_null($group)){
            return self::$_resources;
        }else if(isset(self::$_resources[$group])){
            return self::$_resources[$group];
        }else{
            return false;
        }
    }

    /**
     * 获取资源名称定义
     *
     * @param $group
     * @param $resourceName
     * @return bool
     */
    public static function getResourceTitle($group, $resourceName){
        if(self::$_resources[$group][$resourceName]){
            return self::$_resources[$group][$resourceName];
        }
        return false;
    }
}