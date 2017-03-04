<?php
/**
 * 对话框资源包
 *
 * @author ChenBin
 * @version $Id: DialogAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

/**
 * @author ChenBin
 * @since 1.0
 */
class DialogAsset extends LibraryAsset
{
    public $css = [
        'dialog/css/ui-dialog.css',
        'dialog/css/dialog.css'
    ];
    public $js = [
        'dialog/dialog-plus-min.js',
        'dialog/dialog.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
