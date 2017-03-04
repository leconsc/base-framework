<?php
/**
 * 数据库模型基本Class.
 *
 * @author ChenBin
 * @version $Id:BaseModel.php, 1.0 2014-09-04 17:38+100 ChenBin$
 * @package: app\base
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\base;

use app\helpers\ArrayHelper;
use app\helpers\SystemHelper;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

abstract class Model extends ActiveRecord
{
    const CREATE = 'create';
    const EDIT = 'edit';

    /**
     * 重载保存功能，仅保存有效属性.
     *
     * @param bool $runValidation
     * @param null|array $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (is_null($attributeNames)) {
            $attributeNames = $this->getEnableSavingAttributes();
        }
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * 获取允许保存或更新的字段值.
     *
     * @return array
     */
    protected function getEnableSavingAttributes()
    {
        return $this->safeAttributes();
    }

    /**
     * 获取分类列表.
     *
     * @param ActiveQueryInterface $query 　获取条件
     * @param string $order 排列顺序
     * @param int $offset 偏移量
     * @param int $limit 获取条数
     * @return array array(查询数量,查询数据)
     */
    public static function getList(ActiveQueryInterface $query, $order, $limit = null, $offset = 0)
    {

        $query = $query->orderBy($order);
        if (!is_null($limit)) {
            $limit = intval($limit);
            $offset = intval($offset);
            $query->offset($offset)
                ->limit($limit);
        }

        return [$query->count(), $query->asArray()->all()];
    }

    /**
     * 数据保存前.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $time = time();
        $operator = SystemHelper::getOperator();
        if ($insert) {
            if ($this->hasAttribute('created_at')) {
                $this->created_at = $time;
            }
            if ($this->hasAttribute('created_by')) {
                $this->created_by = $operator;
            }
        } else {
            if ($this->hasAttribute('modified_at')) {
                $this->modified_at = $time;
            }
            if ($this->hasAttribute('modified_by')) {
                $this->modified_by = $operator;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * 更新关键词状态
     *
     * @param array|integer|string $pk 更新使用的KEY
     * @param array $condition
     * @param string $field
     * @param boolean $toInteger
     * @return boolean|integer 如果操作成功，返回改变状态的条目数量，否则返回false
     */
    public static function changeState($pk, array $condition = [], $field = null, $toInteger = true)
    {
        $result = false;
        if ($toInteger) {
            if (is_array($pk)) {
                $pk = ArrayHelper::toInteger($pk);
            } else {
                $pk = intval($pk);
            }
        }
        if (!empty($pk)) {
            $data = array();
            if (empty($field)) {
                $field = 'published';
            }
            $data[$field] = new Expression('1-' . $field);
            $model = new static();
            if ($model->hasAttribute('modified_at')) {
                $data['modified_at'] = time();
            }
            if ($model->hasAttribute('modified_by')) {
                $data['modified_by'] = SystemHelper::getOperator();
            }
            unset($model);

            $primaryKey = static::primaryKey()[0];
            $condition[$primaryKey] = $pk;

            $result = static::updateAll($data, $condition);
        }
        return $result;
    }

    /**
     * 删除指定的项
     *
     * @param array|integer|string $pk 删除项的PrimaryKey
     * @param array $condition 删除条件
     * @param bool|true $toInteger 　是否转换PrimaryKey为整型
     * @return boolean|integer 如果操作成功，返回删除的条目数量，否则返回false
     */
    public static function remove($pk, array $condition = [], $toInteger = true)
    {
        if ($toInteger) {
            if (is_array($pk)) {
                $pk = ArrayHelper::toInteger($pk);
            } else {
                $pk = intval($pk);
            }
        }
        $result = false;
        if (!empty($pk)) {
            $primaryKey = static::primaryKey()[0];
            $condition[$primaryKey] = $pk;
            $result = static::deleteAll($condition);
        }
        return $result;
    }

    /**
     * 以查询结果以指定的列组织数据
     *
     * @param string $columnName 列名
     * @param ActiveQueryInterface $query 查询生成器
     * @return array
     */
    public static function createAssoc($columnName, ActiveQueryInterface $query = null)
    {
        if(is_null($query)) {
            $query = static::find();
        }
        $rows = $query->asArray()->all();
        $result = array();
        foreach ($rows as $row) {
            if (isset($row[$columnName])) {
                $result[$row[$columnName]] = $row;
            }
        }
        return $result;
    }

    /**
     * 从数据库中获取指定名称的键值对
     *
     * @param string $keyName 键名
     * @param string $valueName 值名
     * @param ActiveQueryInterface $query 查询生成器
     * @param boolean $addSelect 是否添加Select语句
     * @return array
     */
    public static function createPairs($keyName, $valueName, ActiveQueryInterface $query = null, $addSelect = true)
    {
        if(is_null($query)) {
            $query = static::find();
        }
        if($addSelect) {
            $query->select([$keyName, $valueName]);
        }
        $rows = $query->asArray()->all();
        $result = array();
        foreach ($rows as $row) {
            $result[$row[$keyName]] = $row[$valueName];
        }
        return $result;
    }

    /**
     * 从查询结果中获取指定列的数据
     *
     * @param string $columnName 列名
     * @param ActiveQueryInterface $query 查询生成器
     * @return array
     */
    public static function createColumn($columnName, ActiveQueryInterface $query = null)
    {
        if(is_null($query)) {
            $query = static::find();
        }
        $query->select($columnName);
        return $query->asArray()->column();
    }

    /**
     * 返回所有错误中的第一条错误.
     *
     * @return mixed|string
     */
    public function getSimpleError()
    {
        if ($this->hasErrors()) {
            $errors = $this->getErrors();
            foreach ($errors as $name => $es) {
                if (!empty($es)) {
                    return reset($es);
                }
            }
        }
        return '';
    }
    /**
     * 获取分页列表项.
     *
     * @param int $limit
     * @param ActiveQueryInterface $query
     * @return array
     */
    public static function getPageItems(ActiveQueryInterface $query, $limit = 20)
    {
        $query->asArray();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $pagination->setPageSize($limit, true);
        $items = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [$pagination, $items];
    }
}