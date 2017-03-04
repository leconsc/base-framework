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
$jsonData['rows'] = [];
$i = 0;
$n = count($rows);
foreach ($rows as $row) {
    $editUrl = Url::toRoute([Operation::O_EDIT, 'id'=>$row['id']]);
    $jsonData['rows'][$i]['id'] = $row['id'];
    $row['toggleBox'] = HtmlHelper::idBox($i, $row['id']);
    $row['title'] = HtmlHelper::link($row['title'], $editUrl, Operation::O_EDIT);
    $row['published'] = HtmlHelper::publishActionLink($i, $row['published']);
    $row['created_at'] = sprintf('%s(%s)', DateTimeHelper::format($row['created_at']), HtmlHelper::label(Administrator::getName($row['created_by'])));
    if (empty($row['modified_at'])) {
        $row['modified_at'] = HtmlHelper::label('未有修改');
    }else{
        $row['modified_at'] = sprintf('%s(%s)', DateTimeHelper::format($row['modified_at']), HtmlHelper::label(Administrator::getName($row['modified_by'])));
    }
    $row['operate'] = join(' ', array(
            HtmlHelper::iconLink('修改', $editUrl, Operation::O_EDIT),
            HtmlHelper::iconActionLink($i, Operation::O_REMOVE, '删除')
        )
    );
    $jsonData['rows'][$i]['cell'] = $row;
    $i++;
}
ResponseHelper::sendJson($jsonData);