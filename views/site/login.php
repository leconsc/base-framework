<?php

/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use app\widgets\ActiveForm;

$this->title = '登录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>请填写以下信息进行登录验证:</p>

    <?php
    $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
    ]);
    echo $form->field($model, 'email')->textInput(['autofocus' => true, 'type'=>'email']);
    echo $form->field($model, 'password')->passwordInput();
    echo $form->field($model, 'rememberMe', ['inputClass'=>'col-sm-offset-1 col-sm-3', 'errorClass'=>'col-sm-8'])->checkbox()
    ?>
    <div class="form-group">
        <div class="col-sm-offset-1 col-sm-11">
            <?= Html::submitButton('登录', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
