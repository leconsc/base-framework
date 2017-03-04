<?php
/**
 * 配置信息获取组件
 *
 * @author ChenBin
 * @version $Id:Config.php, 1.0 2014-08-21 15:49+100 ChenBin$
 * @package: app\helpers
 * @since 2014-08-21 15:49
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use app\models\Configuration;
use yii\helpers\ArrayHelper;
use Yii;

class Config
{
    const CACHE_KEY = 'app_config';

    /**
     * 获取配置值
     * @access public
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public static function get($name = null, $default = null)
    {
        static $config = null;
        if (!is_array($config)) {
            $config = Cache::get(self::CACHE_KEY);
            if (Cache::miss($config)) {
                $config = Yii::$app->params;
                $sysConfig = Configuration::getItems();
                $config = ArrayHelper::merge($config, $sysConfig);
                Cache::set(self::CACHE_KEY, $config);
            }
        }

        if (is_null($name)) {
            return $config;
        } else if (strpos($name, '.') !== false) {
            // Explode the registry path into an array
            $nodes = explode('.', $name);
            // Initialize the current node to be the registry root.
            $node = $config;
            $found = false;
            // Traverse the registry to find the correct node for the result.
            foreach ($nodes as $n) {
                if (isset ($node[$n])) {
                    $node = $node[$n];
                    $found = true;
                } else {
                    $found = false;
                    break;
                }
            }

            if ($found && $node !== null && $node !== '') {
                return $node;
            }
        } else if (array_key_exists($name, $config)) {
            return $config[$name];
        }
        return $default;
    }

    /**
     * 清除Config Cache
     */
    public static function clean()
    {
        Cache::delete(self::CACHE_KEY);
    }
}