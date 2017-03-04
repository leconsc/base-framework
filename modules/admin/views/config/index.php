<?php
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\assets\JqueryUIAsset;
use app\components\SystemConfig;
use app\helpers\Config;
use app\assets\BackendAsset;

JqueryUIAsset::register($this);
?>
<div class="heading">
    <h2 class="config">系统配置</h2>
</div>
<?php
if (count($configItems)) {
    SystemConfig::setTipIcon(BackendAsset::getImageUrl('icon-16-notice-note.png'));
    $htmlOptions = array();
    if (isset($canModify) && !$canModify) {
        $htmlOptions['disabled'] = 'disabled';
    }
    echo Html::beginForm(['index'], 'post', ['class' => 'form-horizontal', 'id' => 'config-form'])
    ?>
    <div id="tabs">
        <ul>
            <?php
            foreach ($configItems as $groupName => $group) {
                if (!empty($group[SystemConfig::ITEMS])) {
                    ?>
                    <li><a href="#<?=$groupName?>"><?=$group[SystemConfig::ITEM_TITLE]?></a></li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
        foreach ($configItems as $groupName => $group) {
            if (!empty($group[SystemConfig::ITEMS])) {
                ?>
                <div id="<?=$groupName?>">
                    <div class="form">
                        <?php
                        if (!empty($group[SystemConfig::ITEM_DESCRIPTION])) {
                            ?>
                        <div class="alert alert-success" role="alert"><?=$group[SystemConfig::ITEM_DESCRIPTION]?></div>
                            <?php
                        }
                        foreach ($group[SystemConfig::ITEMS] as $item) {
                            ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?=$item[SystemConfig::ITEM_TITLE]?></label>
                                <div class="col-sm-10">
                                    <?=SystemConfig::render($item, Config::get($item[SystemConfig::ITEM_NAME]))?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <div class="form-actions">
        <?php if (isset($canModify) && $canModify): ?>
            <button type="submit" class="btn btn-primary">保存</button>
        <?php endif?>
        <button type="button" class="btn btn-default"
                onclick="gotoUrl('<?=Url::toRoute('default/index')?>')">取消
        </button>
    </div>
    <?php
    echo Html::endForm();
}
?>
<div class="clear"></div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#tabs").tabs().show();
    });
</script>