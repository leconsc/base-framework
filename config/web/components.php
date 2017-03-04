<?php
/**
 * 组件相关的配置
 *
 * @author chenbin
 * @version $Id: components.php, 1.0 2016-09-05 16:54+100 chenbin$
 * @package: rongkai
 * @since 1.0
 * @copyright 2016(C)Copyright By chenbin, All rights Reserved.
 */

return [
    'request' => [
        'cookieValidationKey' => '527gl3wAO7BPzoBVrRYpZJI8PbzQA5fQ',
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'session' =>[
        'name' => 'PHPFRONTSESSID',
    ],
    'user' => [
        'identityClass' => 'app\services\MemberIdentity',
        'enableAutoLogin' => true,
        'identityCookie' => [
            'name' => '_frontendUser'
        ]
    ],
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'enableStrictParsing' => false,
        'rules' => [
           '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
           'page/<alias:\w+>' => 'page/index',
           'admin/<controller:\w+>/<action:\w+>/<id:\d+>' => 'admin/<controller>/<action>',
            [
                'class' => 'app\components\NormalRule'
            ]
        ]
    ],
    'authManager' => [
        'class' => 'yii\rbac\DbManager',
        'defaultRoles' =>['author'],
    ],
    'formatter' => [
        'dateFormat' => 'yyyy-MM-dd',
        'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss'
    ],
    'view' => [
        'theme' => [
            'basePath' => '@app/themes/basic',
            'baseUrl' => '@web/assets/themes/basic',
            'pathMap' => [
                '@app/views' => '@app/themes/basic',
            ],
        ],
    ],
    'assetManager'=>[
        'appendTimestamp'=>true
    ],
];