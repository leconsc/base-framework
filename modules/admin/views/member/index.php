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
        ['email', 200, false],
        ['name', 100, false],
        ['uid', 60],
        ['mobile', 120, false],
        ['freeze', 80],
        ['last_login_time', 200],
        ['registration_time', 200],
        ['modified_at', 200],
        ['operate', 100, false, false, false]
    ],
    'defaultSort' => ['registration_time', 'desc'],
    'buttons' => [
        ['刪除', 'remove']
    ],
    'searchItems' => [
        ['searchField', Html::dropDownList('searchField', $searchField, [
            'uid' => $model->getAttributeLabel('uid'),
            'email' => $model->getAttributeLabel('email'),
            'name' => $model->getAttributeLabel('name'),
            'mobile' => $model->getAttributeLabel('mobile'),
        ]), 'select'],
        ['searchWord', Html::textInput('searchWord', $searchWord, ['class' => 'form-text'])],
        ['freeze', '状态 ' . HtmlHelper::publishDropDownList($freeze), 'select']
    ],
    'sortName' => $sortName,
    'sortOrder' => $sortOrder,
    'startPage' => $page,
    'limit' => $limit
]);
?>