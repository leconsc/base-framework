<?php
/**
 * 微信组件封装资源包
 *
 * @author ChenBin
 * @version $Id: WeuiAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

/**
 * @author ChenBin
 * @since 1.0
 */
class WeuiAsset extends LibraryAsset
{
    public $css = [
        'weui/weui.min.css'
    ];
    public $js = [
        'weui/weui.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
