<?php
/**
 *  手机号码验证器
 *
 * @author ChenBin
 * @version $Id: MobileValidator.php, 1.0 2016-10-15 17:29+100 ChenBin$
 * @package: tellhim.net
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\validators;

use app\assets\ValidationAsset;
use yii\helpers\Json;
use yii\validators\Validator;
use yii\web\JsExpression;
use Yii;

class MobileValidator extends Validator
{
    /** 验证模式 */
    public $pattern = '/^13[\d]{9}$|^14[57]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0678]{1}\d{8}$|^18[\d]{9}$/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = '无效的手机号码';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $valid = true;
        if (!is_numeric($value)) {
            $valid = false;
        } else if (!preg_match($this->pattern, $value)) {
            $valid = false;
        }
        $result = $valid ? null : [$this->message, []];
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $options = [
            'pattern' => new JsExpression($this->pattern),
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Yii::$app->language)
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'yii.validation.mobile(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}