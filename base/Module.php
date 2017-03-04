<?php

/**
 * 模块的基础抽像类
 *
 * @author ChenBin
 * @version $Id:BaseModule.php, v1.0 2014-09-27 07:45 ChenBin $
 * @category app\base
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, all rights reserved.
 */
abstract class Module extends \yii\base\Module
{
    public function init()
    {
        $name = $this->getName();
        $this->setImport(array(
            $name . '.models.*',
            $name . '.components.*',
            $name . '.helpers.*',
            'application.components.*',
            'application.helper.*'
        ));
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action)) {
            return true;
        } else{
            return false;
        }
    }
} 