<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests/codeception');
return [
    'id' => 'basic-console',
    'basePath' => BASE_PATH,
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
          '@tests' =>BASE_PATH . '/tests/codeception',
          '@webroot' =>BASE_PATH . '/web',
          '@web' =>'/web',
    ],
    'controllerMap' => [
        'migrates' => [
            'class' => 'e282486518\migration\ConsoleController',
        ],
    ],
];
