<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\modules\admin\models\AdminGroup */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use app\helpers\HtmlHelper;
use app\modules\admin\models\Administrator;
use app\helpers\DateTimeHelper;
use app\modules\admin\authorization\RoleType;
?>
<div class="heading">
    <h2 class="user-group-edit"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'admin-group-form',
        'options' => ['class' => 'form-horizontal'],
        'action' => ['save']
    ]);
    echo $form->field($model, 'group_name')->textInput(['autofocus' => true]);
    if (!$model->gid) {
        echo $form->field($model, 'role_type')->dropDownList(RoleType::getRoleTypes(), ['prompt'=>'--选择角色类型--']);
    }
    if(isset($canFreeze) && $canFreeze){
        echo HtmlHelper::stateRadioButtonList($form, $model);
    }
    echo $form->field($model, 'description', ['inputClass' => 'col-sm-5', 'errorClass' => 'col-sm-6'])->textarea();
    if ($model->gid) {
        echo Html::activeHiddenInput($model, 'gid');
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('created_at'); ?></label>
            <div class="col-sm-3 label-flag"><?= DateTimeHelper::format($model->created_at) ?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('created_by'); ?></label>
            <div class="col-sm-3 label-flag"><?= Administrator::getName($model->created_by) ?></div>
        </div>
        <?php
    }
    if ($model->modified_at) {
        ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('modified_at'); ?></label>
            <div class="col-sm-3 label-flag"><?= DateTimeHelper::format($model->modified_at) ?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?= $model->getAttributeLabel('modified_by'); ?></label>
            <div class="col-sm-3 label-flag"><?= Administrator::getName($model->modified_by) ?></div>
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