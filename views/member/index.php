<?php
/**
 * @var $member \app\models\Member
 */
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\DateTimeHelper;
?>
<div class="heading">
    <h2 class="user">我的信息</h2>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"></h3>
    </div>
    <div class="panel-body">
        <div class="form form-horizontal">
            <div class="form-group">
                <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('email')?>：</label>
                <span class="col-sm-11 control-text"><?=$member->email?></span>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('name')?>：</label>
                <span class="col-sm-11 control-text"><?=$member->name?></span>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('mobile')?>：</label>
                <span class="col-sm-11 control-text"><?=$member->mobile?></span>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('registration_time')?>：</label>
                <span class="col-sm-11 control-text label-flag"><?=DateTimeHelper::format($member->registration_time)?></span>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('registration_ip')?>：</label>
                <span class="col-sm-11 control-text label-flag"><?=$member->registration_ip?></span>
            </div>
            <?php
            if($member->modified_at) {
                ?>
                <div class="form-group">
                    <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('modified_at') ?>：</label>
                    <span class="col-sm-11 control-text label-flag"><?=DateTimeHelper::format($member->modified_at)?></span>
                </div>
                <?php
            }
            if($member->last_login_time) {
                ?>
                <div class="form-group">
                    <label class="col-sm-1 control-label"><?= $member->getAttributeLabel('last_login_time') ?>：</label>
                    <span class="col-sm-11 control-text label-flag"><?=DateTimeHelper::format($member->last_login_time)?></span>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label"><?=$member->getAttributeLabel('last_login_ip')?>：</label>
                    <span class="col-sm-11 control-text label-flag"><?=$member->last_login_ip?></span>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-2 col-sm-push-10 action-button">
        <?php
        echo Html::buttonInput('返回', ['class' => 'btn btn-default', 'name' => 'cancel-button', 'onclick' => 'gotoUrl("' . Url::toRoute('site/index') . '")']);
        echo Html::buttonInput('编辑', ['class' => 'btn btn-primary', 'name' => 'edit-button', 'onclick' => 'gotoUrl("' . Url::toRoute('edit') . '")']);
        ?>
    </div>
</div>