<?php
use yii\helpers\Url;
?>
<div class="heading">
    <h2 class="config">缓存管理</h2>
</div>
<div class="form">
    <h5 class="summary">请点击以下按钮执行相关操作</h5>
    <div class="form-actions">
        <div class="col-sm-1">
            <button type="button" data-action="clean" class="btn btn-primary">清除所有缓存</button>
        </div>
    </div>
</div>
<script>
    $('button').click(function () {
        var action = $(this).data('action');
        switch (action) {
            case 'clean':
                $.dialog.setState('info').confirm('确认清除所有缓存？', function () {
                    doAction(action);
                });
                break;
        }

    });
    function doAction(action) {
        $.post('<?=Url::toRoute('index');?>', {action: action})
            .done(function (response) {
                if ($.isPlainObject(response)) {
                    if (response.status == 'success') {
                        $.dialog.setState('success').messageBox(response.message);
                    } else {
                        $.dialog.setState('error').messageBox(response.message);
                    }
                } else {
                    console.log(response);
                }
            }).fail(function (response) {
                console.log(response);
            }
        );
    }
</script>