<?php
use app\helpers\HtmlHelper;
use app\modules\admin\authorization\Operation;
use app\helpers\ResponseHelper;
use app\helpers\DateTimeHelper;
use yii\helpers\Url;

$jsonData = array();
$jsonData['page'] = $page;
$jsonData['total'] = $total;
$jsonData['rows'] = array();
$i = 0;
foreach ($rows as $row) {
    $jsonData['rows'][$i]['id'] = $row['uid'];
    $editUrl = Url::toRoute([Operation::O_EDIT, 'id' => $row['uid']]);

    $row['toggleBox'] = HtmlHelper::idBox($i, $row['uid']);
    $row['email'] = HtmlHelper::link($row['email'], $editUrl, Operation::O_EDIT);
    $row['operate'] = join(' ', array(
            HtmlHelper::iconLink('修改', $editUrl, Operation::O_EDIT),
            HtmlHelper::iconActionLink($i, Operation::O_REMOVE, '删除')
        )
    );
    $disabled = false;
    if (isset($canFreeze) && !$canFreeze) {
        $disabled = true;
    }
    $row['freeze'] = HtmlHelper::stateActionLink($i, !$row['freeze'], ['close' => '冻结', 'open' => '活动'], Operation::O_FREEZE, [], ['disabled' => $disabled]);
    $row['last_login_time'] = DateTimeHelper::format($row['last_login_time']) . HtmlHelper::label('(IP:' . $row['last_login_ip'] . ')');
    $row['registration_time'] = DateTimeHelper::format($row['registration_time']) . HtmlHelper::label('(IP:' . $row['registration_ip'] . ')');
    if (empty($row['modified_at'])) {
        $row['modified_at'] = HtmlHelper::label('未有修改');
    } else {
        $row['modified_at'] = DateTimeHelper::format($row['modified_at']);
    }
    $jsonData['rows'][$i]['cell'] = $row;
    $i++;
}
ResponseHelper::sendJson($jsonData);