<?php
/**
 * 基础资源包
 *
 * @author ChenBin
 * @version $Id: BaseAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author chenbin
 * @since 1.0
 */
class BaseAsset extends LibraryAsset
{
    public $css = [
        'css/normalize.css',
    ];
    public $js = [
        ['html5shiv.min.js', 'condition'=>'lt IE 9'],
        ['respond.min.js', 'condition'=>'lt IE 9']
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}
