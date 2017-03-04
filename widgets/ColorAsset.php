<?php
/**
 * FlexiGrid资源包定义
 *
 * @author ChenBin
 * @version $Id: FlexiGridAsset.php, 1.0 2016-10-13 07:22+100 ChenBin$
 * @package: app\widgets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\widgets;


use app\assets\AssetAbstract;

class ColorAsset extends AssetAbstract
{
    public $basePath = '@webroot/assets/libs/colorpicker';
    public $baseUrl = '@web/assets/libs/colorpicker';
    public $css = [
        'spectrum.css',
    ];
    public $js = [
        'spectrum.js',
        'i18n/jquery.spectrum-zh-cn.js'
    ];
}