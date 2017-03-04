<?php
/**
 * Html相关助手
 *
 * @author ChenBin
 * @version $Id:HtmlHelper.php, v1.0 2014-11-02 21:47 ChenBin $
 * @package app\helpers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, all rights reserved.
 */

namespace app\helpers;

use app\modules\admin\authorization\Operation;
use yii\helpers\Html;

class HtmlHelper
{
    /**
     * 显示一个整型的下拉列表
     *
     * @param int $start The start integer
     * @param int $end The end integer
     * @param int $inc The increment
     * @param string $name The value of the HTML name attribute
     * @param array $htmlOptions Additional HTML attributes for the <select> tag
     * @param mixed $selected The key that is selected
     * @param string $format
     * @param array $items The printf format to be applied to the number
     * @returns string $format HTML for the select list
     */
    public static function integerSelectList($start, $end, $inc, $name, array $htmlOptions = [], $selected = null, $format = null, array $items = [])
    {
        $start = intval($start);
        $end = intval($end);
        $inc = intval($inc);
        $items = (array)$items;
        $onlyFormatText = false;
        if (isset($htmlOptions['onlyFormatText'])) {
            if ($htmlOptions['onlyFormatText']) {
                $onlyFormatText = true;
            }
            unset($htmlOptions['onlyFormatText']);
        }
        if ($inc > 0) {
            for ($i = $start; $i <= $end; $i += $inc) {
                $fi = $format ? sprintf($format, $i) : $i;
                if ($onlyFormatText) {
                    $items[$i] = $fi;
                } else {
                    $items[$fi] = $fi;
                }
            }
        } else {
            for ($i = $end; $i >= $start; $i += $inc) {
                $fi = $format ? sprintf($format, $i) : $i;
                if ($onlyFormatText) {
                    $items[$i] = $fi;
                } else {
                    $items[$fi] = $fi;
                }
            }
        }
        return Html::dropDownList($name, $selected, $items, $htmlOptions);
    }

    /**
     * 显示完整的年月日列表
     *
     * @param string $name 显示属性名称
     * @param string $selected 选中的年月日
     * @param array $htmlOptions 定义属性选项
     * @return string
     */
    public static function dateSelectList($name, $selected = null, array $htmlOptions = [])
    {
        if ($selected) {
            $item = explode('-', $selected);
        } else {
            $item = ['', '', ''];
        }

        if (isset($htmlOptions['yearName'])) {
            $yearName = $htmlOptions['yearName'];
            unset($htmlOptions['yearName']);
        } else {
            $yearName = 'year';
        }

        if (isset($htmlOptions['monthName'])) {
            $monthName = $htmlOptions['monthName'];
            unset($htmlOptions['monthName']);
        } else {
            $monthName = 'month';
        }

        if (isset($htmlOptions['dayName'])) {
            $dayName = $htmlOptions['dayName'];
            unset($htmlOptions['dayName']);
        } else {
            $dayName = 'day';
        }

        if (isset($htmlOptions['prefix'])) {
            $yearName = $htmlOptions['prefix'] . $yearName;
            $monthName = $htmlOptions['prefix'] . $monthName;
            $dayName = $htmlOptions['prefix'] . $dayName;
            unset($htmlOptions['prefix']);
        }
        if (isset($htmlOptions['separator'])) {
            $separator = $htmlOptions['separator'];
            unset($htmlOptions['separator']);
        } else {
            $separator = ' ';
        }
        $format = null;
        if (isset($htmlOptions['format'])) {
            $format = $htmlOptions['format'];
        }
        if (isset($htmlOptions['inc'])) {
            $inc = $htmlOptions['inc'];
            unset($htmlOptions['inc']);
            if (is_array($inc)) {
                for ($i = 0; $i < 3; $i++) {
                    if (!isset($inc[$i])) {
                        $inc[$i] = 1;
                    }
                }
            } else {
                $inc = [$inc, 1, 1];
            }
        } else {
            $inc = [1, 1, 1];
        }
        if (!empty($name)) {
            $yearName = sprintf('%s[%s]', $name, $yearName);
            $monthName = sprintf('%s[%s]', $name, $monthName);
            $dayName = sprintf('%s[%s]', $name, $dayName);
        }
        $curYear = date('Y');
        if (isset($htmlOptions['maxYear'])) {
            $maxYear = trim($htmlOptions['maxYear']);
            $prefix = substr($maxYear, 0, 1);
            if (in_array($prefix, ['+', '-'])) {
                $maxYear = $curYear + $maxYear;
            }
            unset($htmlOptions['maxYear']);
        } else {
            $maxYear = $curYear + 10;
        }
        if (isset($htmlOptions['minYear'])) {
            $minYear = trim($htmlOptions['minYear']);
            $prefix = substr($minYear, 0, 1);
            if (in_array($prefix, ['+', '-'])) {
                $minYear = $curYear + $minYear;
            }
            unset($htmlOptions['minYear']);
        } else {
            $minYear = $curYear;
        }
        $maxMonth = 12;
        $maxDay = 31;
        $items = array($yearName => [], $monthName => [], $dayName => []);
        if (isset($htmlOptions['prompt'])) {
            if ($htmlOptions['prompt']) {
                $items[$yearName] = ['' => '年'];
                $items[$monthName] = ['' => '月'];
                $items[$dayName] = ['' => '日'];
            }
            unset($htmlOptions['prompt']);
        }

        $yearSelect = self::integerSelectList($minYear, $maxYear, $inc[0], $yearName, $htmlOptions, $item[0], '%02s', $items[$yearName]);
        $monthSelect = self::integerSelectList(1, $maxMonth, $inc[1], $monthName, $htmlOptions, $item[1], '%02s', $items[$monthName]);
        $daySelect = self::integerSelectList(1, $maxDay, $inc[2], $dayName, $htmlOptions, $item[2], '%02s', $items[$dayName]);

        if ($format) {
            $dateList = sprintf($format, $yearSelect, $monthSelect, $daySelect);
        } else {
            if (is_array($separator)) {
                $dateList = $yearSelect . $separator[0] . $monthSelect . $separator[1] . $daySelect . $separator[2];
            } else {
                $dateList = $yearSelect . $separator . $monthSelect . $separator . $daySelect;
            }
        }
        return $dateList;
    }

    /**
     * 显示完整的时分秒列表
     *
     * @param string $name 显示属性名称
     * @param string $selected 选中的时分秒
     * @param array $htmlOptions 选项
     * @return string
     */
    public static function timeSelectList($name, $selected = null, array $htmlOptions = [])
    {
        if ($selected) {
            $selected = date("H:i:s", strtotime($selected));
            $item = explode(':', $selected);
        } else {
            $item = ['', '', ''];
        }
        if (isset($htmlOptions['hourName'])) {
            $hourName = $htmlOptions['hourName'];
            unset($htmlOptions['hourName']);
        } else {
            $hourName = 'hour';
        }

        if (isset($htmlOptions['minuteName'])) {
            $minuteName = $htmlOptions['minuteName'];
            unset($htmlOptions['minuteName']);
        } else {
            $minuteName = 'minute';
        }
        $disableSecond = false;
        if (isset($htmlOptions['disableSecond']) && $htmlOptions['disableSecond']) {
            $disableSecond = true;
        }
        $secondName = null;
        if (!$disableSecond) {
            if (isset($htmlOptions['secondName'])) {
                $secondName = $htmlOptions['secondName'];
                unset($htmlOptions['secondName']);
            } else {
                $secondName = 'second';
            }
        }

        if (isset($htmlOptions['prefix'])) {
            $hourName = $htmlOptions['prefix'] . $hourName;
            $minuteName = $htmlOptions['prefix'] . $minuteName;
            if (!$disableSecond) {
                $secondName = $htmlOptions['prefix'] . $secondName;
            }
            unset($htmlOptions['prefix']);
        }
        if (isset($htmlOptions['separator'])) {
            $separator = $htmlOptions['separator'];
            unset($htmlOptions['separator']);
        } else {
            $separator = ' ';
        }
        $format = null;
        if (isset($htmlOptions['format'])) {
            $format = $htmlOptions['format'];
        }
        if (!empty($name)) {
            $hourName = sprintf('%s[%s]', $name, $hourName);
            $minuteName = sprintf('%s[%s]', $name, $minuteName);
            if (!$disableSecond) {
                $secondName = sprintf('%s[%s]', $name, $secondName);
            }
        }
        $items = [$hourName => [], $minuteName => []];
        if (!$disableSecond) {
            $items[$secondName] = [];
        }
        if (isset($htmlOptions['prompt'])) {
            if ($htmlOptions['prompt']) {
                $items[$hourName] = ['' => '时'];
                $items[$minuteName] = ['' => '分'];
                if (!$disableSecond) {
                    $items[$secondName] = ['' => '秒'];
                }
            }
            unset($htmlOptions['prompt']);
        }
        $hourSelect = self::integerSelectList(0, 23, 1, $hourName, $htmlOptions, $item[0], '%02s', $items[$hourName]);
        $minuteSelect = self::integerSelectList(0, 59, 1, $minuteName, $htmlOptions, $item[1], '%02s', $items[$minuteName]);
        $secondSelect = '';
        if (!$disableSecond) {
            $secondSelect = self::integerSelectList(0, 59, 1, $secondName, $htmlOptions, $item[2], '%02s', $items[$secondName]);
        }

        if ($format) {
            if ($disableSecond) {
                $timeList = sprintf($format, $hourSelect, $minuteSelect);
            } else {
                $timeList = sprintf($format, $hourSelect, $minuteSelect, $secondSelect);
            }
        } else {
            $timeList = $hourSelect . $separator . $minuteSelect;
            if (!$disableSecond) {
                $timeList .= $separator . $secondSelect;
            }
        }
        return $timeList;
    }

    /**
     * @param int $rowNum The row index
     * @param int $value The record value
     * @param string $name checkBox name;
     * @param array $htmlOptions The options of the form element
     * @return string
     */
    public static function idBox($rowNum, $value, $name = 'cid', array $htmlOptions = array())
    {
        $htmlOptions = (array)$htmlOptions;
        $htmlOptions['id'] = 'cb' . $rowNum;
        $htmlOptions['onclick'] = 'isChecked(this.checked);';

        if ('[]' != substr($name, -2)) {
            $name .= '[]';
        }
        $htmlOptions['value'] = $value;

        return Html::checkbox($name, false, $htmlOptions);
    }

    /**
     * 解析请求Action中是否包含资源名称.
     *
     * @param string $action
     * @param array $options
     * @return boolean 权限是否有效
     */
    public static function accessCheck($action, array $options = [])
    {
        if (!isset($options['authItem'])) {
            $options['authItem'] = $action;
        }
        $pos = strpos($options['authItem'], '/');
        if ($pos === false) {
            $resource = null;
            $action = $options['authItem'];
        } else {
            $route = $action;
            $resource = substr($route, 0, $pos);
            $action = substr($route, $pos + 1);
        }
        if (AuthorizationHelper::check($action, $resource)) {
            return true;
        }
        return false;
    }

    /**
     * 获取按钮链接.
     *
     * @param integer $i
     * @param string $text
     * @param string $action
     * @param array $htmlOptions
     * @param bool $accessCheck
     * @return string
     */
    public static function buttonLink($i, $text, $action = 'edit', array $htmlOptions = array(), array $options = []
        , $accessCheck = true)
    {
        if ($accessCheck && !self::accessCheck($action, $options)) {
            return '';
        }
        $htmlOptions = (array)$htmlOptions;
        if (empty($action)) {
            $action = 'edit';
        }
        if (!isset($htmlOptions['onclick'])) {
            if (!isset($htmlOptions['noAction']) || !$htmlOptions['noAction']) {
                $htmlOptions['onclick'] = sprintf("executeAction('cb%s', '%s');return false", $i, $action);
            } else {
                $htmlOptions['refAction'] = $action;
            }
        }
        if (isset($htmlOptions['class'])) {
            $htmlOptions['class'] .= 'button button-rounded button-highlight';
        } else {
            $htmlOptions['class'] = 'button button-rounded button-highlight';
        }
        if (isset($htmlOptions['url'])) {
            $url = $htmlOptions['url'];
            unset($htmlOptions['url']);
        } else {
            $url = 'javascript:void(0)';
        }
        return Html::a($text, $url, $htmlOptions);
    }

    /**
     * 生成权限链接.
     *
     * @param string $text
     * @param string $url
     * @param string $action
     * @param array $htmlOptions
     * @param bool $accessCheck
     * @return string
     */
    public static function link($text, $url, $action, array $htmlOptions = [], $accessCheck = true)
    {
        if ($accessCheck && !self::accessCheck($action)) {
            return $text;
        }
        return Html::a($text, $url, $htmlOptions);
    }

    /**
     * 获取编辑链接
     *
     * @param int $i 索引位置
     * @param string $text 链接文本
     * @param string $action action
     * @param array $htmlOptions HTML属性选项
     * @param array $options 扩展选项
     * @param bool $accessCheck
     * @return string
     */
    public static function actionLink($i, $text, $action = 'edit', array $htmlOptions = [], array $options = []
        , $accessCheck = true)
    {
        if ($accessCheck && !self::accessCheck($action, $options)) {
            return $text;
        }
        if (isset($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' action';
        } else {
            $htmlOptions['class'] = 'action';
        }
        if (isset($htmlOptions['url'])) {
            $url = $htmlOptions['url'];
            unset($htmlOptions['url']);
        } else {
            $url = 'javascript:void(0)';
            if (!isset($htmlOptions['onclick'])) {
                if ($action) {
                    if (!isset($htmlOptions['noAction']) || !$htmlOptions['noAction']) {
                        $htmlOptions['onclick'] = sprintf("executeAction('cb%s', '%s');return false", $i, $action);
                    } else {
                        $htmlOptions['data-action'] = $action;
                    }
                }
            }
        }
        unset($htmlOptions['noAction']);
        return Html::a($text, $url, $htmlOptions);
    }

    /**
     * 獲取操作按鈕.
     *
     * @param string $text
     * @param string $action
     * @param null|string $iconSuffix
     * @param array $options 扩展选项
     * @param bool $accessCheck
     * @return string
     */
    public static function actionButton($text, $action, $iconSuffix = null, array $options = [], $accessCheck = true)
    {
        if ($accessCheck && !self::accessCheck($action, $options)) {
            return '';
        }
        $htmlOptions = array();
        $url = 'javascript:void(0)';
        $htmlOptions['class'] = 'easyui-linkbutton';
        $htmlOptions['data-action'] = $action;
        if ($iconSuffix && is_string($iconSuffix)) {
            $htmlOptions['iconCls'] = 'icon-' . $iconSuffix;
        }
        $htmlOptions['plain'] = 'true';
        return Html::a($text, $url, $htmlOptions);
    }

    /**
     * 获取文本类链接.
     *
     * @param integer $i
     * @param string $text
     * @param string $action
     * @param array $htmlOptions
     * @param string $options 扩展选项
     * @return string
     */
    public static function textActionLink($i, $text, $action = 'edit', array $htmlOptions = [], array $options = [], $accessCheck = true)
    {
        return self::actionLink($i, $text, $action, $htmlOptions, $options, $accessCheck);
    }

    /**
     * 显示状态链接
     *
     * @param int $i 列表编号
     * @param int $state 发布状态
     * @param null $items 选项
     * @param string $action 行为
     * @param array $htmlOptions Html元素选项
     * @param array $options 扩展选项
     * @param bool $accessCheck 是否检查权限
     * @return string
     */
    public static function stateActionLink($i, $state, $items = null, $action = 'state', array $htmlOptions = []
        , array $options = [], $accessCheck = true)
    {
        $disabled = false;
        if ($accessCheck && !self::accessCheck($action, $options)) {
            $disabled = true;
        }else if(isset($options['disabled'])){
            $disabled = (boolean)$options['disabled'];
        }
        $defaultItems = array('1' => '有效', '0' => '无效');
        $items = ArrayHelper::merge2($defaultItems, $items?:[]);
        $text = $items[$state];
        if ($disabled) {
            $class = 'label-default';
            $title = '禁止改变';
        } else {
            $class = $state ? 'label-success' : 'label-warning';
            $title = '点击' . ($state ? $items['0'] : $items['1']);
        }
        if (isset($htmlOptions['title'])) {
            $title = $htmlOptions['title'];
            unset($htmlOptions['title']);
        } else {
            $title = $text . '状态，' . $title;
        }
        if (isset($htmlOptions['class'])) {
            $class = $htmlOptions['class'];
            unset($htmlOptions['class']);
        }
        if ($class) {
            $class = ' ' . $class;
        }
        $content = sprintf('<span class="label%s" title="%s">%s</span>', $class, $title, $text);
        if ($disabled) {
            return $content;
        } else {
            return self::actionLink($i, $content, $action, $htmlOptions);
        }
    }

    /**
     * 显示发布状态链接
     *
     * @param int $i 列表编号
     * @param int $published 发布状态
     * @param array $htmlOptions Html元素选项
     * @param array $options 扩展选项
     * @param bool $accessCheck 是否检查权限
     * @return string
     */
    public static function publishActionLink($i, $published, array $htmlOptions = [], array $options = [], $accessCheck = true)
    {
        $items = ['1' => '已发布', '0' => '未发布'];
        return self::stateActionLink($i, $published, $items, 'publish', $htmlOptions, $options, $accessCheck);
    }

    /**
     * 显示图标按钮
     *
     * @param integer $i
     * @param string $action
     * @param null|string $title
     * @param array $htmlOptions
     * @param array $options
     * @param bool $accessCheck
     * @return string
     */
    public static function iconActionLink($i, $action, $title = null, array $htmlOptions = [], array $options = [], $accessCheck = true)
    {
        if ($accessCheck && !self::accessCheck($action, $options)) {
            return '';
        }
        if (isset($htmlOptions['title'])) {
            $title = $htmlOptions['title'];
            unset($htmlOptions['title']);
        }
        if (isset($htmlOptions['class'])) {
            $class = $htmlOptions['class'];
        }else {
            $class = $action;
        }
        if (is_null($title)) {
            $title = Operation::getOperationDescription($action);
        }
        $content = sprintf('<span class="icon-button %s" title="%s"></span>', $class, $title);
        return self::actionLink($i, $content, $action, $htmlOptions, $options, $accessCheck);
    }

    /**
     * 生成图标链接.
     *
     * @param null|string $title
     * @param string $url
     * @param string $action
     * @param array $htmlOptions
     * @param bool $accessCheck
     * @return string
     */
    public static function iconLink($title, $url, $action, array $htmlOptions = [], $accessCheck = true){
        if ($accessCheck && !self::accessCheck($action)) {
            return '';
        }
        if (isset($htmlOptions['title'])) {
            $title = $htmlOptions['title'];
            unset($htmlOptions['title']);
        }
        if (isset($htmlOptions['class'])) {
            $class = $htmlOptions['class'];
            unset($htmlOptions['class']);
        }else {
            $class = $action;
        }
        $content = sprintf('<span class="icon-button %s" title="%s"></span>', $class, $title);
        return Html::a($content, $url, ['class'=>'action']);
    }
    /**
     * 生成发布状态下拉列表框.
     *
     * @param integer $published
     * @param array $items
     * @param array $htmlOptions
     * @return string
     */
    public static function publishDropDownList($published, array $items = [], $htmlOptions = [])
    {
        $items = ArrayHelper::merge2(['' => '--全部--', '1' => '发布', '0' => '不发布'], $items);
        $htmlOptions = ArrayHelper::merge(['class'=>'form-select'], $htmlOptions);
        $content = Html::dropDownList('published', $published, $items, $htmlOptions);
        return $content;
    }

    /**
     * 生成状态下拉列表框.
     *
     * @param integer $state
     * @param string $field
     * @param array $items
     * @param array $htmlOptions
     * @return string
     */
    public static function stateDropDownList($state, $field = 'freeze', array $items = [], $htmlOptions = [])
    {
        $items = ArrayHelper::merge2(['' => '--全部--', '0' => '活动', '1' => '冻结'], $items);
        $htmlOptions = ArrayHelper::merge(['class'=>'form-select'], $htmlOptions);
        $content = Html::dropDownList($field, $state, $items, $htmlOptions);
        return $content;
    }

    /**
     * 生成发布状态单选列表.
     *
     * @param \app\widgets\ActiveForm $form
     * @param \app\base\Model $model
     * @param array $items
     * @param array $htmlOptions
     * @return string
     */
    public static function publishRadioButtonList($form, $model, array  $items = [], array $htmlOptions = [])
    {
        $items = ArrayHelper::merge2(['1' => '发布', '0' => '不发布'], $items);
        $htmlOptions = ArrayHelper::merge(['separator' => '&nbsp;', 'required' => 'required'], $htmlOptions);
        return $form->field($model, 'published', ['inline' => true])->radioList($items, $htmlOptions);
    }

    /**
     * 生成状态单选框列表.
     *
     * @param \app\widgets\ActiveForm $form
     * @param \app\base\Model $model
     * @param string $field
     * @param array $items
     * @param array $htmlOptions
     * @return string
     */
    public static function stateRadioButtonList($form, $model, $field = 'freeze', array $items = [], array $htmlOptions = [])
    {
        $items = ArrayHelper::merge2(['1' => '冻结', '0' => '活动'], $items);
        $htmlOptions = ArrayHelper::merge(['separator' => '&nbsp;', 'required' => 'required'], $htmlOptions);
        if(!isset($htmlOptions['template'])){
            $htmlOptions['template'] ="{label}<div class=\"col-sm-3\">{input}</div>\n<div class=\"col-sm-8\">{error}</div>";
        }
        return $form->field($model, $field, ['inline' => true])->radioList($items, $htmlOptions);
    }

    /**
     * 生成排序输入框
     *
     * @param \app\widgets\ActiveForm $form
     * @param \app\base\Model $model
     * @param array $htmlOptions
     * @return string
     */
    public static function orderingInput($form, $model, array $htmlOptions = [])
    {
        $htmlOptions = ArrayHelper::merge(['class' => 'form-control ordering', 'required' => 'required'], $htmlOptions);
        $template = "{label}\n<div class='col-sm-1'>{input}</div><div class='col-sm-2 label-flag'>(数字越大越靠前)</div>\n<div class='col-sm-8'>{error}</div>";
        return $form->field($model, 'ordering', ['template' => $template])->textInput($htmlOptions);
    }

    /**
     * 显示排序操作按钮
     *
     * @param integer $i
     * @param integer $n
     * @param integer $value
     * @param array $htmlOptions
     * @param bool $showIcon
     * @return string
     */
    public static function orderButton($i, $n, $value, array $htmlOptions = [], $showIcon = true)
    {
        $content = '<div class="ordering">';
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        } else {
            $id = 'ordering_' . $i;
        }
        $content .= '<span class="order-text">' . Html::input('number', 'ordering[]', $value, ['required' => 'required', 'class' => 'form-control', 'id' => $id]) . '</span>';
        if ($showIcon) {
            $options = ['authItem'=>Operation::O_EDIT];
            if ($i > 0) {
                $content .= '<span class="order-up" title="上移">' . self::actionLink($i, '上移', 'orderUp', $htmlOptions, $options) . '</span>';
            }
            if ($i < $n - 1) {
                $content .= '<span class="order-down" title="下移">' . self::actionLink($i, '下移', 'orderDown', $htmlOptions, $options) . '</span>';
            }
        }
        $content .= '</div>';

        return $content;
    }

    /**
     * 重要的警告性文字.
     *
     * @param $text
     * @return string
     */
    public static function warning($text)
    {
        return self::label($text, 'important');
    }

    /**
     * 非重要性的标注性文字.
     *
     * @param string $text
     * @param string $class
     * @param string $title
     * @return string
     */
    public static function label($text, $class = 'flag', $title = null)
    {
        if ($title) {
            $title = ' title="' . $title . '"';
        } else {
            $title = '';
        }

        switch ($class) {
            case 'flag':
                $content = sprintf('<span class="label-%s"%s>%s</span>', $class, $title, $text);
                break;
            default:
                $content = sprintf('<span class="label label-%s"%s>%s</span>', $class, $title, $text);
                break;
        }
        return $content;
    }
    /**
     * 格式化JS输出.
     *
     * @param string $str
     * @return mixed|string
     */
    public static function jsTextFormat($str)
    {
        $str = trim($str);
        $str = str_replace('\\s\\s', '\\s', $str);
        $str = str_replace(chr(10), '', $str);
        $str = str_replace(chr(13), '', $str);
        $str = str_replace(' ', '', $str);
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('"', '\\"', $str);
        $str = str_replace('\\\'', '\\\\\'', $str);
        $str = str_replace("'", "\'", $str);
        return $str;
    }
    /**
     * 给标题渲染颜色.
     *
     * @param string $title
     * @param string $color
     * @return bool|string
     */
    public static function renderColor($title, $color)
    {
        if (empty($title) || empty($color)) {
            return $title;
        }
        return "<span style='color:$color'>" . $title . "</span>";
    }
} 