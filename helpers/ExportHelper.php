<?php
/**
 * 导出功能组件助手
 *
 * @author chenbin
 * @version $Id:ExportHelper.php, 1.0 2014-10-04 22:52+100 chenbin$
 * @package: WeGames
 * @since 2014-10-04 22:52
 * @copyright 2014(C)Copyright By CQTimes, All rights Reserved.
 */

class ExportHelper
{
    /**
     * 输出导出的Header部分.
     *
     * @param $filename
     */
    public static function header($filename)
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");

        //防止导出中文名称出现乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        header('Content-Type:application/octet-stream');
        if (preg_match("/msie/i", $ua)) {
            $encoded_filename = urlencode($filename);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition:attachment;filename="' . $encoded_filename . '.csv"');
        } else {
            header('Content-Disposition:attachment;filename="' . $filename . '.csv"');
        }
        header("Content-Transfer-Encoding: binary");
    }

    /**
     * 输出title项.
     *
     * @param array $items
     * @param array $client
     * @return string
     */
    public static function writeTitle(array $items, $client=array(),$return = false)
    {
        $result = self::_iconv($items,$client);
        if ($return) {
            return $result;
        } else {
            echo $result;
        }

    }

    /**
     * 写导出内容主体.
     *
     * @param array $contentList
     * @param array $client
     * @param null $definitionSort
     * @param bool $return
     * @return string
     */
    public static function writeContent(array $contentList,$client=array(), $definitionSort = null, $return = false)
    {
        if ($return) {
            $content = '';
        }
        foreach ($contentList as $contentItems) {
            if ($definitionSort) {
                $sortFields = preg_split('/[,\s]+/', $definitionSort);
                $items = array();
                foreach ($sortFields as $sortField) {
                    if (array_key_exists($sortField, $contentItems)) {
                        $items[$sortField] = $contentItems[$sortField];
                    }
                }
            } else {
                $items = $contentItems;
            }
            foreach($items as $field => &$content){
                $content = trim(preg_replace('[\r\n]', '', $content));
                $content = str_replace(',', '，', $content);
            }
            $items = array_values($items);
            $itemContent = self::_iconv($items,$client);
            if ($return) {
                $content .= $itemContent;
            } else {
                echo $itemContent;
            }
        }
        if ($return) {
            return $content;
        } else {
            ob_flush();
            flush();
        }
    }

    /**
     * 處理語言
     *
     * @param array $items 內容
     * @param array client 客戶端信息
     * @return string
     */
    public static function _iconv($items,$client=array()){

        if(empty($client)){
            $client = array('OS'=>'Win','Lang'=>'zh-TW');
        }

        $separator = ',';
        if($client['OS'] =='Mac'){
            $separator = ';';
        }
        $items_str = join($separator, $items);

        $client['Lang'] = strtolower($client['Lang']);
        switch($client['Lang']){
            case 'zh-tw':
            case 'zh-hk':
                //$result = iconv('UTF-8', 'BIG5//IGNORE', $items_str); break;
                $result = mb_convert_encoding($items_str, 'BIG5','UTF-8'); break;
            case 'zh-cn':
            case 'zh-sg':
                //$result = iconv('UTF-8', 'GBK//IGNORE', $items_str); break;
                $result = mb_convert_encoding($items_str, 'GBK','UTF-8'); break;
            default:
                $result = $items_str;
        }
        $result = $result ."\n";
        return $result;
    }
}