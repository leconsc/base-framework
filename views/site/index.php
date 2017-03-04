<?php

/* @var $this yii\web\View */

use app\helpers\StringHelper;
use yii\helpers\Url;

$this->title = '首页';
?>
<div class="site-index">
    <div class="jumbotron">
        <h1>欢迎您!</h1>
        <p class="lead">您现在可以用我们网站记录您的信息了</p>
        <p><a class="btn btn-lg btn-success" href="<?=Url::toRoute('note/index')?>">开始使用</a></p>
    </div>
    <div class="body-content">
        <div class="row">
            <?php
            foreach ($recommendItems as $recommendItem) {
                ?>
                <div class="col-sm-6">
                    <h3><?=StringHelper::truncate($recommendItem['title'], 36)?></h3>
                    <p><?=$recommendItem['summary']?></p>
                    <p><a class="btn btn-default" href="<?= Url::toRoute(['content/view','id'=>$recommendItem['id']]) ?>">更多 &raquo;</a>
                    </p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>