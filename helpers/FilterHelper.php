<?php
/**
 *
 *
 * @author ChenBin
 * @version $Id:FilterHelper.php, v1.0 2017-01-06 09:39 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\helpers;


class FilterHelper
{
    /**
     * 对值进行有效性检查与过滤处理，如果未提供任何处理规则，则不做任何处理,则原值返回，
     * 如果找到有效的处理规则，并成功处理，返回结果，否则返回false或null，如果有处理规则，
     * 但未匹配内部处理规则，则返回默认值.
     *
     * @param $value
     * @param null|mixed $default
     * @param null|string $type
     * @param null|mixed $filter
     * @return array|mixed|null|string
     */
    public static function filter($value, $default = null, $type = null, $filter = null)
    {
        if(is_string($value)){
            $value = trim($value);
        }
        if (empty($filter)) {
            if (empty($type)) {
                if (is_null($default)) {
                    return $value;
                } else {
                    $type = gettype($default);
                }
            }
            switch ($type) {
                case 'integer':
                    $result = filter_var($value, FILTER_VALIDATE_INT);
                    break;
                case 'boolean':
                    $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;
                case 'float':
                    $result = filter_var($value, FILTER_VALIDATE_FLOAT);
                    break;
                case 'string':
                    if (is_string($value)) {
                        $result = $value;
                    }else{
                        $result = false;
                    }
                    break;
                case 'datetime':
                    $result = Validator::isDateTime($value);
                    break;
                case 'date':
                    $result = Validator::isDate($value);
                    break;
                case 'time':
                    $result = Validator::isTime($value);
                    break;
                case 'array':
                    if (is_array($value) && count($value) > 0) {
                        $result = $value;
                    }else{
                        $result = false;
                    }
                    break;
                case 'object':
                    if (is_object($value)) {
                        $result = $value;
                    }else{
                        $result = false;
                    }
                    break;
                default:
                    $result = $default;
                    break;
            }
        } else {
            if (is_callable($filter)) {
                $result = call_user_func($filter, $value);
            } else if (is_array($filter)) {
                if (is_string($value)) {
                    $val = strtolower($value);
                    if (in_array($val, array_map("strtolower", $filter))) {
                        $result = $value;
                    }else{
                        $result = false;
                    }
                } elseif (is_scalar($value)) {
                    if (in_array($value, $filter)) {
                        $result = $value;
                    }else{
                        $result = false;
                    }
                }else{
                    $result = $value;
                }
            }else{
                $result = $default;
            }
        }
        return $result;
    }
}