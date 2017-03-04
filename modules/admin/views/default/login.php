<?php
/* @var $form app\widgets\ActiveForm */
/* @var $model app\modules\admin\models\LoginForm */

use app\assets\BackendAsset;
use app\widgets\ActiveForm;

$this->title = '登录';
?>
<div class="container login-form-wrap">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4 col-sm-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <strong>管理员登录</strong>
                </div>
                <div class="panel-body">
                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'messageDisplayMode' => ActiveForm::MESSAGE_DISPLAY_MODE_POPUP,
                        'options' => ['class' => 'form-horizontal']
                    ]);
                    ?>
                    <fieldset>
                        <div class="row">
                            <div class="center-block">
                                <img class="profile-img" src="<?= BackendAsset::getImageUrl('photo.jpg') ?>"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-10 col-md-offset-1">
                                <?php
                                $options = ['template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>{input}{error}</div>'];
                                echo $form->field($model, 'username', $options)->textInput(['autofocus' => true, 'required' => 'required', 'class' => 'form-control', 'placeholder' => $model->getAttributeLabel('username')]);
                                ?>
                                <?php
                                $options = ['template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>{input}{error}</div>'];
                                echo $form->field($model, 'password', $options)->passwordInput(['required' => 'required', 'class' => 'form-control', 'placeholder' => $model->getAttributeLabel('password')]);
                                ?>
                                <input type="submit" class="btn btn-lg btn-success btn-block" value="登 录">
                            </div>
                        </div>
                    </fieldset>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>