<?php
/**
 * 网站入口页控制器
 *
 * @author ChenBin
 * @version $Id: SiteController.php, 1.0 2016-12-10 18:23+100 ChenBin$
 * @package: app\controllers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\controllers;

use app\helpers\ResponseHelper;
use app\models\Content;
use app\models\Member;
use app\widgets\ActiveForm;
use app\components\FrontendController;
use app\models\LoginForm;
use yii\helpers\Url;
use Yii;

class SiteController extends FrontendController
{
    protected $_only = ['login', 'logout', 'register'];
    protected $_authenticated_user_actions = ['logout'];
    protected $_visitor_actions = ['login', 'register'];

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $recommendItems = Content::getRecommendItems(2);

        $params = [];
        $params['recommendItems'] = $recommendItems;
        return $this->render('index', $params);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if($model->login()) {
                $result = ResponseHelper::getSuccessMessage(null, Yii::$app->user->getReturnUrl());
            }else{
                $result = ResponseHelper::getErrorMessage($model);
            }
            return ResponseHelper::getJsonResponse($result);
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * 用户注册
     * @return string
     */
    public function actionRegister()
    {
        $model = new Member(['scenario' => Member::REGISTER]);

        if ($model->load(Yii::$app->request->post())) {
            $result = ActiveForm::validateAndSave($model);
            if (!is_array($result)) {
                if ($result) {
                    $result = ResponseHelper::getSuccessMessage('注册成功', Url::to('index'));
                } else {
                    $result = ResponseHelper::getErrorMessage('注册失败！');
                }
            }
            return ResponseHelper::getJsonResponse($result);
        }
        $model->agree = 1;
        return $this->render('register', [
            'model' => $model
        ]);

    }
}
