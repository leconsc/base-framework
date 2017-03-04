<?php
return [
    'yii\bootstrap\BootstrapAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/bootstrap',
        'baseUrl' => '@web/assets/libs/bootstrap',
        'css' => [
            'css/bootstrap.min.css',
            'css/bootstrap-theme.min.css'
        ]
    ],
    'yii\bootstrap\BootstrapPluginAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/bootstrap',
        'baseUrl' => '@web/assets/libs/bootstrap',
        'js' => [
            'js/bootstrap.min.js'
        ]
    ],
    'yii\web\JqueryAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/jquery',
        'baseUrl' => '@web/assets/libs/jquery',
        'js' => [
            'jquery.min.js',
            'jquery-migrate-1.2.1.min.js'
        ],
        'jsOptions' => [
            'position' => \yii\web\View::POS_HEAD
        ]
    ],
    'yii\web\YiiAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/yii',
        'baseUrl' => '@web/assets/libs/yii',
        'js' => [
            'yii.js'
        ]
    ],
    'yii\validators\ValidationAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/yii',
        'baseUrl' => '@web/assets/libs/yii',
        'js' => [
            'yii.validation.js'
        ]
    ],
    'yii\captcha\CaptchaAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs/yii',
        'baseUrl' => '@web/assets/libs/yii',
        'js' => [
            'yii.captcha.js'
        ]
    ],
    'yii\widgets\ActiveFormAsset' => [
        'sourcePath' => null,
        'basePath' => '@webroot/assets/libs',
        'baseUrl' => '@web/assets/libs',
        'js' => [
            'app.activeForm.js'
        ]
    ]
];