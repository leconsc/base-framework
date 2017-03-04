<?php
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\helpers\HtmlPurifier;
use app\helpers\StringHelper;
?>
<div class="heading">
    <h2 class="article"><?=StringHelper::truncate($page['title'], 60)?></h2>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">

        </h3>
    </div>
    <div class="panel-body">
        <?=HtmlPurifier::process($page['content'])?>
    </div>
</div>
<div class="row">
    <div class="col-sm-1 col-sm-push-11">
        <?php
        echo Html::buttonInput('返回', ['class' => 'btn btn-default', 'name' => 'cancel-button', 'onclick' => 'gotoUrl("' . Url::toRoute('site/index') . '")'])
        ?>
    </div>
</div>