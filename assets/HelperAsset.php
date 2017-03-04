<?php
/**
 * 常用JS工具封装资源
 *
 * @author ChenBin
 * @version $Id: HelperAsset.php, 1.0 2017-01-21 13:05+100 ChenBin$
 * @package app\assets
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, All rights Reserved.
 */


namespace app\assets;


use yii\web\View;

class HelperAsset extends LibraryAsset
{
    public $js = [
        'helper.js'
    ];
    public $jsOptions =[
        'position' => View::POS_HEAD
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}