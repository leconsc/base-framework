<?php
use yii\helpers\Url;
use app\helpers\StringHelper;
use yii\widgets\LinkPager;
use yii\bootstrap\Html;
use app\helpers\HtmlHelper;
?>
<div>
    <div class="col-md-3 col-sm-4">
        <div class="list-group">
            <a class="list-group-item<?= (is_null($catId) ? ' active' : '') ?>"
               href="<?= Url::toRoute('content/index') ?>"><i class="glyphicon glyphicon-chevron-right"></i>全部资讯</a>
            <a class="list-group-item<?= ($catId === 0 ? ' active' : '') ?>"
               href="<?= Url::toRoute(['content/index', 'cls_id' => 0]) ?>"><i
                    class="glyphicon glyphicon-chevron-right"></i>默认分类</a>
            <?php
            foreach ($categories as $id => $name){
                ?>
                <a class="list-group-item<?= ($catId === $id ? ' active' : '') ?>"
                   href="<?= Url::toRoute(['content/index', 'cat_id' => $id]) ?>"><i
                        class="glyphicon glyphicon-chevron-right"></i><?=StringHelper::truncate($name, 20)?></a>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="col-md-9 col-sm-8">
        <div class="col-md-6 col-sm-7">
            <div class="heading">
                <h2 class="note"><?= $this->context->title ?></h2>
            </div>
        </div>
        <div class="col-md-6 col-sm-5">
            <div class='search-box'>
                <form class='search-form' method="get" action="<?=Url::toRoute('index')?>">
                    <input class='form-control' placeholder='搜索文字' type='text' name="k" value="<?=Html::encode($searchWorld)?>">
                    <button class='btn btn-link search-btn'>
                        <i class='glyphicon glyphicon-search'></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-12 list content-list">
            <h5>资讯动态列表</h5>
            <?php
            if (!empty($contents)) {
                ?>
                <ul class="list-items">
                    <?php
                    foreach ($contents as $content) {
                        ?>
                        <li class="list-item" data-id="<?= $content['id'] ?>">
                            <a href="<?= Url::toRoute(['view', 'id' => $content['id']]) ?>" data-toggle="tooltip"
                               title="浏览 <?= $content['title'] ?>"><?= HtmlHelper::renderColor(StringHelper::truncate($content['title'], 50), $content['title_color']) ?></a>

                            <div class="icon-button">
                                <a href="<?= Url::toRoute(['view', 'id' => $content['id']]) ?>" data-toggle="tooltip" title="浏览">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            }
            echo LinkPager::widget([
                'pagination' => $pagination,
            ]);
            ?>
        </div>
    </div>
</div>
<script>
    $('.list-items>.list-item').mouseover(function(){
        $(this).addClass('hover');
    }).mouseout(function(){
        $(this).removeClass('hover');
    });
</script>