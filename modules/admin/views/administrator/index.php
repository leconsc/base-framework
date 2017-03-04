<?php
use app\widgets\FlexiGrid;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\HtmlHelper;
?>
    <div class="heading">
        <h2 class="user"><?= $this->context->title ?></h2>
    </div>
<?php
echo FlexiGrid::widget([
    'url' => Url::toRoute('get'),
    'title' => $this->context->title,
    'model' => $model,
    'colModels' => [
        ['toggleBox', 30, false, false, false],
        ['username', 120],
        ['truename', 80],
        ['uid', 60],
        ['gid', 80],
        ['role_type', 100],
        ['freeze', 80],
        ['created_at', 200],
        ['modified_at', 200],
        ['operate', 100, false, false, false]
    ],
    'defaultSort' => ['gid', 'desc'],
    'buttons' => [
        ['新增', 'create'],
        ['刪除', 'remove']
    ],
    'searchItems' => [
        ['searchWord', Html::textInput('searchWord', $searchWord, ['class'=>'form-text'])],
        ['freeze', '状态 ' . HtmlHelper::publishDropDownList($freeze), 'select'],
        ['roleType', '角色类型 ' . Html::dropDownList('roleType', $roleType, $roleTypes, ['prompt'=>'--全部--', 'class'=>'form-select']), 'select'],
        ['group', '所属用户组'.Html::dropDownList('group', $group, $groups, ['prompt'=>'--全部--', 'class'=>'form-select']), 'select'],
    ],
    'sortName' => $sortName,
    'sortOrder' => $sortOrder,
    'startPage' => $page,
    'limit' => $limit
]);
?>