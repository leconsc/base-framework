<?php
use app\assets\EasyUiAsset;
use yii\helpers\Url;
use app\helpers\HtmlHelper;

EasyUiAsset::register($this);
?>
<div class="heading">
    <h2 class="categories"><?= $this->context->title ?></h2>
</div>
<table id="category" class="easyui-treegrid" style="width: 99%"
       data-options="
       url: '<?= Url::toRoute('get'); ?>',
       method: 'get',
       title: '<?= $this->context->title ?>',
       animate: true,
       nowrap: false,
       singleSelect: false,
       rownumbers: true,
       fitColumns: true,
       collapsible: false,
       striped: true,
       toolbar: '#toolbar',
       pagination: true,
       pageSize: <?= $limit ?>,
       pageNumber: <?= $page ?>,
       pageList: [10,15,20],
       idField: 'id',
       treeField: 'name',
       onBeforeLoad:onBeforeLoad,
       onLoadSuccess:onLoadSuccess
       ">
    <thead data-options="frozen:true">
    <tr>
        <th data-options="field:'ck', checkbox:'true'"></th>
        <th data-options="field:'name'" width="300"><?= $model->getAttributeLabel('name') ?></th>
    </tr>
    </thead>
    <thead>
    <tr>
        <th data-options="field:'published'" width="30"><?= $model->getAttributeLabel('published') ?></th>
        <th data-options="field:'ordering'" width="40"><?= $model->getAttributeLabel('ordering') ?></th>
        <th data-options="field:'created_at'" width="60"><?= $model->getAttributeLabel('created_at') ?></th>
        <th data-options="field:'modified_at'" width="60"><?= $model->getAttributeLabel('modified_at') ?></th>
        <th data-options="field:'operate'" width="120">操作</th>
    </tr>
    </thead>
</table>
<!-- end數據區 -->

<!-- toolbar -->
<div id="toolbar">
    <div class="datagrid-toolbar">
        <?php
        echo HtmlHelper::actionButton('新增', 'create', 'add');
        echo HtmlHelper::actionButton('刪除', 'remove', 'remove');
        ?>
        <a class="datagrid-btn-separator" style="float:none"></a>
        <?php
        echo HtmlHelper::actionButton('保存排序', 'saveorder', 'save', ['authItem'=>'edit']);
        ?>
    </div>
</div>
<script>
    var $dataTable = $('#category');
    function onBeforeLoad(row, param) {
        if (!row) {
            param.id = 0;
        }
    }
    function onLoadSuccess(data) {
        <?php if($enabledEdit || $enabledRemove){?>
        $('a.action').unbind().click(function () {
            var action = $(this).data('action');
            var itemId = $(this).data('id');
            switch (action) {
            <?php if($enabledEdit){?>
                case 'publish':
                    post('<?=Url::toRoute('publish')?>', {id: itemId}, function (response) {
                        $dataTable.treegrid('reload').treegrid('unselectAll');
                    });
                    break;
                case 'orderDown':
                case 'orderUp':
                    var parent = $(this).data('parent');
                    post("<?=Url::toRoute('category/')?>/" + action, {
                        id: itemId,
                        parent: parent
                    }, function (response) {
                        $dataTable.treegrid('reload');
                    });
                    break;
            <?php
                }
                if($enabledRemove){
                ?>
                case 'remove':
                    removeItem([itemId]);
                    break;
            <?php } ?>
                default:
                    return true;
            }

        });
        <?php } ?>
    }
    <?php
    if($enabledRemove){?>
    function removeItem(ids) {
        if (ids.length == 0) {
            messageBox('请选择操作项!', '信息', 'warn');
        } else {
            remove('<?=Url::toRoute('remove')?>', {cid: ids}, function (response) {
                $dataTable.treegrid('reload').treegrid('unselectAll');
            });
        }
    }
    <?php }
    if($enabledCreate || $enabledRemove || $enabledEdit){
    ?>
    $('#toolbar .easyui-linkbutton').click(function () {
        var action = $(this).data('action'),
            selectedRows = $dataTable.datagrid('getSelections'),
            i, rowCount;
        switch (action) {
        <?php if($enabledCreate){?>
            case 'create':
                var params = {};
                if (selectedRows.length > 0) {
                    params['parent'] = selectedRows[0].id;
                }
                gotoUrl('<?=Url::toRoute('create')?>', params);
                break;
        <?php
            }
            if($enabledCreate || $enabledEdit){
            ?>
            case 'saveorder':
                $dataTable.treegrid('selectAll');
                nodes = $dataTable.treegrid('getSelections');
                var orderData = {}, orderValue, selectRow;
                for (i = 0, rowCount = selectedRows.length; i < rowCount; i++) {
                    selectRow = selectedRows[i];
                    if (typeof orderData[selectRow.parent] === 'undefined') {
                        orderData[selectRow.parent] = {};
                    }
                    orderValue = $('#ordering_' + selectRow.id).val();
                    orderData[selectRow.parent][selectRow.id] = orderValue;
                }
                $dataTable.treegrid('unselectAll');
                post('<?=Url::toRoute('save-order')?>', {orderData: orderData}, function (response) {
                    $dataTable.treegrid('reload');
                });
                break;
        <?php
            }
            if($enabledRemove){?>
            case 'remove':
                var ids = [];
                for (i = 0, rowCount = selectedRows.length; i < rowCount; i++) {
                    ids.push(selectedRows[i].id);
                }
                removeItem(ids);
                break;
        <?php }?>
        }
    });
    <?php } ?>
</script>