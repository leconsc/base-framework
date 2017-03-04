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
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'log' => [
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=tellhim',
        'username' => 'tellhim',
        'password' => '8Lezq%DwSzUb',
        'charset' => 'utf8mb4',
    ]
];