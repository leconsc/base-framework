<?php
/**
 *
 *
 * @author ChenBin
 * @version $Id:AdminIdentity.php, v1.0 2017-01-05 09:47 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\modules\admin\services;

use app\helpers\Cache;
use app\modules\admin\authorization\AuthItem;
use yii\helpers\Json;
use yii\web\IdentityInterface;
use app\modules\admin\models\Administrator;
use app\modules\admin\models\AdminGroup;

class AdminIdentity implements IdentityInterface
{
    const CACHE_IDENTITY = 'administrator_identity_%s';
    const CACHE_IDENTITY_GROUP = 'administrator_identity_group_%s';

    public $uid;
    public $username;
    public $truename;
    public $gid;
    public $groupname;
    public $permission;
    public $roleType;
    public $authKey;

    /**
     * 查询登入身份信息.
     *
     * @param int|string $id
     * @return AdminIdentity
     */
    public static function findIdentity($id)
    {
        $cacheKey = sprintf(self::CACHE_IDENTITY, $id);
        $identity = Cache::get($cacheKey);
        if ($identity === false) {
            $identity = null;
            $administrator = Administrator::findOne($id);
            if ($administrator) {
                $group = AdminGroup::findOne($administrator->gid);
                if ($group) {
                    $identity = new self();
                    $identity->uid = $administrator->uid;
                    $identity->username = $administrator->username;
                    $identity->truename = $administrator->truename;
                    $identity->gid = $administrator->gid;
                    $identity->groupname = $group->group_name;
                    $identity->permission = $group->permission === AuthItem::FULL_PERMISSIONS ? AuthItem::FULL_PERMISSIONS : Json::decode($group->permission);
                    $identity->roleType = $group->role_type;
                    $identity->authKey = $administrator->auth_key;
                    $groupCacheKey = sprintf(self::CACHE_IDENTITY_GROUP, $administrator->gid);
                    Cache::set($cacheKey, $identity, $groupCacheKey);
                }
            }
        }
        return $identity;
    }

    /**
     * 移除Cache
     *
     * @param integer|array $ids
     * @param bool $isGroupId
     */
    public static function removeCache($ids, $isGroupId = false)
    {
        $ids = (array)$ids;
        if ($isGroupId) {
            foreach ($ids as $id) {
                $groupCacheKey = sprintf(self::CACHE_IDENTITY_GROUP, $id);
                Cache::flush($groupCacheKey);
            }
        } else {
            foreach ($ids as $id) {
                $cacheKey = sprintf(self::CACHE_IDENTITY, $id);
                Cache::delete($cacheKey);
            }
        }
    }

    /**
     * 根据AccessToken查询登录身份信息.
     *
     * @param string $token
     * @param null|string $type
     * @return null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * 获取用户身份标识.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * 获取用户验证Key.
     *
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * 验证用户Key.
     *
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
}