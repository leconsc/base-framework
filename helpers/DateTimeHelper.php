<?php
/**
 * 时间日期工具助手
 *
 * @author ChenBin
 * @version $Id: DateTimeHelper.php, 1.0 2016-06-24 16:12+100 ChenBin$
 * @package: app\helpers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\helpers;

use Yii;
class DateTimeHelper
{
    /**
     * 把时间惟转换为日期时间格式.
     *
     * @param $timeStamp
     * @return bool|string
     */
    public static function format($timeStamp)
    {
        if (empty($timeStamp)) {
            return '';
        } else {
            if(Validator::isInt($timeStamp)) {
                return Yii::$app->formatter->asDatetime($timeStamp);
            }else{
                return $timeStamp;
            }
        }
    }
}