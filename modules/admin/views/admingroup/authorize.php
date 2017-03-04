<?php
/* @var $this yii\web\View */
/* @var $adminGroup app\modules\admin\models\AdminGroup */

use app\modules\admin\authorization\RoleType;
use app\modules\admin\authorization\AuthItem;
use app\assets\JqueryUIAsset;
use yii\bootstrap\Html;
use yii\helpers\Url;

JqueryUIAsset::register($this);
?>
<div class="heading">
    <h2 class="authorize">给组「<?= $adminGroup->group_name ?>」授权<span
                class="label label-info"><?= RoleType::getRoleTypeTitle($adminGroup->role_type) ?></span></h2>
</div>
<?php
echo Html::beginForm(['authorize'], 'post', ['class' => 'form-horizontal', 'id' => 'authorize-form'])
?>
<div id="tabs">
    <ul>
        <?php
        foreach ($authList as $group => $list) {
            ?>
            <li><a href="#<?= $group ?>"><?= $list['title'] ?></a></li>
            <?php
        }
        ?>
    </ul>
    <?php
    foreach ($authList as $group => $list) {
        ?>
        <div id="<?= $group ?>">
            <div class="form">
                <h5 class="group">
                            <span class="label-all-select">
                                <label for="all_select_<?= $group ?>">
                                    <input class="all_select" id="all_select_<?= $group ?>" value="1" type="checkbox"/>全选
                                </label>
                            </span>
                </h5>
                <div class="resource_list">
                    <?php
                    foreach ($list['items'] as $name => $resource) {
                        ?>
                        <span class="resource_name"><?= $resource['title'] ?></span>
                        <ul class="action_list">
                            <?php
                            foreach ($resource['actions'] as $action) {
                                ?>
                                <li>
                                    <label for="perm_<?= $name, '_', $action['action'] ?>"><input
                                                data-ref="<?= $group ?>"
                                                type="checkbox"
                                                name="perm[<?= $name ?>][<?= $action['action'] ?>]"
                                                id="perm_<?= $name, '_', $action['action'] ?>"
                                                value="1"
                                                <?php if ($action['checked']){ ?>checked="true" <?php } ?>/><?= $action['title'] ?>
                                    </label>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="clear"></div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <input type="hidden" name="gid" value="<?=$adminGroup->gid?>" />
    <input type="hidden" name="act" value="saveAuthorize" />
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">保存</button>
    <button type="button" class="btn btn-default" onclick="gotoUrl('<?= Url::toRoute('index') ?>')">取消</button>
    <label for="<?= AuthItem::FULL_PERMISSIONS ?>" id="full_label">
        <?=Html::checkBox(AuthItem::FULL_PERMISSIONS, $adminGroup->permission == AuthItem::FULL_PERMISSIONS, ['value' => 1, 'id'=>AuthItem::FULL_PERMISSIONS])?> 所有权限
    </label>
</div>
<?php
echo Html::endForm();
?>
<div class="clear"></div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#tabs").tabs().show();
        function changeCheckboxState(disabled) {
            $(':checkbox[id^=perm_]').prop('disabled', disabled);
            $('.all_select').prop('disabled', disabled);
        }

        $('#<?=AuthItem::FULL_PERMISSIONS;?>').click(function () {
            changeCheckboxState(this.checked);
        });
        <?php
        if($adminGroup->permission == AuthItem::FULL_PERMISSIONS){
        ?>
        changeCheckboxState('disabled');
        <?php
        }
        ?>
        $('.all_select').click(function () {
            var ref = this.id.substr(11);
            $(':checkbox[data-ref=' + ref + ']').prop('checked', this.checked);
        });
    });
</script>