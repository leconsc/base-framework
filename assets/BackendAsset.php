<?php
/**
 * App后台管理资源包
 *
 * @author ChenBin
 * @version $Id: BackendAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

use yii\web\AssetBundle;
use Yii;
/**
 * @author ChenBin
 * @since 1.0
 */
class BackendAsset extends AssetAbstract
{
    public $basePath = '@webroot/assets/backend';
    public $baseUrl = '@web/assets/backend';
    public $css = [
        'css/backend.css',
    ];
    public $js = [
    ];
    public $depends = [
        'app\assets\BaseAsset',
        'app\assets\DialogAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset'
    ];
}
