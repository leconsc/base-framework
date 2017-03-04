<?php
/**
 * 加解密相关助手工具
 *
 * @author ChenBin
 * @version $Id: EncryptHelper.php, 1.0 2016-09-18 12:52+100 ChenBin$
 * @package: app\helpers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

class Encrypt
{
    /**
     * 生成用于保存的密码.
     *
     * @param $password
     * @param bool $original
     * @param int $start
     * @param int $length
     * @return string
     */
    public static function encryptPassword($password, $start = 5, $length = 20)
    {
        $password = sha1($password);
        $storedPassword = substr($password, $start, $length);
        return $storedPassword;
    }

    /**
     * 字符串加密碼.
     *
     * @param $string
     * @param string $key
     * @return string
     */
    public static function encryptString($string, $key = '')
    {
        return self::_authCode($string, 'ENCODE', $key);
    }

    /**
     * 字符串解密.
     *
     * @param $string
     * @param string $key
     * @return string
     */
    public static function decryptString($string, $key = '')
    {
        return self::_authCode($string, 'DECODE', $key);
    }

    /**
     * 加密解密實現函數.
     *
     * @param $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return string
     */
    private static function _authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        if (empty($string)) {
            return $string;
        }
        $cKeyLength = 4;
        $key = md5($key != '' ? $key : Config::get('encryptKey'));
        $keyA = md5(substr($key, 0, 16));
        $keyB = md5(substr($key, 16, 16));
        $keyC = $cKeyLength ? ($operation == 'DECODE' ? substr($string, 0, $cKeyLength) : substr(md5(microtime()), -$cKeyLength)) : '';

        $cryptKey = $keyA . md5($keyA . $keyC);
        $key_length = strlen($cryptKey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $cKeyLength)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyB), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndKey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndKey[$i] = ord($cryptKey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyB), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyC . str_replace('=', '', base64_encode($result));
        }
    }
}