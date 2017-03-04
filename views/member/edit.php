<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $member app\models\Member */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use app\helpers\DateTimeHelper;

?>
<div class="heading">
    <h2 class="user"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'member-form',
        'options' => ['class' => 'form-horizontal'],
        'action' => ['save']
    ]);
    echo $form->field($member, 'email')->textInput(['autofocus' => true]);
    echo $form->field($member, 'name')->textInput();
    echo $form->field($member, 'mobile')->textInput();
    ?>
    <div class="form-group">
        <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('registration_time') ?>：</label>
        <span class="col-sm-11 control-text label-flag"><?= DateTimeHelper::format($member->registration_time) ?></span>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('registration_ip') ?>：</label>
        <span class="col-sm-11 control-text label-flag"><?= $member->registration_ip ?></span>
    </div>
    <?php
    if ($member->modified_at) {
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('modified_at') ?>：</label>
            <span class="col-sm-11 control-text label-flag"><?= DateTimeHelper::format($member->modified_at) ?></span>
        </div>
        <?php
    }
    if ($member->last_login_time) {
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('last_login_time') ?>：</label>
            <span class="col-sm-11 control-text label-flag"><?= DateTimeHelper::format($member->last_login_time) ?></span>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('last_login_ip') ?>：</label>
            <span class="col-sm-11 control-text label-flag"><?= $member->last_login_ip ?></span>
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