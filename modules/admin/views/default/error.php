<?php
use yii\helpers\Html;

/** @var Exception $exception */

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($exception->getMessage())) ?>
    </div>

    <p>
        当服务器处理您的请求时出现以上错误。
    </p>
    <p>
        如果您认为这是一个服务器方面的错误，请联系我们，谢谢！
    </p>

</div>
