<?php
/**
 * 登入基本封装操作
 *
 * @author chenbin
 * @version $Id:LoginHelper.php, v1.0 12-9-22 16:04+100 chenbin $
 * @package Core
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
final class LoginHelper
{
    /**
     * 累记错误登入次数验证.
     *
     * @return bool
     * @throws Exception
     */
    public static function checkLoginTime()
    {
        if (Config::get('login.loginTryTimes', 0) > 0) {
            $loginPerm = self::_loginCheck();
            if (!$loginPerm) {
                $message = sprintf('累计%s次错误尝试，%s分钟内你不能登入。 ', Config::get('login.loginTryTimes', 5), Config::get('loginFreezeTime', 15));
                throw new Exception($message);
            }
        }
        return true;
    }

    /**
     * 登入失败结果处理.
     *
     * @return bool|string
     */
    public static function loginFailureProcess($message)
    {
        if (Config::get('login.loginTryTimes', 0) > 0) {
            $loginPerm = self::_loginCheck();
            self::_loginFailed($loginPerm);
            if ($loginPerm) {
                $message .= sprintf('，您可以至多%s次償試', Config::get('login.loginTryTimes'));
            }
        }
        return $message;
    }

    /**
     * 用户登入记录验证
     *
     * @return int 返回验证结果
     */
    private static function _loginCheck()
    {
        static $loginPerm;

        if (is_null($loginPerm)) {
            $timestamp = time();
            $login = Db::fetchRow('SELECT count, last_update FROM #__tb_failedlogins WHERE ip=?', UtilsHelper::getClientIp());
            if ($login) {
                if ($timestamp - $login['last_update'] > Config::get('login.loginFreezeTime', 15) * 60) {
                    $loginPerm = 3;
                } elseif ($login['count'] < Config::get('login.loginTryTimes', 5)) {
                    $loginPerm = 2;
                } else {
                    $loginPerm = 0;
                }
            } else {
                $loginPerm = 1;
            }
        }
        return $loginPerm;
    }

    /**
     * 登录失败记录
     *
     * @param int $permission　登录记录验证结果作为登败的参数
     */
    private static function _loginFailed($permission)
    {
        $timestamp = time();

        $onlineIp = UtilsHelper::getClientIp();
        switch ($permission) {
            case 1:
                Db::execute("REPLACE INTO #__tb_failedlogins (ip, count, last_update) VALUES (?, '1', ?)", array($onlineIp, $timestamp));
                break;
            case 2:
                Db::execute("UPDATE #__tb_failedlogins SET count=count+1, last_update=? WHERE ip=?", array($timestamp, $onlineIp));
                break;
            case 3:
                Db::execute("UPDATE #__tb_failedlogins SET count=1, last_update=? WHERE ip=?", array($timestamp, $onlineIp));
                $waitTime = Config::get('login.loginFreezeTime', 15) * 60;
                Db::execute("DELETE FROM #__tb_failedlogins WHERE last_update<?-?", array($timestamp, $waitTime));
                break;
        }
    }
}
