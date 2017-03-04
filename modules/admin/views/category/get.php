<?php
use app\helpers\HtmlHelper;
use app\helpers\ResponseHelper;
use app\modules\admin\models\Administrator;
use yii\helpers\Url;

$formatter = \Yii::$app->formatter;
if($parent == 0){
    $treeData = array('total'=>$total, 'rows'=>[]);
}else{
    $treeData = array();
}
$htmlOptions = array();
$htmlOptions['noAction'] = true;
if (is_array($rows) && count($rows)) {
    $n = count($rows);
    $i = 0;
    foreach ($rows as $row) {
        $htmlOptions['data-id'] = $row['id'];
        $editUrl = Url::toRoute(['edit', 'id'=>$row['id']]);
        $row['name'] = HtmlHelper::link($row['name'], $editUrl, 'edit', $htmlOptions);
        unset($htmlOptions['url']);
        $row['published'] = HtmlHelper::stateActionLink($i, $row['published'], null, 'publish', $htmlOptions);
        $row['created_at'] = $formatter->asDate($row['created_at']). HtmlHelper::label('(' . Administrator::getName($row['created_by']) . ')');
        if (empty($row['modified_at'])) {
            $row['modified_at'] = HtmlHelper::label('未有修改');
        } else {
            $row['modified_at'] = $formatter->asDate($row['modified_at']) . HtmlHelper::label('(' . Administrator::getName($row['modified_by']) . ')');
        }
        $operate = join(' ', array(
                HtmlHelper::iconLink('修改', $editUrl, 'edit', $htmlOptions),
                HtmlHelper::iconActionLink($i, 'remove', '删除', $htmlOptions)
            )
        );
        $treeItem = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'parent' => $row['parent'],
            'published' => $row['published'],
            'ordering' => HtmlHelper::orderButton($i, $n, $row['ordering'], ['noAction'=>true,'data-parent'=>$row['parent'], 'data-id'=>$row['id'], 'id'=>'ordering_'.$row['id']]),
            'created_at' => $row['created_at'],
            'modified_at' => $row['modified_at'],
            'operate' => $operate,
        );
        if($row['sub_count']){
            $treeItem['state'] = 'closed';
        }
        if($parent > 0){
            $treeData[] = $treeItem;
        }else{
            $treeData['rows'][] = $treeItem;
        }
        $i++;
    }
}
ResponseHelper::sendJson($treeData);