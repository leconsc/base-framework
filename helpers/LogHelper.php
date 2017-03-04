<?php
/**
 * Log相关函数封装
 *
 * @author chenbin
 * @version $Id:Log.php, 1.0 2014-08-21 15:19+100 chenbin$
 * @package: WeGames
 * @since 1.0
 * @copyright 2014(C)Copyright By CQTimes, All rights Reserved.
 */

class LogHelper
{
    /** @var  string 分类 */
    static private $_category = '';

    /**
     * 设置当前日志分类
     *
     * @param $category
     */
    public static function setCategory($category)
    {
        self::$_category = (string)$category;
    }

    /**
     * 记录修改操作日志.
     *
     * @param string $message  修改操作说明
     * @param array $revised 修改后的数据
     * @param array $original 原始数据
     */

    public static function writeModifyLog($message, array $revised = array(),array $original = array())
    {
        $logs = array();
        $logs['message'] = $message;
        if(count($original)){
        $logs['original'] = $original;
        }
        if(count($revised)){
        $logs['revised'] = $revised;
        }
        self::_log($logs, self::$_category, 'modify');
    }

    /**
     * 记录创建数据过程中日志.
     *
     * @param string $message 创建操作说明
     * @param array $created 创建的内容
     */
    public static function writeCreateLog($message, array $created = array())
    {
        $logs = array();
        $logs['message'] = $message;
        if(count($created)){
           $logs['created'] = $created;
        }
        self::_log($logs, self::$_category, 'create');
    }

    /**
     * 记录删除操作过程中的日志.
     *
     * @param string $message 删除操作说明
     * @param array $deleted 删除内容
     */

    public static function writeDeleteLog($message, array $deleted = array())
    {
        $logs = array();
        $logs['message'] = $message;
        if(count($deleted)){
           $logs['deleted'] = $deleted;
        }
        self::_log($logs, self::$_category, 'delete');
    }

    /**
     * 记录用户登录/登出日志
     *
     * @param $message
     */
    public static function writeLoginLog($message, $operator)
    {
        $logs = array();
        $logs['message'] = $message;
        self::_log($logs, 'login', null, $operator);
    }

    /**
     * 获取功能项列表.
     *
     * @return array
     */
    public static function getResourceItems()
    {
        $resources = array_change_key_case(Resource::getResources());
        $result = array();
        foreach ($resources as $group => $items) {
            $result = array_merge($result, array_change_key_case($items));
        }
        return $result;
    }

    /**
     * 写用户登录日志与操作日志.
     *
     * @param $message mixed 日志内容
     * @param $category string 所属分类
     * @param null|string $action 动作
     * @param null|string $operator 操作人
     * @return bool
     */
    private static function _log($message, $category, $action = null, $operator = null)
    {
        if (strcasecmp($category, 'login') && !ConfigHelper::get('writeOperationLog')
            || !strcasecmp($category, 'login') && !ConfigHelper::get('writeLoginLog')
        ) {
            return false;
        }

        if (!empty($operator)) {
            Yii::app()->user->setName($operator);
        }

        if (strcasecmp($category, 'login')) {
            $category = $category ? $category . '.' . $action : $action;
        }

        $message = Helper::jsonEncode($message);


        Yii::log($message, CLogger::LEVEL_INFO, $category);
    }
}