<?php

/**
 * Db相关助手.
 *
 * @author ChenBin
 * @version $Id:DbHelper.php, 1.0 2014-09-12 10:02+100 ChenBin$
 * @package: app\helpers
 * @since 2014-09-12 10:02
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use Yii;
use yii\db\Connection;
use yii\db\Transaction;

final class DbHelper
{
    /**
     * @var Connection $defaultConnection
     *
     */
    private static $_defaultConnection ;

    /**
     * 设置默认数据库连接名字.
     *
     * @param Connection $connection
     */
    public static function setDefaultConnection(Connection $connection)
    {
         self::$_defaultConnection = $connection;
    }

    /**
     * 获取默认连接.
     *
     * @return Connection
     */
    private static function _getDefaultConnection(){
        if(!self::$_defaultConnection instanceof Connection){
            self::$_defaultConnection = Yii::$app->db;
        }
        return self::$_defaultConnection;
    }
    /**
     * 從數據庫中獲取一行數據.
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function fetchRow($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $row = $command->queryOne();
        return $row;
    }

    /**
     * 從數據庫中獲取所有查詢結果數據.
     *
     * @param string $sql string sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function fetchAll($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * 以查询结果第一列值为数组KEY组织数据.
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return array
     */
    public static function fetchAssoc($sql, array $params = [], Connection $connection = null){
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $rows = $command->queryAll();
        $result = [];
        foreach($rows as $row){
             $key = current($row);
             $result[$key] = $row;
        }
        return $result;
    }

    /**
     * 從數據庫中獲取鍵值對數據
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function fetchPairs($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $rows = $command->queryAll();
        $pairs = [];
        foreach ($rows as $row) {
            $pairs[$row[0]] = $row[1];
        }
        return $pairs;
    }

    /**
     * 從數據庫中獲取第一行第一列的數據.
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function fetchScalar($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $scalar = $command->queryScalar();
        return $scalar;
    }

    /**
     * 從數據庫中獲取第一列的數據.
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function fetchColumn($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        $column = $command->queryColumn();
        return $column;
    }

    /**
     * 執行一個SQL語句.
     *
     * @param string $sql sql語句
     * @param array $params bind參數
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function execute($sql, array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand($sql);
        if(count($params)){
            $command->bindValues($params);
        }
        return $command->execute();
    }

    /**
     * 插入数据.
     *
     * @param string $table 表名
     * @param array $columns 插入的列
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function insert($table, array $columns, Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand();
        if ($command->insert($table, $columns)) {
            return $connection->lastInsertID;
        }
        return false;
    }

    /**
     * 更新數據
     *
     * @param string $table 表名
     * @param array $columns 更新的列
     * @param string $condition 更新条件
     * @param array $params bind参数
     * @param Connection $connection 使用的其它DB连接
     * @return mixed
     */
    public static function update($table, array $columns, $condition = '', array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand();
        return $command->update($table, $columns, $condition, $params);
    }

    /**
     * 删除表数据.
     *
     * @param string $table 表名
     * @param string $condition 删除条件
     * @param array $params 绑定参数
     * @param string $connection 使用的其它DB连接
     * @return mixed
     */
    public static function delete($table, $condition = '', array $params = [], Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        $command = $connection->createCommand();
        return $command->delete($table, $condition, $params);
    }

    /**
     * 事务处理开始.
     *
     * @param Connection $connection 使用的其它DB连接
     * @return Transaction the transaction initiated
     */
    public static function beginTransaction(Connection $connection = null)
    {
        if(is_null($connection)){
            $connection = self::_getDefaultConnection();
        }
        return $connection->beginTransaction();
    }
    /**
     * 其他函數請求
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        $oReflectionClass = new \ReflectionClass('Command');
        if ($oReflectionClass->hasMethod($method)) {
            $oMethod = $oReflectionClass->getMethod($method);
            $numberOfParameters = $oMethod->getNumberOfParameters() + 1;
            $connection = null;
            if(count($params) == $numberOfParameters && $params[$numberOfParameters] instanceof Connection) {
                $connection = $params[$numberOfParameters];
                unset($params[$numberOfParameters]);
            }else{
                $connection = self::_getDefaultConnection();
            }
            $command = $connection->createCommand();
            return $oMethod->invokeArgs($command, $params);
        } else {
            throw new \Exception('方法' . $method . '未定義!');
        }
    }
}