<?php
/**
 * 与用户Request请求相关状态数据保存与获取
 *
 * @author ChenBin
 * @version $Id:RequestHelper.php, v1.0 2014-12-05 10:16+100 ChenBin $
 * @package Helper
 * @since 1.0
 * @copyright 2013(C)Copyright By Chenbin,all rights reserved.
 */
namespace app\helpers;

use Yii;

/**
 * @access public
 * @author ChenBin
 */
class RequestHelper
{
    const REQUEST = 'REQUEST';
    const POST = 'POST';
    const GET = 'GET';
    const COOKIE = 'COOKIE';

    /** @var array request data */
    private static $_data = array();
    /** @var string $_prefix */
    private static $_prefix = null;
    /** @var boolean true if RequestHelper init or false */
    private static $_inited = false;

    /**
     * 设置userState数据源
     * @access public
     * @static
     * @param string|array $source
     * @return array
     */
    public static function setDataSource($source)
    {
        $oldData = self::$_data;
        if (is_array($source)) {
            self::$_data = &$source;
        } else {
            $source = strtoupper(settype($source, 'string'));
            switch ($source) {
                case self::GET:
                    self::$_data = &$_GET;
                    break;
                case self::POST:
                    self::$_data = &$_POST;
                    break;
                case self::COOKIE:
                    self::$_data = &$_COOKIE;
                    break;
                default:
                    self::$_data = $_GET + $_POST;
                    break;
            }
        }
        return $oldData;
    }

    /**
     * 获取取值源数据
     *
     * @return array
     */
    private static function _getData()
    {
        if (!self::$_inited) {
            self::$_data = $_GET + $_POST;
            self::$_inited = true;
        }
        return self::$_data;
    }

    /**
     * 设置使用的前缀.
     *
     * @param string $prefix
     * @param bool|true $withControllerId
     */
    public static function setPrefix($prefix, $withControllerId = true)
    {
        if ($withControllerId && Yii::$app->controller) {
            $prefix = Yii::$app->controller->id . '_' . $prefix . '_';
        } else {
            $prefix .= '_';
        }
        self::$_prefix = $prefix;
    }

    /**
     * 获取请求变量的前缀.
     *
     * @return string
     */
    public static function getPrefix()
    {
        $prefix = '';
        if (self::$_prefix === null) {
            if (Yii::$app->controller) {
                $prefix = Yii::$app->controller->id . '_';
            }
        } else {
            $prefix = self::$_prefix;
        }
        return $prefix;
    }


    /**
     * 获取有效的存储状态名称.
     *
     * @param string $name
     * @return string
     */
    private static function _getSessionName($name)
    {
        $prefix = self::getPrefix();
        $sessionName = $prefix . $name;
        return $sessionName;
    }

    /**
     * 设置状态值
     *
     * @param string $name 变量名
     * @param string $val 变量值
     * @return void
     */
    public static function set($name, $val)
    {
        $sessionName = self::_getSessionName($name);

        if(is_null($val)){
            Yii::$app->session->remove($sessionName);
        }else {
            Yii::$app->session->set($sessionName, $val);
        }
    }

    /**
     * 仅仅从Session中读取数据(保存时,Session中的数据已经是处理过的了).
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed
     */
    public static function read($name, $default = null, $filter = null, array $params = [])
    {
        $sessionName = self::_getSessionName($name);

        $value = Yii::$app->session->get($sessionName, $default);
        $type = null;
        if (isset($params['type'])) {
            $type = $params['type'];
        }
        $result = FilterHelper::filter($value, $default, $type, $filter);
        return $result;
    }

    /**
     * 获得用户请求变量的值(本操作会检查SESSION)
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function fetch($name, $default = null, $filter = null, array $params = [])
    {
        $sessionName = self::_getSessionName($name);
        $data = self::_getData();
        if (!isset($data[$name])) {
            return Yii::$app->session->get($sessionName, $default);
        }
        $result = self::get($name, $default, $filter, $params);
        if (!is_null($result)) {
            Yii::$app->session->set($sessionName, $result);
        }
        return $result;
    }

    /**
     * 获得用户GET请求中变量的值(本操作会检查SESSION)
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function fetchQuery($name, $default = null, $filter = null, array $params = [])
    {
        $oldData = self::setDataSource(self::GET);
        $result = self::fetch($name, $default, $filter, $params);
        self::setDataSource($oldData);
        return $result;
    }

    /**
     * 获得用户Post请求中变量的值(本操作会检查SESSION)
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function fetchPost($name, $default = null, $filter = null, array $params = [])
    {
        $oldData = self::setDataSource(self::POST);
        $result = self::fetch($name, $default, $filter, $params);
        self::setDataSource($oldData);
        return $result;
    }

    /**
     * 获得用户请求变量的值
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function get($name, $default = null, $filter = null, array $params = [])
    {
        $data = self::_getData();
        if (isset($data[$name])) {
            $result = null;
            $value = $data[$name];
            $type = null;
            if (isset($params['type'])) {
                $type = $params['type'];
            }
            $result = FilterHelper::filter($value, $default, $type, $filter);
            return $result;
        }

        return $default;
    }

    /**
     * 获得用户GET请求中变量的值
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function getQuery($name, $default = null, $filter = null, array $params = [])
    {
        $oldData = self::setDataSource(self::GET);
        $result = self::get($name, $default, $filter, $params);
        self::setDataSource($oldData);
        return $result;
    }

    /**
     * 获得用户Post请求中变量的值
     *
     * @param string $name 变量名称
     * @param mixed $default 默认值
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @param array $params 一些扩展参数
     * @return mixed|null
     */
    public static function getPost($name, $default = null, $filter = null, array $params = [])
    {
        $oldData = self::setDataSource(self::POST);
        $result = self::get($name, $default, $filter, $params);
        self::setDataSource($oldData);
        return $result;
    }
}

?>