<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\Category */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use app\assets\EasyUiAsset;
use app\helpers\HtmlHelper;
use app\modules\admin\models\Administrator;
use app\helpers\DateTimeHelper;
use app\widgets\EasyUiTreeDropDown;

EasyUiAsset::register($this);
?>
<div class="heading">
    <h2 class="edit"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'category-form',
        'options' => ['class' => 'form-horizontal'],
        'action' => ['save']
    ]);
    echo $form->field($model, 'name')->textInput(['autofocus' => true, 'required' => 'required']);
    $config = ['data'=>$categories, 'rootData'=>['id'=>0, 'name'=>'一级分类'], 'options'=>['class'=>'form-control']];
    if($model->id){
        $config['ignore'] = $model->id;
    }
    echo $form->field($model, 'parent')->widget(EasyUiTreeDropDown::className(), $config);
    echo HtmlHelper::orderingInput($form, $model);
    echo HtmlHelper::publishRadioButtonList($form, $model);

    if ($model->id) {
        echo Html::activeHiddenInput($model, 'id');
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