<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;
use app\assets\BackendAsset;
use app\modules\admin\components\MenuList;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;
use app\assets\HelperAsset;

BackendAsset::register($this);
if (!Yii::$app->user->isGuest) {
    HelperAsset::register($this);
}
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    if(!$isGuest) {
        $items = [];
        $menus = MenuList::getMenus();
        foreach ($menus as $menu) {
            if (isset($menu[MenuList::SUBMENUS])) {
                $subItems = [];
                foreach ($menu[MenuList::SUBMENUS] as $subMenu) {
                    if(is_array($subMenu)) {
                        $subItems[] = [
                            'label' => $subMenu[MenuList::TITLE],
                            'url' => $subMenu[MenuList::LINK]
                        ];
                    }else if(is_string($subMenu) && $subMenu === '-'){
                        $subItems[] = '<li class="divider"></li>';
                    }
                }
                $items[] = [
                    'label' => $menu[MenuList::TITLE],
                    'items' => $subItems
                ];
            }else{
                $items[] = [
                    'label' => $menu[MenuList::TITLE],
                    'url' => $menu[MenuList::LINK]
                ];
            }
        }
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => $items,
        ]);
        ?>
        <div class="nav navbar-nav navbar-right">
            <span class="welcome">
                <img src="<?=BackendAsset::getImageUrl('icon-16-back-user.png'); ?>" align="absmiddle">
                <span class="label label-info">欢迎, <?=Yii::$app->user->identity->username?></span>
            </span>
            <a href="<?=Url::toRoute('default/password') ?>" class="btn btn-link">修改密码</a>
            <a href="<?=Url::toRoute('default/logout') ?>" class="btn btn-link">退出</a>
        </div>
<?php
    }
    NavBar::end();
    ?>
</div>
<?php
if (!Yii::$app->user->isGuest && isset($this->params['breadcrumbs']) && count($this->params['breadcrumbs'])) {
    ?>
    <div id="breadcrumbs">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </div>
<?php } ?>
<div class="container-fluid">
    <?=$content?>
</div>
<footer class="footer">
    <div class="container">
        <p class="pull-right"></p>
    </div>
</footer>
<?php
if (Yii::$app->session->hasFlash('success')) {
    $message = Yii::$app->session->getFlash('success');
    $title = '信息';
    $state = 'info';
} elseif (Yii::$app->session->hasFlash('error')) {
    $message = Yii::$app->session->getFlash('error');
    $title = '错误';
    $state = 'error';
}
if (!empty($message)) {
    echo "<script type=\"text/javascript\">$(function(){messageBox('$message', '$title', '$state')});</script>";
}
$this->endBody();
?>
</body>
</html>
<?php $this->endPage() ?>