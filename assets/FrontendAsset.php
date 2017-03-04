<?php
/**
 * 前端部分资源包
 *
 * @author ChenBin
 * @version $Id: AppAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

/**
 * @author ChenBin
 * @since 1.0
 */
class FrontendAsset extends AssetAbstract
{
    public $basePath = '@webroot/assets/frontend';
    public $baseUrl = '@web/assets/frontend';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'app\assets\BaseAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset'
    ];
}
