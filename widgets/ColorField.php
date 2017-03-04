<?php
/**
 *
 *
 * @author ChenBin
 * @version $Id:ColorField.php, v1.0 2017-01-12 09:43 ChenBin $
 * @package
 * @since 1.0
 * @see https://bgrins.github.io/spectrum/
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\widgets;

use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class ColorField extends InputWidget
{
    /**
     * 使用的渲染模板
     * @var string $template
     */
    public $template = '<div class="col-lg-10">{input}</div><div class="col-lg-2">{color}</div>';
    /** @var array $colorOptions 颜色选项 */
    public $colorOptions = [];
    /** @var array $options 表单元素options */
    public $options = [];
    /** @var bool|string 当前颜色 */
    public $color = false;
    /** @var bool 是否以平面形式显示 */
    public $flat = false;
    /** @var bool 是否显示按钮 */
    public $showButtons = true;
    /** @var bool 是否显示颜色输入框 */
    public $showInput = true;
    /** @var bool  */
    public $showInitial = true;
    /** @var bool 是否允许空 */
    public $allowEmpty = true;
    /** @var bool 是否显示透明 */
    public $showAlpha = true;
    /** @var bool 是否禁用 */
    public $disabled = false;
    /** @var string 本地存储使用的 */
    public $localStorageKey = null;
    /** @var bool 是否显示色板 */
    public $showPalette = true;
    /** @var bool 是否仅仅显示色板 */
    public $showPaletteOnly = true;
    /** @var bool 可切换色板与取色器 */
    public $togglePaletteOnly = true;
    /** @var bool */
    public $showSelectionPalette = true;
    /** @var bool  */
    public $clickoutFiresChange = false;
    /** @var null 取消按钮文字 */
    public $cancelText = null;
    /** @var null 选择按钮文字 */
    public $chooseText = null;
    /** @var string 查看更多 */
    public $togglePaletteMoreText = '查看更多';
    /** @var string less按钮文字 */
    public $togglePaletteLessText = '收缩';
    /** @var null 容器Class */
    public $containerClassName = null;
    /** @var null 替换使用的Class名字 */
    public $replacerClassName = null;
    /** @var string 颜色使用的格式 */
    public $preferredFormat = 'hex';
    /** @var int  */
    public $maxSelectionSize = 7;
    /** @var bool 点击色板后是否隐藏 */
    public $hideAfterPaletteSelect = true;
    public $palette = [
        ["#000", "#444", "#666", "#999", "#ccc", "#eee", "#f3f3f3", "#fff"],
        ["#f00", "#f90", "#ff0", "#0f0", "#0ff", "#00f", "#90f", "#f0f"],
        ["#f4cccc", "#fce5cd", "#fff2cc", "#d9ead3", "#d0e0e3", "#cfe2f3", "#d9d2e9", "#ead1dc"],
        ["#ea9999", "#f9cb9c", "#ffe599", "#b6d7a8", "#a2c4c9", "#9fc5e8", "#b4a7d6", "#d5a6bd"],
        ["#e06666", "#f6b26b", "#ffd966", "#93c47d", "#76a5af", "#6fa8dc", "#8e7cc3", "#c27ba0"],
        ["#c00", "#e69138", "#f1c232", "#6aa84f", "#45818e", "#3d85c6", "#674ea7", "#a64d79"],
        ["#900", "#b45f06", "#bf9000", "#38761d", "#134f5c", "#0b5394", "#351c75", "#741b47"],
        ["#600", "#783f04", "#7f6000", "#274e13", "#0c343d", "#073763", "#20124d", "#4c1130"]
    ];
    public $selectionPalette = [];

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->options = ArrayHelper::merge(['class' => 'form-control'], $this->options);
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
            $this->options['id'] = Html::getInputId($this->model, $this->attribute);
        } else {
            if (!isset($this->options['id'])) {
                $this->options['id'] = $this->name;
            }
            $input = Html::textInput($this->name, $this->value, $this->options);
        }
        if (isset($this->colorOptions['name'])) {
            $colorName = $this->colorOptions['name'];
            unset($this->colorOptions['name']);
        } else {
            $colorName = $this->attribute . '_color';
        }
        if ($this->hasModel()) {
            $color = Html::activeTextInput($this->model, $colorName, $this->colorOptions);
            $this->colorOptions['id'] = Html::getInputId($this->model, $colorName);
            $this->color = $this->model->$colorName;
        } else {
            $value = '';
            if (isset($this->colorOptions['value'])) {
                $value = $this->colorOptions['value'];
                unset($this->colorOptions['value']);
            }
            if (!isset($this->colorOptions['id'])) {
                $this->colorOptions['id'] = $colorName;
            }
            $color = Html::textInput($colorName, $value, $this->$this->colorOptions);
            $this->color = $value;
        }
        $this->registerClientScript();
        echo strtr($this->template, [
            '{input}' => $input,
            '{color}' => $color,
        ]);
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $config = $this->getClientConfig();
        $colorFieldId = $this->colorOptions['id'];
        $view = $this->getView();
        ColorAsset::register($view);
        $view->registerJs("jQuery('#$colorFieldId').spectrum($config);");
    }

    /**
     * Returns the options for the color JS widget.
     * @return array the options
     */
    protected function getClientConfig()
    {
        $configItems = ['color','flat','showInput','showInitial','allowEmpty','showAlpha','disabled','localStorageKey',
            'showPalette','showPaletteOnly','togglePaletteOnly','showSelectionPalette','clickoutFiresChange',
            'cancelText','chooseText','togglePaletteMoreText','togglePaletteLessText','containerClassName',
            'replacerClassName','preferredFormat','maxSelectionSize','palette','selectionPalette', 'hideAfterPaletteSelect'];
        $config = [];

        foreach ($configItems as $configItem){
            if(isset($this->$configItem)){
                $config[$configItem] = $this->$configItem;
            }
        }
        $config['hide'] = 'onHide';
        $eventCode = "function(color) {if(color){var color=color.toHexString();jQuery('#{$this->options['id']}').css('color',color);}else{jQuery('#{$this->options['id']}').removeAttr('style');}}";
        $config = Json::htmlEncode($config);
        $config = str_replace('"onHide"', $eventCode, $config);
        return $config;
    }
}