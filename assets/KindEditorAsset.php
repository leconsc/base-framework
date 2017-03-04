<?php
/**
 * KindEditor资源包
 *
 * @author ChenBin
 * @version $Id: KindEditorAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

/**
 * @author ChenBin
 * @since 1.0
 */
class KindEditorAsset extends LibraryAsset
{
    public $css = [
        'kindeditor/themes/default/default.css'
    ];
    public $js = [
        'kindeditor/kindeditor-min.js',
        'kindeditor/lang/zh_CN.js'
    ];
}
