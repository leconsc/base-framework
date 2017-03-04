<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV')
    or define('YII_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : (get_cfg_var('application.env') ? get_cfg_var('application.env') :'production')));
defined('APP_TYPE') or define('APP_TYPE', 'web');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/config.php');
(new yii\web\Application($config))->run();
