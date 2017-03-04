<?php
/**
 * 配置文件载入
 *
 * @author ChenBin
 * @version $Id: config.php, 1.0 2016-09-06 10:21+100 ChenBin$
 * @package: app\config
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
use yii\helpers\ArrayHelper;

define('BASE_PATH', dirname(__DIR__));
define('CONFIG_DIR', __DIR__ . DIRECTORY_SEPARATOR . APP_TYPE . DIRECTORY_SEPARATOR);
define('CONFIG_FILE_EXTENSION', '.php');

//在配置文件可用配置项,因为这些配置项比较复杂，所以可以单独提取出来以文件进行配置
$configItems = ['aliases', 'bootstrap', 'modules', 'components', 'controllerMap', 'extensions', 'params'];
$componentItems = ['db', 'bundles'];
$config = [];

$configFiles = array(
    CONFIG_DIR . APP_TYPE . CONFIG_FILE_EXTENSION,
    CONFIG_DIR . YII_ENV . CONFIG_FILE_EXTENSION
);
foreach ($configFiles as $configFile) {
    if (is_file($configFile)) {
        $config = ArrayHelper::merge($config, require $configFile);
    }
}

foreach ($configItems as $item) {
    $optionFile = CONFIG_DIR . DIRECTORY_SEPARATOR . $item . CONFIG_FILE_EXTENSION;
    $itemConfig = [];
    if (is_file($optionFile)) {
        $itemConfig = ArrayHelper::merge($itemConfig, require($optionFile));
    }
    $envOptionFile = CONFIG_DIR . DIRECTORY_SEPARATOR . YII_ENV . DIRECTORY_SEPARATOR . $item . CONFIG_FILE_EXTENSION;
    if (is_file($envOptionFile)) {
        $itemConfig = ArrayHelper::merge($itemConfig, require($envOptionFile));
    }
    if (count($itemConfig)) {
        if (!isset($config[$item])) {
            $config[$item] = [];
        }
        $config[$item] = ArrayHelper::merge($config[$item], $itemConfig);
    }
}

foreach ($componentItems as $item) {
    if (!$config['components']) {
        $config['components'] = [];
    }
    $optionFile = CONFIG_DIR . DIRECTORY_SEPARATOR . $item . CONFIG_FILE_EXTENSION;
    $itemConfig = [];
    if (is_file($optionFile)) {
        $itemConfig = ArrayHelper::merge($itemConfig, require($optionFile));
    }
    $envOptionFile = CONFIG_DIR . DIRECTORY_SEPARATOR . YII_ENV . DIRECTORY_SEPARATOR . $item . CONFIG_FILE_EXTENSION;
    if (is_file($envOptionFile)) {
        $itemConfig = ArrayHelper::merge($itemConfig, require($envOptionFile));
    }
    if (count($itemConfig)) {
        switch ($item) {
            case 'db':
                if (isset($itemConfig['db'])) {
                    $config['components'] = ArrayHelper::merge($config['components'], $itemConfig);
                } else {
                    if (!isset($config['components']['db'])) {
                        $config['components']['db'] = [];
                    }
                    $config['components']['db'] = ArrayHelper::merge($config['components']['db'], $itemConfig);
                }
                break;
            case 'bundles':
                if (!isset($config['components']['assetManager'])) {
                    $config['components']['assetManager'] = [];
                }
                if (!isset($config['components']['assetManager']['bundles'])) {
                    $config['components']['assetManager']['bundles'] = [];
                }
                $config['components']['assetManager']['bundles'] = ArrayHelper::merge($config['components']['assetManager']['bundles'], $itemConfig);
                break;
        }
    }
}
return $config;