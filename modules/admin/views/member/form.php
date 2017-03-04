<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\Member */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use app\helpers\HtmlHelper;
use app\helpers\DateTimeHelper;

?>
<div class="heading">
    <h2 class="user-edit"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'member-form',
        'options' => ['class' => 'form-horizontal'],
        'action' => ['save']
    ]);
    echo $form->field($model, 'email')->textInput(['autofocus' => true]);
    echo $form->field($model, 'password')->passwordInput();
    echo $form->field($model, 'password_repeat')->passwordInput();
    echo $form->field($model, 'name')->textInput();
    echo $form->field($model, 'mobile')->textInput();
    if (isset($canFreeze) && $canFreeze) {
        echo HtmlHelper::stateRadioButtonList($form, $model);
    }
    if ($model->uid) {
        echo Html::activeHiddenInput($model, 'uid');
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('registration_time'); ?></label>
            <div class="col-sm-3 label-flag"><?= DateTimeHelper::format($model->registration_time) ?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('registration_ip'); ?></label>
            <div class="col-sm-3 label-flag"><?= $model->registration_ip ?></div>
        </div>
        <?php
    }
    if ($model->modified_at) {
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('modified_at'); ?></label>
            <div class="col-sm-3 label-flag"><?= DateTimeHelper::format($model->modified_at) ?></div>
        </div>
        <?php
    }
    if ($model->last_login_time) {
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('last_login_time'); ?></label>
            <div class="col-sm-3 label-flag"><?= DateTimeHelper::format($model->last_login_time) ?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('last_login_ip'); ?></label>
            <div class="col-sm-3 label-flag"><?= $model->last_login_ip ?></div>
        </div>
        <?php
    }
    ?>
    <div class="form-actions">
        <?php
        echo Html::submitButton('保存', ['class' => 'btn btn-primary', 'name' => 'save-button']);
        echo Html::buttonInput('取消', ['class' => 'btn btn-default', 'name' => 'cancel-button', 'onclick' => 'gotoUrl("' . Url::toRoute('index') . '")'])
        ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="clear"></div>