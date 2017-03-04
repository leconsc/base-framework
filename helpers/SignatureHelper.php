<?php

/**
 * 参数签名处理
 *
 * @author ChenBin
 * @version $Id:SignatureHelper.php, 1.0 2014-10-28 16:33+100 ChenBin$
 * @package: Core
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

class SignatureHelper
{
    const PARAM_PREFIX = 'a_';
    const SIGNATURE_KEY = 'sig';

    private static $_signatureKey = null;
    /** @var array 签名参数 */
    private static $_signatureParams = array();
    /** @var array 非签名参数 */
    private static $_nonSignatureParams = array();
    /** @var boolean 是否已经解析 */
    private static $_parsed = false;

    /**
     * 创建签名参数及签名.
     *
     * @param array $signatureParams
     * @return array
     */
    public static function create(array $signatureParams)
    {
        ksort($signatureParams);
        $signatureArr = array();
        $params = array();
        foreach ($signatureParams as $field => $value) {
            if (substr($field, 0, strlen(self::PARAM_PREFIX)) == self::PARAM_PREFIX) {
                $field = substr($field, strlen(self::PARAM_PREFIX));
            }
            $signatureArr[] = $field . '=' . $value;
            $params[self::PARAM_PREFIX . $field] = $value;
        }
        $signatureKey = self::getSignatureKey();
        $signatureString = join('&', $signatureArr) . $signatureKey;
        $params[self::SIGNATURE_KEY] = sha1($signatureString);
        return $params;
    }

    /**
     * 设置签名用的signatureKey
     *
     * @param $signatureKey
     */
    public static function setSignatureKey($signatureKey)
    {
        if (is_string($signatureKey)) {
            self::$_signatureKey = $signatureKey;
        }
    }

    /**
     * 获取签名Key
     * @return null|string
     */
    public static function getSignatureKey()
    {
        if (self::$_signatureKey === null) {
            self::$_signatureKey = Config::get('signatureKey');
        }
        return self::$_signatureKey;
    }

    /**
     * 解析签名参数.
     *
     * @param array $params
     * @return boolean
     */
    public static function parse(array $params)
    {
        if (!self::$_parsed) {
            $prefixLen = strlen(self::PARAM_PREFIX);
            foreach ($params as $field => $value) {
                if (strlen($field) > $prefixLen && substr($field, 0, $prefixLen) == self::PARAM_PREFIX) {
                    $name = substr($field, $prefixLen);
                    self::$_signatureParams[$name] = $value;
                } else if (!isset($signatureParams[$field]) && ($field != self::SIGNATURE_KEY)) {
                    self::$_nonSignatureParams[$field] = $value;
                }
            }
            self::$_parsed = true;
        }
        return true;
    }

    /**
     * 清除解析结果
     */
    public static function clearParseResult()
    {
        self::$_signatureParams = array();
        self::$_nonSignatureParams = array();
        self::$_parsed = false;
    }

    /**
     * 签名正确性检查.
     *
     * @param array $requestParams
     * @return array|bool
     */
    public static function getSignatureParams(array $requestParams = array())
    {
        if (self::$_parsed) {
            return self::$_signatureParams;
        }
        if (empty($requestParams)) {
            $requestParams = $_GET;
        }
        if (isset($requestParams[self::SIGNATURE_KEY])) {
            $signature = $requestParams[self::SIGNATURE_KEY];
            if (!empty($signature)) {
                if (self::parse($requestParams)) {
                    if (!empty(self::$_signatureParams)) {
                        $params = self::create(self::$_signatureParams);
                        if ($params[self::SIGNATURE_KEY] === $signature) {
                            return self::$_signatureParams;
                        } else {
                            throw new Exception('无效的签名信息');
                        }
                    }
                }
            }
        }
        return array();
    }

    /**
     * 获取请求中非签名参数.
     *
     * @param array $requestParams
     * @return array|bool
     */
    public static function getNonSignatureParams(array $requestParams = array())
    {
        if (empty($requestParams)) {
            $requestParams = $_GET;
        }

        if (self::parse($requestParams)) {
            return self::$_nonSignatureParams;
        }
        return false;
    }

    /**
     * 从中获取所有有效的参数.
     *
     * @param array $requestParams
     * @return array|bool
     */
    public static function getAllParams(array $requestParams = array())
    {
        if (empty($requestParams)) {
            $requestParams = $_GET;
        }
        if (self::parse($requestParams)) {
            return array_merge(self::$_nonSignatureParams, self::$_signatureParams);
        }
        return false;
    }
} 