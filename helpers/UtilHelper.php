<?php

/**
 * 工具类助手
 *
 * @author ChenBin
 * @version $Id:UtilHelper.php, v1.0 2014-12-04 07:51 ChenBin $
 * @category app\helpers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\helpers;

class UtilHelper
{
    /**
     * 格式化方法名及对应的文件名.
     *
     * @param $name
     * @return string
     */
    public static function formatClassName($name)
    {
        $parts = explode('_', $name);
        $classParts = array();
        foreach ($parts as $part) {
            $classParts[] = ucfirst(strtolower($part));
        }
        return join('', $classParts);
    }



    /**
     * 驼峰格式转成下划线格式.
     *
     * @param $input
     * @return string
     */
    public static function decamelize($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
    /**
     * 获取客户端访问IP地址.
     *
     * @return string
     */
    public static function getUserHostAddress()
    {
        static $userHostAddress = null;

        if (empty($userHostAddress)) {
            $userHostAddress = $_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
                $userHostAddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
                foreach ($matches[0] as $xip) {
                    if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                        $userHostAddress = $xip;
                        break;
                    }
                }
            }
        }
        return $userHostAddress;
    }

    /**
     * 获取类的短名字.
     *
     * @param $object
     * @return string
     */
    public static function getClassShortName($object){
        $reflector = new \ReflectionClass($object);
        return $reflector->getShortName();
    }
    /**
     * 檢測是否有效的IP
     *
     * @param string $boundIp
     * @return bool
     */
    public static function checkIpBound($boundIp)
    {
        if (empty($boundIp)) {
            return true;
        }
        $clientIp = self::getUserHostAddress();
        $boundIp = '/^(' . str_replace(array('\\*', "\n", '\r\n'), array('.*', '|', '|'), preg_quote(($ips = trim($boundIp)), '/')) . ')$/i';
        if (@preg_match($boundIp, $clientIp)) {
            return true;
        }
        return false;
    }
    /**
     * 生成随机串
     *
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 20, $non_alphanumeric = true)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        if ($non_alphanumeric) {
            $chars .= '!@#$%^&*()~_+{}|?';
        }
        $randString = substr(str_shuffle(str_repeat($chars, 5)), 0, $length);

        return $randString;
    }
    /**
     * php获取中文字符拼音首字母
     *
     * @param $str
     * @return null|string
     */
    public static function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $firstChar = ord($str{0});
        if ($firstChar >= ord('A') && $firstChar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gbk//IGNORE', $str);
        $s2 = iconv('gbk', 'UTF-8//IGNORE', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }
}