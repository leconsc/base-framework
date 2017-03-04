<?php
/**
 * 模型排序Trait
 *
 * @author ChenBin
 * @version $Id: ModelOrderTrait.php, 1.0 2017-02-25 13:19+100 ChenBin$
 * @package app\traits
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, All rights Reserved.
 */


namespace app\traits;


use yii\db\ActiveQueryInterface;
use yii\db\Expression;

trait ModelOrderTrait
{
    /**
     * 获取当前最大排列顺序
     *
     * @param ActiveQueryInterface|null $query
     * @return bool|int
     */
    public function getMaxOrdering(ActiveQueryInterface $query = null)
    {
        if ($this->hasAttribute('ordering')) {
            if (is_null($query)) {
                $query = static::find();
            }
            $express = new Expression('max(ordering)+1');
            $query->select($express);

            $result = $query->scalar();
            return $result ? intval($result) : 1;
        }
        return false;
    }

    /**
     * 执行排序移动操作
     *
     * @param string $sortOrder
     * @param string $moveDirection
     * @param null|ActiveQueryInterface $query
     * @return bool
     */
    public function order($sortOrder, $moveDirection, ActiveQueryInterface $query = null)
    {
        if (!$this->hasAttribute('ordering')) {
            return false;
        }
        $sortOrder = strtolower($sortOrder);
        if (!in_array($sortOrder, array('desc', 'asc'))) {
            $sortOrder = 'desc';
        }
        $moveDirection = strtolower($moveDirection);
        if (strcasecmp($sortOrder, 'desc')) {
            if (strcasecmp($moveDirection, 'orderUp')) {
                $direction = 1;
            } else {
                $direction = -1;
            }
        } else {
            if (strcasecmp($moveDirection, 'orderUp')) {
                $direction = -1;
            } else {
                $direction = 1;
            }
        }
        $compOps = array(-1 => '<', 0 => '=', 1 => '>');
        $relation = $compOps[($direction > 0) - ($direction < 0)];
        $ordering = ($relation == '<' ? 'DESC' : 'ASC');
        $primaryKey = static::primaryKey()[0];
        $o1 = $this->ordering;
        $k1 = $this->$primaryKey;

        if (is_null($query)) {
            $query = static::find();
        }
        $query = $query->select([$primaryKey, 'ordering'])
            ->andWhere([$relation, 'ordering', $o1]);
        $row = $query->orderBy('ordering ' . $ordering)->one();
        if ($row) {
            $o2 = $row->ordering;
            $k2 = $row->$primaryKey;
            $express = new Expression("(ordering=$o1)*$o2 + (ordering=$o2)*$o1");
            static::updateAll(['ordering' => $express], "$primaryKey = $k1 OR $primaryKey = $k2");
        }
        return true;
    }

    /**
     * 更新ordering值.
     *
     * @param array $idArray
     * @param array $orderArray
     * @param ActiveQueryInterface $query
     * @return bool
     */
    public function updateOrder(array $idArray, array $orderArray, ActiveQueryInterface $query = null)
    {
        if (!$this->hasAttribute('ordering')) {
            return false;
        }
        $primaryKey = static::primaryKey()[0];
        if (!empty($idArray) AND !empty($orderArray)) {
            $set = array();
            foreach ($idArray as $i => $id) {
                $o = intval($orderArray[$i]);
                $set[] = "($primaryKey='{$id}')*$o";
            }
            $setValue = implode(' + ', $set);
            static::updateAll(['ordering' => new Expression($setValue)], ['in', $primaryKey, $idArray]);
        }
        if (is_null($query)) {
            $query = static::find();
        }
        $query = $query->select([$primaryKey, 'ordering']);
        $rows = $query->orderBy('ordering')->all();
        $i = 1;
        foreach ($rows as $row) {
            static::updateAll(['ordering' => $i], ['=', $primaryKey, $row[$primaryKey]]);
            $i++;
        }
        return true;
    }
    /**
     * 改变数据顺序
     *
     * @param integer|string $pk
     * @param string $sortOrder
     * @param string $moveDirection
     * @param null $condition
     * @param bool|true $toInteger
     * @return bool
     */
    public function changeOrder($pk, $sortOrder, $moveDirection, $condition = null, $toInteger = true)
    {
        $model = new static();
        if (!$model->hasAttribute('ordering')) {
            return false;
        }
        $status = false;

        if ($toInteger) {
            $pk = intval($pk);
        }
        if ($pk) {
            $item = static::findOne($pk);
            if(!empty($condition)) {
                $query = static::find()->where($condition);
            }else{
                $query = null;
            }
            $status = $item->order($sortOrder, $moveDirection, $query);
        }
        return $status;
    }
}