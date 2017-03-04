<?php

/**
 * 数组处理函数封装
 *
 * @author ChenBin
 * @version $Id:ArrayHelper.php, v1.0 2014-08-26 15:48 ChenBin $
 * @category app\helpers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin,all rights reserved.
 */
namespace app\helpers;

use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * 转换一个数组所有内容为整型值
     *
     * @access public
     * @static
     * @param array $array 待转换的数组
     * @param mixed $default 默认值
     * @return array 转换结果
     */
    public static function toInteger(array $array, $default = null)
    {
        if (is_array($array)) {
            foreach ($array as $i => $v) {
                if (is_array($v)) {
                    $array[$i] = self::toInteger($v);
                } else {
                    $v = trim($v);
                    $array[$i] = (int)$v;
                }
            }
        } else {
            if ($default === null) {
                $array = array();
            } elseif (is_array($default)) {
                $array = self::toInteger($default);
            } else {
                $array = array((int)$default);
            }
        }

        return $array;
    }

    /**
     * 转换一个数组所有内容为浮点型值
     *
     * @access public
     * @static
     * @param array $array 待转换的数组
     * @param mixed $default 默认值
     * @return array 转换结果
     */
    public static function toFloat(array $array, $default = null)
    {
        if (is_array($array)) {
            foreach ($array as $i => $v) {
                if (is_array($v)) {
                    $array[$i] = self::toFloat($v);
                } else {
                    $v = trim($v);
                    $array[$i] = (float)$v;
                }
            }
        } else {
            if ($default === null) {
                $array = array();
            } elseif (is_array($default)) {
                $array = self::toFloat($default);
            } else {
                $array = array((float)$default);
            }
        }

        return $array;
    }

    /**
     * 转换一个数组至一个对象
     *
     * @access public
     * @static
     * @param array $array
     * @param boolean $filterNumeric
     * @param string $class
     * @return null or object
     */
    public static function toObject(array $array, $filterNumeric = false, $class = 'stdClass')
    {
        $obj = null;
        if (is_array($array)) {
            $obj = new $class();
            foreach ($array as $k => $v) {
                if (!$filterNumeric || !is_numeric($k)) {
                    if (is_array($v)) {
                        $obj->$k = self::toObject($v, $class);
                    } else {
                        $obj->$k = $v;
                    }
                }
            }
            $object_vars = get_object_vars($obj);
            if (empty($object_vars)) {
                $obj = null;
            }
        }
        return $obj;
    }

    /**
     * 转换一个数组为一字符串
     *
     * @access public
     * @static
     * @param array $array
     * @param string $innerGlue
     * @param string $outerGlue
     * @param boolean $keepOuterKey
     * @return string
     */
    public static function toString(array $array, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false)
    {
        $output = array();
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                if (is_array($item)) {
                    if ($keepOuterKey) {
                        $output[] = $key;
                    }
                    // This is value is an array, go and do it again!
                    $output[] = self::toString($item, $innerGlue, $outerGlue, $keepOuterKey);
                } else {
                    $output[] = $key . $innerGlue . '"' . $item . '"';
                }
            }
        }

        return implode($outerGlue, $output);
    }

    /**
     * 转换一个数组的值为一个字符串
     * @access public
     * @static
     * @param array $array
     * @param string $outerGlue
     * @param boolean $quote
     * @return string
     */
    public static function toValueString(array $array, $outerGlue = ',', $quote = true)
    {
        $output = array();
        if (is_array($array)) {
            foreach ($array as $value) {
                if ($quote) {
                    while (!empty($value)) {
                        if (substr($value, 0, 1) === "'") {
                            $value = substr($value, 1);
                        } elseif (substr($value, -1) === "'") {
                            $value = substr($value, 0, -1);
                        } else {
                            break;
                        }
                    }
                    $value = "'" . $value . "'";
                }
                $output[] = $value;
            }
        }
        return implode($outerGlue, $output);
    }

    /**
     * 把一个对象转换成数组
     *
     * @access public
     * @static
     * @param object $obj
     * @param boolean $recursion
     * @param string $regex
     * @return null or array
     */
    public static function fromObject($obj, $recursion = true, $regex = null)
    {
        $result = null;
        if (is_object($obj)) {
            $result = array();
            foreach (get_object_vars($obj) as $k => $v) {
                if ($regex) {
                    if (!preg_match($regex, $k)) {
                        continue;
                    }
                }
                if (is_object($v)) {
                    if ($recursion) {
                        $result[$k] = self::fromObject($v, $recursion, $regex);
                    }
                } else {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 把一个字符串撤分成一个数组
     *
     * @access public
     * @static
     * @param string $string 要撤分的字符串
     * @param string $separator 撤分符号
     * @param boolean $regex 使用正则撤分
     * @return array
     */
    public static function fromString($string, $separator = ",", $regex = false)
    {
        $string = preg_replace('/\s*/', '', (string)$string);
        if ($regex) {
            $result = preg_split($separator, $string);
        } else {
            $result = explode($separator, $string);
        }
        return $result;
    }

    /**
     * 从数组中移除空白元素（包括只有空白字符的元素）
     *
     * @access public
     * @static
     * @param array $arr
     * @param boolean $trim
     * @return array
     */
    public static function removeEmpty(&$arr, $trim = true)
    {
        $result = array();
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = self::removeEmpty($arr[$key]);
                if (!empty($value)) {
                    $result[$key] = $value;
                }
            } else {
                $value = trim($value);
                if ($value == '') {
                    continue;
                } elseif ($trim) {
                    $result[$key] = $value;
                } else {
                    $result[$key] = $arr[$key];
                }
            }
        }
        return $result;
    }

    /**
     * 从数组中移除零值.
     *
     * @param $arr
     * @return array
     */
    public static function removeZeroNegativeValue($arr)
    {
        $result = array();
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = self::removeZeroNegativeValue($arr[$key]);
                if (!empty($value)) {
                    $result[$key] = $value;
                }
            } else {
                $value = intval($value);
                if ($value <= 0) {
                    continue;
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * 将一个二维数组转换为 hashMap
     *
     * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
     *
     * @access public
     * @static
     * @param array $arr
     * @param string $keyField
     * @param string $valueField
     * @return array
     */
    public static function toHashMap(&$arr, $keyField, $valueField = null)
    {
        $ret = array();
        if ($valueField) {
            foreach ($arr as $row) {
                $ret[$row[$keyField]] = $row[$valueField];
            }
        } else {
            foreach ($arr as $row) {
                $ret[$row[$keyField]] = $row;
            }
        }
        return $ret;
    }

    /**
     * 将一个二维数组按照指定字段的值分组
     *
     * @access public
     * @static
     * @param array $arr
     * @param string $keyField
     * @return array
     */
    public static function groupBy(& $arr, $keyField)
    {
        $ret = array();
        foreach ($arr as $row) {
            $key = $row[$keyField];
            if (!isset($ret[$key])) {
                $ret[$key] = array();
            }
            $ret[$key][] = $row;
        }
        return $ret;
    }

    /**
     * 合并数组，支持覆盖包含数字索引在内的所有元素
     *
     * @param array $a
     * @param array $b
     * @return mixed
     * @see self::merge
     */
    public static function merge2(array $a, array $b){
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if ($v instanceof UnsetArrayValue) {
                    unset($res[$k]);
                } elseif ($v instanceof ReplaceArrayValue) {
                    $res[$k] = $v->value;
                }elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }
}
?>