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

namespace app\services;

use app\helpers\Cache;
use app\models\Member;
use yii\web\IdentityInterface;

class MemberIdentity implements IdentityInterface
{
    const CACHE_IDENTITY = 'member_identity_%s';

    public $uid;
    public $email;
    public $name;
    public $mobile;
    public $authKey;

    /**
     * 查询登入身份信息.
     *
     * @param int|string $id
     * @return MemberIdentity
     */
    public static function findIdentity($id)
    {
        $cacheKey = sprintf(self::CACHE_IDENTITY, $id);
        $identity = Cache::get($cacheKey);
        if ($identity === false) {
            $identity = null;
            $member = Member::findOne($id);
            if ($member) {
                $identity = new self();
                $identity->uid = $member->uid;
                $identity->email = $member->email;
                $identity->name = $member->name;
                $identity->mobile = $member->mobile;
                $identity->authKey = $member->auth_key;
                Cache::set($cacheKey, $identity);
            }
        }
        return $identity;
    }

    /**
     * 移除Cache
     *
     * @param integer|array $ids
     */
    public static function removeCache($ids)
    {
        $ids = (array)$ids;
        foreach ($ids as $id) {
            $cacheKey = sprintf(self::CACHE_IDENTITY, $id);
            Cache::delete($cacheKey);
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