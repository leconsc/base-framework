<?php
/**
 * EasyUI资源包
 *
 * @author ChenBin
 * @version $Id: EasyUiAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

/**
 * @author ChenBin
 * @since 1.0
 */
class EasyUiAsset extends LibraryAsset
{
    public $css = [
        'easyui/themes/bootstrap/easyui.css',
        'easyui/themes/icon.css'
    ];
    public $js = [
        'easyui/jquery.easyui.min.js',
        'easyui/locale/easyui-lang-zh_CN.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
