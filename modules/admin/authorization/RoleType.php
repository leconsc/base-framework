<?php
/**
 * 角色类型定义
 *
 * @author ChenBin
 * @version $Id:RoleType.php, v1.0 2017-01-04 13:45+100 ChenBin $
 * @category app\modules\admin\authorization
 * @since 2017-01-04 13:45
 * @copyright 2017(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\authorization;

class RoleType {
    const T_ADMIN = 1; //系统管理员
    const T_MANAGER = 2; //普通管理员

    private static $_roleTypes = array(
        self::T_ADMIN => '系统管理员',
        self::T_MANAGER => '管理员'
    );

    /**
     * 获取所有角色类型信息
     *
     * @return array
     */
    public static function getRoleTypes()
    {
        return self::$_roleTypes;
    }
    /**
     * 获取角色类型信息.
     *
     * @param $roleType string
     * @return boolean | string
     */
    public static function getRoleTypeTitle($roleType)
    {
        if (isset(self::$_roleTypes[$roleType])) {
            return self::$_roleTypes[$roleType];
        }
        return false;
    }
}