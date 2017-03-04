<?php
use app\helpers\HtmlHelper;
use app\modules\admin\authorization\Operation;
use app\helpers\ResponseHelper;
use app\helpers\DateTimeHelper;
use app\modules\admin\models\Administrator;
use yii\helpers\Url;
use app\modules\admin\authorization\RoleType;

$jsonData = array();
$jsonData['page'] = $page;
$jsonData['total'] = $total;
$jsonData['rows'] = array();
$i = 0;
foreach ($rows as $row) {
    $jsonData['rows'][$i]['id'] = $row['gid'];
    $editUrl = Url::toRoute([Operation::O_EDIT, 'id'=>$row['gid']]);

    $htmlOptions = array();
    if ($row['is_core']) {
        $htmlOptions['disabled'] = 'disabled';
    } else {
        $row['group_name'] = HtmlHelper::link($row['group_name'], $editUrl, Operation::O_EDIT);
        $row['operate'] = join(' ', array(
                HtmlHelper::iconLink('授权', Url::toRoute([Operation::O_AUTHORIZE, 'id'=>$row['gid']]), Operation::O_AUTHORIZE),
                HtmlHelper::iconLink('修改', $editUrl, Operation::O_EDIT),
                HtmlHelper::iconActionLink($i, Operation::O_REMOVE, '删除')
            )
        );
    }
    $row['toggleBox'] = HtmlHelper::idBox($i, $row['gid'], 'cid', $htmlOptions);
    $row['role_type'] = RoleType::getRoleTypeTitle($row['role_type']);
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