<?php
use app\widgets\FlexiGrid;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\HtmlHelper;
use app\widgets\EasyUiTreeDropDown;
?>
    <div class="heading">
        <h2 class="content"><?= $this->context->title ?></h2>
    </div>
<?php
echo FlexiGrid::widget([
    'url' => Url::toRoute('get'),
    'title' => $this->context->title,
    'model' => $model,
    'colModels' => [
        ['toggleBox', 30, false, false, false],
        ['title', 200],
        ['id', 60],
        ['cat_id', 100],
        ['click', 60],
        ['published', 100],
        ['recommend', 100],
        ['ordering', 150],
        ['created_at', 200],
        ['modified_at', 200],
        ['operate', 100, false, false, false]
    ],
    'defaultSort' => ['created_at', 'desc'],
    'buttons' => [
        ['新增', 'create'],
        ['刪除', 'remove'],
        '|',
        ['保存排序', 'saveorder', '保存排序设置']
    ],
    'searchItems' => [
        ['searchWord', Html::textInput('searchWord', $searchWord, ['class'=>'form-text'])],
        ['published', '状态 ' . HtmlHelper::publishDropDownList($published), 'select'],
        ['catId', '所属分类' . EasyUiTreeDropDown::widget(['name'=>'catId', 'value'=>$catId, 'rootData'=>['id'=>'', 'name'=>'--所有文章--'], 'data'=>$categories]), 'hidden']
    ],
    'sortName' => $sortName,
    'sortOrder' => $sortOrder,
    'startPage' => $page,
    'limit' => $limit
]);
?>

