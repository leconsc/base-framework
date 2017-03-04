<?php

/**
 * 扩展表单功能，主要增加错误显示方式
 *
 * @author ChenBin
 * @version $Id: ActiveForm.php, 1.0 2016-10-16 11:09+100 ChenBin$
 * @package: app\widgets
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\widgets;

use app\helpers\RequestHelper;
use app\base\Model;
use yii\bootstrap\ActiveField;
use yii\helpers\Html;

class ActiveForm extends \yii\bootstrap\ActiveForm
{
    /** 弹出信息 */
    const MESSAGE_DISPLAY_MODE_POPUP = 1;
    /** 以摘要形式显示信息 */
    const MESSAGE_DISPLAY_MODE_SUMMARY = 2;
    /** 在表单旁边显示信息 */
    const MESSAGE_DISPLAY_MODE_SIMPLE = 3;
    /**
     * @var boolean whether to enable AJAX-based data validation.
     */
    public $enableAjaxValidation = true;
    /**
     * @var boolean whether to perform validation when the value of an input field is changed.
     * If [[ActiveField::validateOnChange]] is set, its value will take precedence for that input field.
     */
    public $validateOnChange = false;
    /**
     * @var boolean whether to perform validation when an input field loses focus.
     * If [[ActiveField::$validateOnBlur]] is set, its value will take precedence for that input field.
     */
    public $validateOnBlur = false;
    /**
     * @var int $errorDisplayMode 错误显示模式
     */
    public $messageDisplayMode = self::MESSAGE_DISPLAY_MODE_SIMPLE;

    public $validateAndSave = true;
    /** @var array $fieldConfig field字段定义选项定义 */
    public $fieldConfig = [
        'template' => "{label}\n<div class=\"{inputClass}\">{input}</div>\n<div class=\"{errorClass}\">{error}</div>",
        'labelOptions' => ['class' => '{labelClass} control-label'],
    ];

    /**
     * Returns the options for the form JS widget.
     * @return array the options
     */
    protected function getClientOptions()
    {
        $options = array_merge(
            parent::getClientOptions(),
            array(
                'messageDisplayMode' => $this->messageDisplayMode,
                'validateAndSave' => $this->validateAndSave
            )
        );
        return array_diff_assoc($options, [
            'messageDisplayMode' => self::MESSAGE_DISPLAY_MODE_SIMPLE,
            'validateAndSave' => true
        ]);
    }

    /**
     * 数据验证与保存.
     *
     * @param Model $model
     * @return bool|array
     */
    public static function validateAndSave(Model $model)
    {
        $validateAndSave = RequestHelper::get('validateAndSave', false);
        $status = $model->validate();
        if ($status && $validateAndSave) {
            if ($model->save(false)) {
                return true;
            } else {
                return false;
            }
        } else {
            $result = [];
            if (!$status) {
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[Html::getInputId($model, $attribute)] = $errors;
                }
            }
            return $result;
        }
    }
    /**
     * @inheritdoc
     * @return ActiveField the created ActiveField object
     */
    public function field($model, $attribute, $options = [])
    {
        if(!isset($options['template'])){
            $replacePairs = [];
            if(!isset($options['inputClass'])){
                $options['inputClass'] = 'col-lg-3';
            }
            $replacePairs['{inputClass}'] = $options['inputClass'];
            if(!isset($options['errorClass'])){
                $options['errorClass'] = 'col-lg-8';
            }
            $replacePairs['{errorClass}'] = $options['errorClass'];
            $options['template'] = strtr($this->fieldConfig['template'], $replacePairs);
            unset($options['inputClass'], $options['errorClass']);
        }
        if(!isset($options['labelOptions']['class'])){
            $replacePairs = [];
            if(!isset($options['labelClass'])){
                $options['labelClass'] = 'col-lg-1';
            }
            $replacePairs['{labelClass}'] = $options['labelClass'];
            $options['labelOptions']['class'] = strtr($this->fieldConfig['labelOptions']['class'], $replacePairs);
            unset($options['labelClass']);
        }
        return parent::field($model, $attribute, $options);
    }
}