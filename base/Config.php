<?php

/**
 * 配置基础类
 *
 * @author ChenBin
 * @version $Id:ConfigList.php, 1.0 2015-10-12 17:38+100 ChenBin$
 * @package: app\base
 * @since 1.0
 * @copyright 2015(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\base;


use yii\helpers\Html;

abstract class Config
{
    const DEFAULT_GROUP_TEMPLATE = '{content}';
    const DEFAULT_ITEM_TEMPLATE = '{input}{description}';

    const GROUP_TITLE = 'title';
    const GROUP_DESCRIPTION = 'description';
    const GROUP_ITEMS = 'items';
    const GROUP_TEMPLATE = 'template';

    const ITEMS = 'items';
    const ITEM_TITLE = 'title';
    const ITEM_ID = 'id';
    const ITEM_NAME = 'name';
    const ITEM_VALUE = 'value';
    const ITEM_VALUE_TYPE = 'value_type';
    const ITEM_DEFAULT_VALUE = 'default_value';
    const ITEM_DESCRIPTION = 'description';
    const ITEM_TEMPLATE = 'template';

    const UI_TYPE = 'ui_type';
    const UI_OPTIONS = 'options';
    const UI_HTML_OPTIONS = 'html_options';

    const UI_TYPE_TEXT = 'text';
    const UI_TYPE_NUMBER = 'number';
    const UI_TYPE_RANGE = 'range';
    const UI_TYPE_DATE = 'date';
    const UI_TYPE_TIME = 'time';
    const UI_TYPE_EMAIL = 'email';
    const UI_TYPE_TEL = 'tel';
    const UI_TYPE_URL = 'url';
    const UI_TYPE_HIDDEN = 'hidden';
    const UI_TYPE_PASSWORD = 'password';
    const UI_TYPE_TEXTAREA = 'textarea';
    const UI_TYPE_RADIO = 'radio';
    const UI_TYPE_CHECKBOX = 'checkbox';
    const UI_TYPE_DROPDOWNLIST = 'dropDownList';
    const UI_TYPE_LISTBOX = 'listbox';
    const UI_TYPE_CHECKBOXLIST = 'checkBoxList';
    const UI_TYPE_RADIOLIST = 'radioList';
    const UI_TYPE_GROUP = 'group';

    const VALUE_TYPE_STRING = 'string';
    const VALUE_TYPE_INT = 'int';
    const VALUE_TYPE_NUMERIC = 'numeric';
    const VALUE_TYPE_FLOAT = 'float';
    const VALUE_TYPE_DECIMAL = 'decimal';
    const VALUE_TYPE_URL = 'url';
    const VALUE_TYPE_TEL = 'tel';
    const VALUE_TYPE_DATE = 'date';
    const VALUE_TYPE_TIME = 'time';
    const VALUE_TYPE_RANGE = 'range';


    /** @var array 配置项定义 */
    protected static $_configItems = array();
    /** @var string TipIcon路径 */
    protected static $_tipIcon;

    /**
     * 初始化函数
     */
    private static function _init()
    {
        static::_setUp();
        if (!is_array(self::$_configItems) || count(self::$_configItems) === 0) {
            throw new \Exception('配置项目未初始化!');
        }
    }

    /**
     * 配置项设置
     */
    protected static function _setUp()
    {

    }

    /**
     * 获取所有定义的配置项.
     *
     * @return array
     */
    public static function getConfigItems()
    {
        static::_init();
        return self::$_configItems;
    }

    /**
     * 获取所有可用的配置项类型定义.
     *
     * @return array
     */
    public static function getConfigTypes()
    {
        static::_init();

        $configNames = array();
        foreach (static::$_configItems as $groupName => $configItems) {
            foreach ($configItems[self::ITEMS] as $configItem) {
                if (strcasecmp($configItem[self::UI_TYPE], self::UI_TYPE_GROUP)) {
                    $configNames[$configItem[self::ITEM_NAME]] = isset($configItem[self::ITEM_VALUE_TYPE]) ? $configItem[self::ITEM_VALUE_TYPE] : self::VALUE_TYPE_STRING;
                } else {
                    foreach ($configItem[self::ITEMS] as $item) {
                        $configNames[$item[self::ITEM_NAME]] = isset($item[self::ITEM_VALUE_TYPE]) ? $item[self::ITEM_VALUE_TYPE] : self::VALUE_TYPE_STRING;
                    }
                }
            }
        }
        return $configNames;
    }

    /**
     * 获取默认值列表.
     *
     * @return array
     */
    public static function getConfigDefaultValueList()
    {
        static::_init();

        $defaultValueList = array();
        foreach (static::$_configItems as $groupName => $configItems) {
            foreach ($configItems[self::ITEMS] as $configItem) {
                if (strcasecmp($configItem[self::UI_TYPE], self::UI_TYPE_GROUP)) {
                    $defaultValueList[$configItem[self::ITEM_NAME]] = isset($configItem[self::ITEM_DEFAULT_VALUE]) ? $configItem[self::ITEM_DEFAULT_VALUE] : '';
                } else {
                    foreach ($configItem[self::ITEMS] as $item) {
                        $defaultValueList[$item[self::ITEM_NAME]] = isset($item[self::ITEM_DEFAULT_VALUE]) ? $item[self::ITEM_DEFAULT_VALUE] : '';
                    }
                }
            }
        }
        return $defaultValueList;
    }

    /**
     * 设置TipIcon.
     *
     * @param string $icon
     */
    public static function setTipIcon($icon)
    {
        static::$_tipIcon = Html::img($icon);
    }

    /**
     * 获取Tip信息.
     *
     * @return string
     */
    protected static function _getTipIcon()
    {
        if (empty(self::$_tipIcon)) {
            self::$_tipIcon = Html::tag('span', 'info', ['class' => 'label label-warning']);
        }
        return self::$_tipIcon;
    }

    /**
     * 创建Tool Tip.
     *
     * @param array $configItem 每个配置项信息
     * @return string
     */
    protected static function _createTooltip(array $configItem)
    {
        if (isset($configItem[self::ITEM_DESCRIPTION])) {
            return Html::a(self::_getTipIcon(), 'javascript:void(0)', ['data-toggle' => 'tooltip', 'title' => $configItem[self::ITEM_DESCRIPTION]]);
        }
        return '';
    }

    /**
     * 配置项解析
     *
     * @param array $configItem 每个配置项
     * @param null|string|number|callable $spec 配置默认值或获取回调值的一个回调函数
     * @return bool|mixed|string
     */
    public static function render(array $configItem, $spec = null, array $htmlOptions = [], $return = false)
    {
        $parser = self::getInstance();
        $content = $value = $callback = null;
        if (is_callable($spec)) {
            $callback = $spec;
        } else {
            $value = $spec;
        }
        $uiType = isset($configItem[self::UI_TYPE]) ? $configItem[self::UI_TYPE] : self::UI_TYPE_TEXT;
        if (strcasecmp(self::UI_TYPE_GROUP, $uiType)) { //非组合元素解析
            $method = '_form' . ucfirst($uiType);
            if (is_callable(array($parser, $method))) {
                $htmlOptions = isset($configItem[self::UI_HTML_OPTIONS]) ? array_merge($configItem[self::UI_HTML_OPTIONS], $htmlOptions) : $htmlOptions;
                if (!is_null($callback)) {
                    $value = $callback($configItem[self::ITEM_NAME]);
                }
                if (is_null($value)) {
                    $value = isset($configItem[self::ITEM_DEFAULT_VALUE]) ? $configItem[self::ITEM_DEFAULT_VALUE] : null;
                }
                $template = self::DEFAULT_ITEM_TEMPLATE;
                if (isset($configItem[self::ITEM_TEMPLATE])) {
                    $template = $configItem[self::ITEM_TEMPLATE];
                }

                $parts = [];
                $parts['{input}'] = $parser->$method($configItem, $value, $htmlOptions);
                $parts['{description}'] = self::_createTooltip($configItem);
                $content = strtr($template, $parts);
            }
        } else {
            if (isset($configItem[self::GROUP_ITEMS]) && is_array($configItem[self::GROUP_ITEMS])) { //组合元素解析
                $result = array();
                foreach ($configItem[self::GROUP_ITEMS] as $item) {
                    $uiType = isset($item[self::UI_TYPE]) ? $item[self::UI_TYPE] : self::UI_TYPE_TEXT;
                    $htmlOptions = isset($item[self::UI_HTML_OPTIONS]) ? array_merge($item[self::UI_HTML_OPTIONS], $htmlOptions) : $htmlOptions;
                    $method = '_form' . ucfirst($uiType);
                    if (is_callable(array($parser, $method))) {
                        if (!is_null($callback)) {
                            $value = $callback($item[self::ITEM_NAME]);
                        }
                        if (is_null($value)) {
                            $value = isset($item[self::ITEM_DEFAULT_VALUE]) ? $item[self::ITEM_DEFAULT_VALUE] : null;
                        }
                        $template = self::DEFAULT_ITEM_TEMPLATE;
                        if (isset($configItem[self::ITEM_TEMPLATE])) {
                            $template = $configItem[self::ITEM_TEMPLATE];
                        }
                        $parts = [];
                        $parts['{input}'] = $parser->$method($configItem, $value, $htmlOptions);
                        $parts['{description}'] = self::_createTooltip($configItem);
                        $result[] = strtr($template, $parts);;
                    }
                }
                if (count($result)) {
                    $template = self::GROUP_TEMPLATE;
                    if (isset($configItem[self::GROUP_TEMPLATE])) {
                        if(strpos($template, '%s') !== false) {
                            array_unshift($result, $configItem[self::GROUP_TEMPLATE]);
                            $content = call_user_func_array('sprintf', $result);
                        }else{
                            $content = strtr($configItem[self::GROUP_TEMPLATE], '{content}', join('', $result));
                        }
                    } else {
                        $content = strtr($template, '{content}', join('', $result));
                    }
                }
            }
        }
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * 获取解析文件唯一实例.
     *
     * @return $this
     */
    private static function getInstance()
    {
        static $_instance;

        if (!is_object($_instance)) {
            $className = get_called_class();
            $_instance = new $className();
        }
        return $_instance;
    }

    /**
     * 显示文本输入框.
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formText(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::textInput($configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示数字输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formNumber(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_NUMBER, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示范围文本输入框.
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formRange(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_RANGE, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示日期输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formDate(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_DATE, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示时间输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formTime(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_TIME, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示Email输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formEmail(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_EMAIL, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示电话输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formTel(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_TEL, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示URL输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formUrl(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::input(self::UI_TYPE_URL, $configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示隐藏输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formHidden(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::hiddenInput($configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示密码输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formPassword(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::passwordInput($configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示多行文本输入框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formTextArea(array $configItem, $value, array $htmlOptions = [])
    {
        return Html::textarea($configItem[self::ITEM_NAME], $value, $htmlOptions);
    }

    /**
     * 显示单选框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formRadio(array $configItem, $value, array $htmlOptions = [])
    {
        $checked = false;
        if (isset($htmlOptions[self::ITEM_VALUE])) {
            if ($htmlOptions[self::ITEM_VALUE] == $value) {
                $checked = true;
            }
        } else {
            $checked = $value ? true : false;
        }
        return Html::radio($configItem[self::ITEM_NAME], $checked, $htmlOptions);
    }

    /**
     * 显示复选框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formCheckBox(array $configItem, $value, array $htmlOptions = [])
    {
        $checked = false;
        if (isset($htmlOptions[self::ITEM_VALUE])) {
            if ($htmlOptions[self::ITEM_VALUE] == $value) {
                $checked = true;
            }
        } else {
            $checked = $value ? true : false;
        }
        return Html::checkbox($configItem[self::ITEM_NAME], $checked, $htmlOptions);
    }

    /**
     * 显示下拉列表框
     *
     * @param array $configItem
     * @param mixed $selection
     * @param array $htmlOptions
     * @return string
     */
    private function _formDropDownList(array $configItem, $selection = null, array $htmlOptions = [])
    {
        return Html::dropDownList($configItem[self::ITEM_NAME], $selection, $configItem[self::UI_OPTIONS], $htmlOptions);
    }

    /**
     * 显示列表框
     *
     * @param array $configItem
     * @param mixed $selection
     * @param array $htmlOptions
     * @return string
     */
    private function _formListBox(array $configItem, $selection = null, array $htmlOptions = [])
    {
        return Html::listBox($configItem[self::ITEM_NAME], $selection, $configItem[self::UI_OPTIONS], $htmlOptions);
    }

    /**
     * 显示多个复选框
     *
     * @param array $configItem
     * @param mixed $value
     * @param array $htmlOptions
     * @return string
     */
    private function _formCheckBoxList(array $configItem, $selection = null, array $htmlOptions = [])
    {
        if (!isset($htmlOptions['separator'])) {
            $htmlOptions['separator'] = '&nbsp;';
        }
        return Html::checkboxList($configItem[self::ITEM_NAME], $selection, $configItem[self::UI_OPTIONS], $htmlOptions);
    }

    /**
     * 显示多个单选框
     *
     * @param array $configItem
     * @param mixed $selection
     * @param array $htmlOptions
     * @return string
     */
    private function _formRadioList(array $configItem, $selection = null, array $htmlOptions = [])
    {
        if (!isset($htmlOptions['separator'])) {
            $htmlOptions['separator'] = '&nbsp;';
        }
        return Html::radioList($configItem[self::ITEM_NAME], $selection, $configItem[self::UI_OPTIONS], $htmlOptions);
    }
}