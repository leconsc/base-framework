<?php

/**
 * 资源组的定义, 不同区域可能有相同的组名
 *
 * @author ChenBin
 * @version $Id:AccessResourceGroup.php, v1.0 2017-01-04 13:45 ChenBin $
 * @category app\modules\admin\authorization
 * @since 2017-01-04 13:45
 * @copyright 2017(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\authorization;

class AccessResourceGroup
{
    const G_CONTENT = 'content';
    const G_MEMBER = 'member';
    const G_SYSTEM = 'system';
    const G_OTHER = 'other';

    /** @var array 组描述 */
    private static $_groupDescription = array(
        self::G_CONTENT => '文章管理',
        self::G_MEMBER => '用户管理',
        self::G_SYSTEM => '系统',
        self::G_OTHER => '其它'
    );

    /**
     * 获取所有分组定义.
     *
     * @return array|boolean
     */
    public static function getGroupDescription($group = null)
    {
        if (is_null($group)) {
            return self::$_groupDescription;
        } else if (isset(self::$_groupDescription[$group])) {
            return self::$_groupDescription[$group];
        }else{
            return false;
        }
    }
}