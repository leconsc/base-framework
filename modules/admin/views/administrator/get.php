<?php
use app\helpers\HtmlHelper;
use app\modules\admin\authorization\Operation;
use app\helpers\ResponseHelper;
use app\helpers\DateTimeHelper;
use app\modules\admin\models\Administrator;
use yii\helpers\Url;

$jsonData = array();
$jsonData['page'] = $page;
$jsonData['total'] = $total;
$jsonData['rows'] = array();
$i = 0;
foreach ($rows as $row) {
    $jsonData['rows'][$i]['id'] = $row['uid'];
    $editUrl = Url::toRoute([Operation::O_EDIT, 'id'=>$row['uid']]);

    $htmlOptions = array();
    if ($row['is_core']) {
        $htmlOptions['disabled'] = 'disabled';
    } else {
        $row['username'] = HtmlHelper::link($row['username'], $editUrl, Operation::O_EDIT);
        $row['operate'] = join(' ', array(
                HtmlHelper::iconLink('修改', $editUrl, Operation::O_EDIT),
                HtmlHelper::iconActionLink($i, Operation::O_REMOVE, '删除')
            )
        );
    }
    $row['toggleBox'] = HtmlHelper::idBox($i, $row['uid'], 'cid', $htmlOptions);
    if($row['group_freeze']){
        $row['group_name'] = sprintf('%s<span class="label-flag label-flag-red">(%s)</span>', $row['group_name'], '冻结');
    }
    $row['gid'] = $row['group_name'];
    $disabled = (boolean)$row['is_core'];
    if(isset($canFreeze) && !$canFreeze){
        $disabled = true;
    }
    $row['freeze'] = HtmlHelper::stateActionLink($i, !$row['freeze'], ['close' => '冻结', 'open' => '活动'], Operation::O_FREEZE, [], ['disabled'=>$disabled]);
    $row['created_at'] = DateTimeHelper::format($row['created_at']) . HtmlHelper::label('('.Administrator::getName($row['created_by']).')');
    if (empty($row['modified_at'])) {
        $row['modified_at'] = HtmlHelper::label('未有修改');
    } else {
        $row['modified_at'] = DateTimeHelper::format($row['modified_at']). HtmlHelper::label('('.Administrator::getName($row['modified_by'].')'));
    }
    $jsonData['rows'][$i]['cell'] = $row;
    $i++;
}
ResponseHelper::sendJson($jsonData);