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

class FlexiGridAsset extends AssetAbstract
{
    public $basePath = '@webroot/assets/libs';
    public $baseUrl = '@web/assets/libs';
    public $css = [
        'flexigrid/css/flexigrid.css',
    ];
    public $js = [
        'flexigrid/flexigrid.pack.js'
    ];
}