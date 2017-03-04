<?php

namespace app\models;

use app\helpers\Cache;
use MongoDB\Driver\Query;
use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent
 * @property integer $published
 * @property integer $ordering
 * @property integer $created_at
 * @property string $created_by
 * @property integer $modified_at
 * @property string $modified_by
 */
class Category extends \app\base\Model
{
    const CACHE_ITEMS_KEY = 'category_items';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'parent', 'published', 'ordering'], 'required'],
            [['parent', 'published', 'ordering'], 'integer'],
            ['published', 'in', 'range'=>[0, 1]],
            [['name'], 'string', 'max' => 60],
            [['created_at', 'created_by', 'modified_at', 'modified_by'], 'safe'],
            ['name', 'unique'],
            ['parent', 'checkParent']
        ];
    }

    /**
     * 检查分类是否正确.
     *
     * @param string $attribute
     * @param array|null $params
     */
    public function checkParent($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $primaryKey = self::primaryKey()[0];
            $parents = self::createColumn($primaryKey);
            if (!$this->isNewRecord) {
                $parents = array_diff($parents, [$this->$primaryKey]);
            }
            array_unshift($parents, 0);
            if (!in_array($this->$attribute, $parents)) {
                $this->addError($attribute, '错误的上级分类');
            }
        }
    }

    /**
     * 场景定义.
     *
     * @return array
     */
    public function scenarios()
    {
        $scenarios = [];
        $scenarios[self::CREATE] = ['name', 'parent', 'published', 'ordering', 'created_at', 'created_by'];
        $scenarios[self::EDIT] = ['name', 'parent', 'published', 'ordering', 'modified_at', 'modified_by'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '分类编号',
            'name' => '分类名称',
            'parent' => '上级分类',
            'published' => '是否发布',
            'ordering' => '排列顺序',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'modified_at' => '修改时间',
            'modified_by' => '修改人',
        ];
    }

    /**
     * 获取分类ID=>分类名称列表。
     *
     * @return array
     */
    public static function getItems()
    {
        $cacheKey = sprintf(self::CACHE_ITEMS_KEY);
        $items = Cache::get($cacheKey);
        if ($items === false) {
            $query = self::find()
                    ->where('published=1')
                    ->orderBy('ordering DESC');

            $items = self::createPairs('id', 'name', $query);
            Cache::set($cacheKey, $items);
        }
        return $items;
    }
}
