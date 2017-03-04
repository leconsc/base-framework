<?php
/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
// Yii::setAlias('@webroot', __DIR__ . '/../web');
// Yii::setAlias('@web', '/');

return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    'bundles' => [
        'app\assets\BaseAsset',
        'yii\web\JqueryAsset',
        'app\assets\DialogAsset',
        'app\assets\ValidationAsset',
        'app\assets\FrontendAsset'
    ],
    // Asset bundle for compression output:
    'targets' => [
        'frontend' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets/frontend',
            'baseUrl' => '@web/assets/frontend',
            'js' => 'js/frontend.js',
            'css' => 'css/frontend.css',
            'depends'=>[
                'app\assets\BaseAsset'
            ]
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
        'basePath' => '@webroot/assets',
        'baseUrl' => '@web/assets',
    ],
];