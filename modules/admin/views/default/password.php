<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;

?>
<div class="heading">
    <h2 class="security"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
    ]);
    echo $form->field($model, 'password_original')->passwordInput(['autofocus' => true]);
    echo $form->field($model, 'password')->passwordInput();
    echo $form->field($model, 'password_repeat')->passwordInput()
    ?>
    <div class="form-actions">
        <?php
           echo Html::submitButton('保存', ['class' => 'btn btn-primary', 'name' => 'save-button']);
           echo Html::buttonInput('取消', ['class' => 'btn btn-default', 'name' => 'cancel-button', 'onclick'=>'gotoUrl("'.Url::toRoute('index').'")'])
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<div class="clear"></div>