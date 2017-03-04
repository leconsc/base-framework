<?php

/**
 * 常用验证器定义
 *
 * @author ChenBin
 * @version $Id: Validator.php, 1.0 2016-10-12 06:26+100 ChenBin$
 * @package: app\helpers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use DateTime;

class Validator
{
    /**
     * 手机号码验证.
     *
     * @param string $mobile
     * @return bool
     */
    public static function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

    /**
     * 检查值是否整型.
     *
     * @param $value
     * @return mixed
     */
    public static function isInt($value)
    {
        return is_int(filter_var($value, FILTER_VALIDATE_INT)) ? true : false;
    }

    /**
     * 检测日期是否有效，如果日期正确，则返回格式化日期.
     *
     * @param string $date
     * @param boolean $strict 是否严格模式
     * @return bool|string
     */
    public static function isDate($date, $strict = true)
    {
        if (is_string($date)) {
            $result = DateTime::createFromFormat('Y-m-d', $date);
            if ($result) {
                if ($strict) {
                    $errors = DateTime::getLastErrors();
                    if (empty($errors['warning_count'])) {
                        return $result->format('Y-m-d');
                    }
                } else {
                    return $result->format('Y-m-d');
                }
            }
        }
        return false;
    }

    /**
     * 检测日期时间是否有效，如果日期时间正确，则返回格式化日期时间.
     *
     * @param string $datetime
     * @param boolean $strict 是否严格模式
     * @return bool|string
     */
    public static function isDateTime($datetime, $strict = true)
    {
        if (is_string($datetime)) {
            $result = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
            if ($result) {
                if ($strict) {
                    $errors = DateTime::getLastErrors();
                    if (empty($errors['warning_count'])) {
                        return $result->format('Y-m-d H:i:s');
                    }
                } else {
                    return $result->format('Y-m-d H:i:s');
                }
            } else {
                $result = DateTime::createFromFormat('Y-m-d H:i', $datetime);
                if ($result) {
                    if ($strict) {
                        $errors = DateTime::getLastErrors();
                        if (empty($errors['warning_count'])) {
                            return $result->format('Y-m-d H:i');
                        }
                    } else {
                        return $result->format('Y-m-d H:i');
                    }
                }
            }
        }
        return false;
    }

    /**
     * 时间有效性检查，如果时间格式正确，则返回格式化时间.
     *
     * @param string $time
     * @param bool $strict
     * @return bool|string
     */
    public static function isTime($time, $strict = true)
    {
        if (is_string($time)) {
            $result = DateTime::createFromFormat('H:i:s', $time);
            if ($result) {
                if ($strict) {
                    $errors = DateTime::getLastErrors();
                    if (empty($errors['warning_count'])) {
                        return $result->format('H:i:s');
                    }
                } else {
                    return $result->format('H:i:s');
                }
            } else {
                $result = DateTime::createFromFormat('H:i', $time);
                if ($result) {
                    if ($strict) {
                        $errors = DateTime::getLastErrors();
                        if (empty($errors['warning_count'])) {
                            return $result->format('H:i');
                        }
                    } else {
                        return $result->format('H:i');
                    }
                }
            }
        }
        return false;
    }
}