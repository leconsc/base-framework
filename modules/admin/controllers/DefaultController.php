<?php
namespace app\modules\admin\controllers;

use app\modules\admin\components\BackendController;
use app\modules\admin\models\Administrator;
use app\widgets\ActiveForm;
use Yii;
use app\helpers\ResponseHelper;
use app\modules\admin\models\LoginForm;
use yii\helpers\Url;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends BackendController
{
    protected $_only = ['login', 'logout', 'password', 'index'];
    protected $_authenticated_user_actions = ['logout', 'password', 'index'];
    protected $_visitor_actions = ['login'];

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays the login page
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
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        return $this->goHome();
    }

    /**
     * 修改密码显示页面
     */
    public function actionPassword()
    {
        $model = Administrator::findOne(Yii::$app->user->getId());
        $model->setScenario(Administrator::CHANGE_PASSWORD);
        if ($model->load(Yii::$app->request->post())) {
            $result = ActiveForm::validateAndSave($model);
            if (!is_array($result)) {
                if($result) {
                    $result = ResponseHelper::getSuccessMessage('更新密码成功', Url::toRoute('index'));
                }else {
                    $result = ResponseHelper::getErrorMessage('更新密码成功失败！');
                }
            }
            return ResponseHelper::getJsonResponse($result);
        } else {
            $title = '修改密码';
            $this->_setTitle($title);
            $this->_addBreadcrumb($title);

            $model->password = '';
            return $this->render('password', ['model' => $model]);
        }
    }
}
