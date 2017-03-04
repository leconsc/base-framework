<?php
/**
 * 語言包翻譯工具
 *
 * @author chenbin
 * @version $Id:Lang.php, 1.0 2014-09-29 17:34+100 chenbin$
 * @package: wegame
 * @since 1.0
 * @copyright 2014(C)Copyright By XiaoShiJie, All rights Reserved.
 */

final class Lang {
    /**
     * 語言包翻譯.
     *
     * @return string
     */
    public static function _()
    {
        $argList = func_get_args();
        if (isset($argList[1]) && is_array($argList[1]) || !isset($argList[1])) {
            if(isset(Yii::app()->controller)){
                $category = Yii::app()->controller->id;
            }else{
                $category = PhpMessageSource::COMMON_LANG_CATEGORY;
            }
            array_unshift($argList, $category);
        }
        $category = null;
        $message = null;
        $params = array();
        $source = null;
        $language = null;
        foreach (array('category', 'message', 'params', 'source', 'language') as $argVarName) {
            if (count($argList)) {
                $$argVarName = array_shift($argList);
            } else {
                break;
            }
        }
        return yii::t($category, $message, $params, $source, $language);
    }
} 