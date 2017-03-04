<?php

/**
 * Cache访问助手.
 *
 * @author ChenBin
 * @version $Id:Cache.php, 1.0 2014-09-06 11:30+100 ChenBin$
 * @package: app\helpers
 * @since 2014-09-06 11:30
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use yii\base\Exception;

class Cache
{
    /** @var \yii\caching\Cache Cache Object */
    private static $_cache;
    /** @var boolean $_cacheDisabled Cache禁用设置 */
    private static $_cacheDisabled = false;

    /**
     * 创建唯一实例对象.
     *
     * @return \yii\caching\Cache|boolean;
     */
    public static function getCache()
    {
        if (self::$_cacheDisabled) {
            return false;
        } else {
            if (!self::$_cache instanceof \yii\caching\Cache) {
                if (\Yii::$app->cache instanceof \yii\caching\Cache) {
                    self::$_cache = \Yii::$app->cache;
                } else {
                    return false;
                }
            }
            return self::$_cache;
        }
    }

    /**
     * 禁用Cache功能
     *
     */
    public static function disableCache()
    {
        self::$_cacheDisabled = true;
    }

    /**
     * 启用Cache功能
     *
     */
    public static function enableCache()
    {
        self::$_cacheDisabled = false;
    }

    /**
     * Set a cache object
     *
     * @param object $cache
     */
    public function setCache(\yii\caching\Cache $cache)
    {
        self::$_cache = $cache;
    }

    /**
     * Removes any set cache
     *
     */
    public function removeCache()
    {
        self::$_cache = null;
    }

    /**
     * 以静态方法呼叫Cache助手相关方法.
     *
     * @param string $name
     * @param array $arguments
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if ($cache = self::getCache()) {
            if (method_exists($cache, $name)) {
                return call_user_func_array(array($cache, $name), $arguments);
            } else {
                throw new \Exception('方法' . $name . '未定义!');
            }
        } else {
            return null;
        }
    }

    /**
     * 缓存静态读取方法.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get($key)
    {
        if ($cache = self::getCache()) {
            return $cache->get($key);
        } else {
            return null;
        }
    }

    /**
     * 缓存静态写入方法.
     *
     * @param string $key
     * @param mixed $value
     * @param null|string $group
     * @param int $expire
     * @return bool|null
     */
    public static function set($key, $value, $group = null, $expire = 0)
    {
        if ($cache = self::getCache()) {
            $result = $cache->set($key, $value, $expire);
            if ($result && $group) {
                $result = self::_addKeyToGroup($group, $key) && $result;
                if (!$result) {
                    self::delete($key);
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 缓存内容删除方法.
     *
     * @param string $key
     * @return bool|null
     */
    public static function delete($key)
    {
        if ($cache = self::getCache()) {
            return $cache->delete($key);
        } else {
            return null;
        }
    }

    /**
     * 缓存内容批量删除方法
     *
     * @param string|array $keys
     * @return bool|null
     * @throws Exception
     */
    public static function batchDelete($keys)
    {
        if ($cache = self::getCache()) {
            if (is_string($keys)) {
                $keys = ArrayHelper::fromString($keys);
            }
            if (is_array($keys)) {
                $result = true;
                foreach ($keys as $key) {
                    $result &= self::delete($key);
                }
                return $result;
            } else {
                throw new Exception('无效的缓存参数keys');
            }
        } else {
            return null;
        }
    }

    /**
     * 缓存内容组清除或全部清除方法.
     *
     * @param null|string $group
     * @return bool|null
     */
    public static function flush($group = null)
    {
        if ($cache = self::getCache()) {
            if ($group) {
                $arr = self::get($group);
                if ($arr && is_array($arr)) {
                    foreach ($arr as $key => $value) {
                        self::delete($key);
                    }
                    return self::delete($group);
                } else {
                    return false;
                }
            } else {
                return $cache->flush();
            }
        } else {
            return null;
        }
    }

    /**
     * 检查Cache是否命中, 未命中返回true, 命中返回false.
     *
     * @param mixed
     * @return bool|mixed
     */
    public static function miss($value)
    {
        if (is_null($value) || $value === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加key到指定的组中
     *
     * @param string $group
     * @param string $key
     * @return bool
     */
    private static function _addKeyToGroup($group, $key)
    {
        $arr = self::get($group);
        if (!$arr || !is_array($arr)) {
            $arr = array();
        }
        $arr[$key] = 1;
        return self::set($group, $arr, null, 0);
    }
}