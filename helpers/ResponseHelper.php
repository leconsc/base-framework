<?php

/**
 * 响应相关的助手
 *
 * @author ChenBin
 * @version $Id: ResponseHelper.php, 1.0 2016-09-18 12:12+100 ChenBin$
 * @package: slf
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use yii\base\Model;
use yii\web\Response;
use yii\bootstrap\Html;
use Yii;
use yii\helpers\Json;

class ResponseHelper
{
    /**
     * 输出JSON数据
     *
     * @access public
     * @param array $data
     * @return void
     */
    public static function sendJson($data)
    {
        header('Content-type: application/json;charset=utf-8');
        echo Json::encode($data);
        Yii::$app->end();
    }

    /**
     * 以JSON格式定义响应输出.
     *
     * @param mixed $data
     * @return mixed
     */
    public static function getJsonResponse($data){
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

    /**
     * 组合错误数组为一条错误信息.
     *
     * @param array $errors
     * @return string
     */
    public static function combineErrors(array $errors)
    {
        $message = array();
        foreach ($errors as $field => $error) {
            $message[] = $field . ':' . $error[0];
        }
        return join("\n", $message);
    }

    /**
     * 发送JSON格式的错误信息
     *
     * @access public
     * @param null|string|Model $spec
     * @return array
     */
    public static function getErrorMessage($spec = null)
    {
        $data = [];
        if (is_object($spec) && ($spec instanceof Model)) {
            $errors = $spec->getErrors();
            foreach ($errors as $attribute => $error) {
                $data[Html::getInputId($spec, $attribute)] = $error;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = $spec;
        }
        return $data;
    }

    /**
     * 以JSON格式输出错误信息.
     *
     * @param null|string|Model $spec
     */
    public static function sendErrorMessage($spec = null){
        $data = self::getErrorMessage($spec);
        self::sendJson($data);
    }
    /**
     * 发送JSON格式的成功信息
     *
     * @access public
     * @param null|string $message 显示信息
     * @param null|string $redirectUrl 跳转URL
     * @return  array
     */
    public static function getSuccessMessage($message = null, $redirectUrl = null)
    {
        $data = ['status' => 'success'];
        if (!empty($message)) {
            $data['message'] = $message;
        }
        if (!empty($redirectUrl)) {
            $data['redirectUrl'] = $redirectUrl;
        }
        return $data;
    }

    /**
     * 以JSON格式输出成功信息.
     *
     * @param null|string $message
     * @param null|string $redirectUrl
     */
    public static function sendSuccessMessage($message = null, $redirectUrl = null){
        $result = self::getSuccessMessage($message, $redirectUrl);
        self::sendJson($result);
    }
    /**
     * 创建操作成功信息，并跳转.
     *
     * @param string $message
     * @param string $redirectUrl
     * @return Response
     */
    public static function sendSuccessRedirect($message, $redirectUrl)
    {
        if (!empty($message)) {
            Yii::$app->session->setFlash('success', $message);
        }
        return Yii::$app->controller->redirect($redirectUrl);
    }

    /**
     * 创建操作失败信息，并跳转.
     *
     * @param string $message
     * @param string $redirectUrl
     * @return Response
     */
    public static function sendErrorRedirect($message, $redirectUrl)
    {
        if (is_array($message)) {
            $messageArr = [];
            foreach ($message as $messageItems) {
                $messageArr = array_merge($messageArr, $messageItems);
            }
            if (count($messageArr) > 1) {
                $items = ['<ol>'];
                foreach ($messageArr as $messageItem) {
                    $items[] = sprintf('<li>%s</li>', $messageItem);
                }
                $items [] = '</ol>';
                $message = join("\n", $items);
            } else {
                $message = $messageArr[0];
            }
        }
        if (!empty($message)) {
            Yii::$app->session->setFlash('error', $message);
        }
        return Yii::$app->controller->redirect($redirectUrl);
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