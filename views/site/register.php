<?php

/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\Member */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use \yii\captcha\Captcha;

$this->title = '用户注册';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-register">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>请填写下列字段进行注册：</p>

    <?php
    $form = ActiveForm::begin([
        'id' => 'register-form',
        'options' => ['class' => 'form-horizontal'],
        'validateOnChange' => true,
        'validateOnBlur' => true,
    ]);

    echo $form->field($model, 'email')->textInput(['autofocus' => true, 'type'=>'email']);
    echo $form->field($model, 'password')->passwordInput();
    echo $form->field($model, 'password_repeat')->passwordInput();
    echo $form->field($model, 'mobile')->textInput(['type'=>'tel']);
    echo $form->field($model, 'name')->textInput();
    echo $form->field($model, 'verify_code')->widget(Captcha::className(), [
        'template' => "<div class=\"col-sm-4\">{input}</div>\n<div class=\"col-sm-8\">{image}</div>",
    ]);
    echo $form->field($model, 'agree', ['inputClass'=>'col-sm-offset-1 col-sm-3', 'errorClass'=>'col-sm-8'])->checkbox([
        'value'=>'1',
        'label' => '我已阅读并同意《'.Html::a('告知科技注册协议', ['about/policy']). '》',
    ])
    ?>
    <div class="form-group">
        <div class="col-sm-offset-1 col-sm-11">
            <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'register-button']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
