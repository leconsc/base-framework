<?php
/**
 *
 * Jquery UI资源组件
 *
 * @author ChenBin
 * @version $Id: JqueryUIAsset.php, 1.0 2017-01-14 08:41+100 ChenBin$
 * @package app\assetss
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, All rights Reserved.
 */


namespace app\assets;

class JqueryUIAsset extends LibraryAsset
{
    public $css = [
        'jui/css/base/jquery-ui.min.css'
    ];
    public $js = [
        'jui/js/jquery-ui.min.js',
        'jui/js/i18n/datepicker-zh-cn.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}