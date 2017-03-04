<?php

/**
 * 授权检查
 *
 * @author ChenBin
 * @version $Id:AuthorizationHelper.php, v1.0 2017-01-04 17:06 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */
namespace app\helpers;

use Yii;
class AuthorizationHelper
{
    /**
     * 授权检查.
     *
     * @param null|string $action
     * @param null|string $resource
     * @return bool
     */
    public static function check($action, $resource = null)
    {
        if (Yii::$app->controller && method_exists(Yii::$app->controller, 'accessCheck')) {
            return Yii::$app->controller->accessCheck($action, $resource);
        }
        return true;
    }
}