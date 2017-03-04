<?php

/**
 * 授权项目定义.
 *
 * @author ChenBin
 * @version $Id:AuthItem.php, 1.0 2017-01-04 13:45+100 ChenBin$
 * @package: WeGames
 * @since 2017-01-04 13:45
 * @copyright 2014(C)Copyright By WeGames, All rights Reserved.
 */
namespace app\modules\admin\authorization;

class AuthItem
{
    const FULL_PERMISSIONS = 'full_permissions';
    /**
     * @var array 授权项目定义
     */
    private static $_authItems = array(
        RoleType::T_ADMIN => array(
            AccessResource::R_PAGE => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_CONTENT => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_CATEGORY => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_MEMBER => array(
                Operation::O_VIEW,
                Operation::O_EDIT,
                Operation::O_FREEZE,
                Operation::O_REMOVE
            ),
            AccessResource::R_ADMINISTRATOR => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW,
                Operation::O_AUTHORIZE,
                Operation::O_FREEZE
            ),
            AccessResource::R_ADMIN_GROUP => array(
                Operation::O_VIEW,
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_AUTHORIZE,
                Operation::O_FREEZE
            ),
            AccessResource::R_CACHE => array(
                Operation::O_VIEW
            ),
            AccessResource::R_CONFIG => array(
                Operation::O_VIEW,
                Operation::O_EDIT
            )
        ),
        RoleType::T_MANAGER => array(
            AccessResource::R_PAGE => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_CONTENT => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_CATEGORY => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW
            ),
            AccessResource::R_MEMBER => array(
                Operation::O_VIEW,
                Operation::O_EDIT,
                Operation::O_FREEZE,
                Operation::O_REMOVE
            ),
            AccessResource::R_ADMINISTRATOR => array(
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_VIEW,
                Operation::O_AUTHORIZE,
                Operation::O_FREEZE
            ),
            AccessResource::R_ADMIN_GROUP => array(
                Operation::O_VIEW,
                Operation::O_CREATE,
                Operation::O_EDIT,
                Operation::O_REMOVE,
                Operation::O_AUTHORIZE,
                Operation::O_FREEZE
            ),
            AccessResource::R_CONFIG => array(
                Operation::O_VIEW,
                Operation::O_EDIT
            )
        )
    );

    /**
     * 根据角色类型返回授权项目或全部授权项目定义.
     *
     * @param null|string $roleType
     * @return array|bool
     */
    public static function getAuthItems($roleType = null)
    {
        if (is_null($roleType)) {
            $result = array();
            foreach(self::$_authItems as $roleType => $authItems){
                $result[$roleType] = self::_preProcessAuthItems($authItems);
            }
            return $result;
        } else if (isset(self::$_authItems[$roleType])) {
            return self::_preProcessAuthItems(self::$_authItems[$roleType]);
        }
        return false;
    }

    /**
     * 预处理授权项.
     *
     * @param $authItems
     * @return array
     */
    private static function _preProcessAuthItems($authItems){
        $result = array();
        foreach($authItems as $resource => $actions){
            $result[$resource] = array_fill_keys($actions, 1);
        }
        return $result;
    }
}