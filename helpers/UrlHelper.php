<?php

/**
 *
 *
 * @author chenbin
 * @version $Id: UrlHelper.php, 1.0 2016-09-18 11:56+100 chenbin$
 * @package: libs
 * @since 1.0
 * @copyright 2016(C)Copyright By chenbin, All rights Reserved.
 */
namespace app\helpers;

class UrlHelper
{
    /**
     * 验证URL的有效性.
     *
     * @param string $url
     * @return bool
     */
    public static function validate($url){
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 为一个指定的URL地址附加查询参数.
     *
     * @param string $url
     * @param array $urlParams
     * @return string
     */
    public static function attachUrlParams($url, array $urlParams)
    {
        if (empty($urlParams)) {
            return $url;
        }
        $separator = '?';
        if (strpos($url, $separator) !== false) {
            $separator = '&';
        }
        $url .= $separator . http_build_query($urlParams);
        return $url;
    }

    /**
     * URL反解板处理.
     *
     * @param array $parsedUrl
     * @return string
     */
    public static function unParseUrl(array $parsedUrl) {
        $scheme   = empty($parsedUrl['scheme']) ? '' : $parsedUrl['scheme'] . '://';
        $host     = empty($parsedUrl['host']) ? '' : $parsedUrl['host'];
        $port     = empty($parsedUrl['port']) ? '' : ':' . $parsedUrl['port'];
        $user     = empty($parsedUrl['user']) ? '' : $parsedUrl['user'];
        $pass     = empty($parsedUrl['pass']) ? '' : ':' . $parsedUrl['pass'];
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = empty($parsedUrl['path']) ? '' : $parsedUrl['path'];
        $query    = empty($parsedUrl['query']) ? '' : '?' . $parsedUrl['query'];
        $fragment = empty($parsedUrl['fragment']) ? '' : '#' . $parsedUrl['fragment'];

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}