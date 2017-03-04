<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\FrontendAsset;
use app\assets\DialogAsset;
use app\assets\HelperAsset;

FrontendAsset::register($this);
DialogAsset::register($this);
HelperAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $isGuest = Yii::$app->user->isGuest;
    $name = '';
    if(!$isGuest){
        $name = Yii::$app->user->identity->name;
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => '首页', 'url' => ['/site/index']],
            ['label' => '资讯动态', 'url' => ['/content/index']],
            ['label' => '关于我们', 'url' => ['/page/about']],
            ['label' => '登录', 'url' => ['/site/login'], 'visible'=>$isGuest],
            ['label' => '注册', 'url' => ['/site/register'], 'visible'=>$isGuest],
            [
              'label' => '我('.$name.')',
              'items' => [
                  ['label'=>'我的信息', 'url'=>['member/index']],
                  ['label'=>'修改密码', 'url'=>['member/password']],
                  '<li class="divider"></li>',
                  ['label'=>'退出', 'url' => ['/site/logout']]
              ],
              'visible'=>!$isGuest,
              'linkOptions'=>['class'=>'btn btn-link']
            ]
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-right"></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
