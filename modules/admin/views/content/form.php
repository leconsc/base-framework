<?php
/* @var $this yii\web\View */
/* @var $form app\widgets\ActiveForm */
/* @var $model app\models\Content */

use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use app\helpers\HtmlHelper;
use app\modules\admin\models\Administrator;
use app\helpers\DateTimeHelper;
use app\widgets\KindEditorWidget;
use app\widgets\ColorField;
use app\widgets\EasyUiTreeDropDown;
?>
<div class="heading">
    <h2 class="<?= ($model->id ? 'content-edit' : 'content-add') ?>"><?= $this->context->title ?></h2>
</div>
<div class="form">
    <h5 class="summary">详细信息(带*为必填项)</h5>
    <?php
    $form = ActiveForm::begin([
        'id' => 'content-form',
        'options' => ['class' => 'form-horizontal'],
        'action' => ['save']
    ]);
    echo $form->field($model, 'title', ['inputClass' => 'col-sm-6', 'errorClass' => 'col-sm-5'])->widget(ColorField::className(), ['options'=>['autofocus' => true]]);
    echo $form->field($model, 'cat_id')->widget(EasyUiTreeDropDown::className(), ['data'=>$categories, 'hasRoot'=>false, 'options'=>['class'=>'form-control']]);
    echo $form->field($model, 'summary', ['inputClass' => 'col-sm-8', 'errorClass' => 'col-sm-3'])->textarea();
    echo $form->field($model, 'content', ['inputClass' => 'col-sm-11', 'errorClass' => 'col-sm-11 col-sm-offset-1'])->textarea();
    echo $form->field($model, 'click', ['inputClass' => 'col-sm-1', 'errorClass' => 'col-sm-10'])->input('number');
    echo HtmlHelper::publishRadioButtonList($form, $model);
    echo HtmlHelper::stateRadioButtonList($form, $model, 'recommend', ['1'=>'推荐', '0'=>'不推荐']);
    echo HtmlHelper::orderingInput($form, $model);
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
<?php
KindEditorWidget::widget([
    'id' => Html::getInputId($model, 'content'),
    'fileManagerUrl' => Url::toRoute('imagelist'),
    'uploadUrl' => Url::toRoute('upload'),
    'category' => 'content',
    'createThumb' => true
]);
?>
<script>
    var $form = $('#<?=$form->id?>');
    $form.on('beforeValidate', function (event, messages, deferreds) {
        editor.sync();
        return true;
    });
</script>
