<?php

/**
 * 系统常用一些基本方法封装.
 *
 * @author ChenBin
 * @version $Id:ControllerHelper.php, 1.0 2014-08-28 12:02+100 ChenBin$
 * @package: app\helpers
 * @since 2014-08-28 12:02
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use Yii;
class SystemHelper
{
    /**
     * 获取文件实际保存目录
     *
     * @return string 返回保存目录
     */
    public static function getFileUploadDirectory($uploadDir, $subDir = null, $dirCreateMode = 30)
    {
        $baseName = date("Ym");
        if (is_null($subDir) || !is_string($subDir)) {
            $subDir = "";
        }
        switch ($dirCreateMode) {
            case 1:
                $subDir .= DIRECTORY_SEPARATOR . $baseName . DIRECTORY_SEPARATOR . date("d");
                break;
            case 7:
                $week = ceil(date("d") / 7);
                $subDir .= DIRECTORY_SEPARATOR . $baseName . DIRECTORY_SEPARATOR . $week;
                break;
            case 30:
                $subDir .= DIRECTORY_SEPARATOR . $baseName;
                break;
            default:
                break;
        }
        $uploadDir .= DIRECTORY_SEPARATOR . $subDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        return rtrim($uploadDir, DIRECTORY_SEPARATOR);
    }

    /**
     * 判断當前是否處於一個模塊運行中.
     *
     * @return null | string 是在模塊中運行，返回模塊名字，否則返回null
     */
    public static function curRunAtModule()
    {

        $moduleId = null;
        if (isset(Yii::$app->controller)) {
            $module = Yii::$app->controller->module;
            if ($module) {
                $moduleId = $module->id;
            }
        }
        return $moduleId;
    }

    /**
     * 获取当前操作用户.
     *
     * @return int
     */
    public static function getOperator()
    {
        return Yii::$app->user->getId();
    }
}