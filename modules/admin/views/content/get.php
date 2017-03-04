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
$orderSerial = [];
foreach ($rows as $row) {
    $editUrl = Url::toRoute([Operation::O_EDIT, 'id'=>$row['id']]);
    $jsonData['rows'][$i]['id'] = $row['id'];
    $row['toggleBox'] = HtmlHelper::idBox($i, $row['id']);
    $row['title'] = HtmlHelper::link(ResponseHelper::renderColor($row['title'],$row['title_color']), $editUrl, Operation::O_EDIT);
    $row['published'] = HtmlHelper::publishActionLink($i, $row['published']);
    $row['recommend'] = HtmlHelper::stateActionLink($i, $row['recommend'], ['1'=>'推荐', '0'=>'不推荐'], 'recommend');
    if($sortName === 'cat_id' && isset($stats[$row['cat_id']])){
        if(!isset($orderSerial[$row['cat_id']])){
            $orderSerial[$row['cat_id']] = 0;
        }else{
            $orderSerial[$row['cat_id']]++;
        }
        $serial = $orderSerial[$row['cat_id']];
        $count = $stats[$row['cat_id']];
    }else{
        $serial = $i;
        $count = $n;
    }
    $row['ordering'] = HtmlHelper::orderButton($serial, $count, $row['ordering']);
    if(isset($categories[$row['cat_id']])){
        $row['cat_id'] = $categories[$row['cat_id']];
    }
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