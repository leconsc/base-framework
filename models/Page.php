<?php

namespace app\models;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property string $alias
 * @property string $title
 * @property string $content
 * @property integer $published
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $modified_at
 * @property integer $modified_by
 */
class Page extends \app\base\Model
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alias', 'title', 'content'], 'required'],
            [['alias'], 'match', 'pattern' => '/^[a-z]+[a-z0-9_-]+$/i', 'message' => '别名必须是字母开头，然后由字母、数字、减号或下划线组成'],
            [['content'], 'string'],
            [['published'], 'in', 'range'=>[0, 1]],
            [['alias'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 50],
            [['created_at', 'created_by', 'modified_at', 'modified_by'], 'safe'],
            [['alias'], 'unique'],
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
        $scenarios[self::CREATE] = ['alias', 'title', 'content', 'published','created_at', 'created_by'];
        $scenarios[self::EDIT] = ['alias', 'title', 'content', 'published', 'modified_at', 'modified_by'];
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一标识',
            'alias' => '别名标识',
            'title' => '页面标题',
            'content' => '页面内容',
            'published' => '是否发布',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'modified_at' => '修改时间',
            'modified_by' => '修改人',
        ];
    }
}
