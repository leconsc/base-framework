<?php
use app\widgets\FlexiGrid;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\HtmlHelper;
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
        ['title', 300],
        ['alias', 100],
        ['id', 60],
        ['published', 100],
        ['created_at', 200],
        ['modified_at', 200],
        ['operate', 100, false, false, false]
    ],
    'defaultSort' => ['created_at', 'desc'],
    'buttons' => [
        ['新增', 'create'],
        ['刪除', 'remove']
    ],
    'searchItems' => [
        ['searchWord', Html::textInput('searchWord', $searchWord)],
        ['published', '状态 ' . HtmlHelper::publishDropDownList($published), 'select']
    ],
    'sortName' => $sortName,
    'sortOrder' => $sortOrder,
    'startPage' => $page,
    'limit' => $limit
]);
?>