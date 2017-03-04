<?php

namespace app\models;

use app\base\Model;
use app\helpers\Validator;
use yii\db\Expression;

/**
 * This is the model class for table "content".
 *
 * @property integer $id
 * @property string $title
 * @property string $title_color
 * @property string $summary
 * @property string $content
 * @property integer $click
 * @property integer $published
 * @property integer $recommend
 * @property integer $cat_id
 * @property integer $ordering
 * @property integer $created_at
 * @property string $created_by
 * @property integer $modified_at
 * @property string $modified_by
 */
class Content extends Model
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'cat_id', 'content', 'published', 'ordering'], 'required'],
            [['content', 'summary'], 'string'],
            [['published', 'recommend'], 'in', 'range' => [0, 1]],
            [['cat_id', 'ordering', 'click'], 'integer'],
            [['title'], 'string', 'max' => 100],
            [['title_color'], 'string', 'max' => 7],
            [['created_at', 'created_by', 'modified_at', 'modified_by'], 'safe'],
            ['cat_id', 'exist', 'targetClass' => Category::className(), 'targetAttribute' => Category::primaryKey()[0]]
        ];
    }

    /**
     * 场景定义.
     *
     * @return array
     */
    public function scenarios()
    {
        $scenarios = [];
        $scenarios[self::CREATE] = ['title_color', 'title', 'published','summary', 'content', 'cat_id', 'click', 'recommend', 'ordering', 'created_at', 'created_by'];
        $scenarios[self::EDIT] = ['title_color', 'title', 'published','summary', 'content', 'cat_id', 'click', 'recommend', 'ordering', 'modified_at', 'modified_by'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '文章编号',
            'title' => '文章标题',
            'title_color' => '标题颜色',
            'summary' => '摘要',
            'content' => '内容',
            'click' => '点击次数',
            'published' => '是否发布',
            'cat_id' => '所属分类',
            'recommend' => '是否推荐',
            'ordering' => '排列顺序',
            'created_at' => '创建日期',
            'created_by' => '创建人',
            'modified_at' => '修改日期',
            'modified_by' => '修改人',
        ];
    }

    /**
     * 记录文章点击数.
     *
     * @param integer $id
     * @return bool
     */
    public static function click($id)
    {
        $id = intval($id);
        if ($id) {
            self::updateAll(['click' => new Expression('click+1')], ['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * 获取推荐文章.
     *
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRecommendItems($limit = 0){
        $query = self::find()
            ->select(['id', 'title', 'title_color', 'summary'])
            ->where(['published'=>1, 'recommend'=>1])
            ->orderBy(['ordering'=>SORT_DESC])
            ->asArray();
        if(Validator::isInt($limit) && $limit > 0){
            $query->limit($limit);
        }
        return $query->all();
    }
}
