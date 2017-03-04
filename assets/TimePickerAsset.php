<?php
/**
 *
 *
 * @author ChenBin
 * @version $Id: TimePickerAsset.php, 1.0 2017-01-22 15:39+100 ChenBin$
 * @package:
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, All rights Reserved.
 */


namespace app\assets;


use app\assets\LibraryAsset;

class TimePickerAsset extends LibraryAsset
{
    public $js = [
        'timepicker/jquery.timepicker.min.js'
    ];
    public $css = [
        'timepicker/jquery.timepicker.css'
    ];
}