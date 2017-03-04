<?php
/**
 * 字符串操作相关助手
 *
 * @author ChenBin
 * @version $Id: StringHelper.php, 1.0 2016-10-08 19:50+100 ChenBin$
 * @package: app\helpers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;


class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * 基于UTF-8去掉字符串前后的字符列表中的字符.
     *
     * @param string $string
     * @param null|string $charList
     * @return mixed|string
     */
    public static function trim($string, $charList = null)
    {
        if (is_null($charList)) {
            return trim($string);
        } else {
            $charList = preg_quote($charList, '/');
            return preg_replace("/(^[$charList]+)|([$charList]+$)/us", '', $string);
        }
    }

    /**
     * 基于UTF-8去掉字符串后面的字符列表中的字符.
     *
     * @param string $string
     * @param null|string $charList
     * @return mixed|string
     */
    public static function rtrim($string, $charList = null)
    {
        if (is_null($charList)) {
            return rtrim($string);
        } else {
            $charList = preg_quote($charList, '/');
            return preg_replace("/([$charList]+$)/us", '', $string);
        }
    }

    /**
     * 基于UTF-8去掉字符串前面的字符列表中的字符.
     *
     * @param string $string
     * @param null|string $charList
     * @return mixed|string
     */
    public static function ltrim($string, $charList = null)
    {
        if (is_null($charList)) {
            return ltrim($string);
        } else {
            $charList = preg_quote($charList, '/');
            return preg_replace("/(^[$charList]+)/us", '', $string);
        }
    }

    /**
     * 截取字符
     * @param string $string
     * @param int $length
     * @return string
     */
    public static function substring($string, $length)
    {
        return mb_substr($string, 0, $length, 'UTF-8');
    }
}