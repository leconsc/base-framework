<?php
/**
 * APP验证资源包
 *
 * @author ChenBin
 * @version $Id: ValidationAsset.php, 1.0 2016-10-15 18:34+100 ChenBin$
 * @package: app\assets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\assets;

class ValidationAsset extends LibraryAsset
{
    public $js = [
        'app.validation.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\validators\ValidationAsset'
    ];
}