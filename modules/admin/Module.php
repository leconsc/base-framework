<?php

namespace app\modules\admin;

use Yii;
use yii\helpers\Url;
use yii\web\Session;
use yii\web\User;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    public $layout = "main";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->name .= '管理后台';
        Yii::$app->errorHandler->errorAction = $this->id . '/default/error';
        Yii::$app->homeUrl = '/' . $this->id.'/default/index';
        Yii::$app->setComponents([
            'session' =>[
                'class' => Session::className(),
                'name' => 'PHPBACKSESSID',
            ],
            'user' => [
                'class' => User::className(),
                'identityClass' => 'app\modules\admin\services\AdminIdentity',
                'enableAutoLogin' => true,
                'loginUrl' => '/'.$this->id.'/default/login',
                'identityCookie' => [
                    'name' => '_backendUser'
                ]
            ]
        ]);
    }

    /**
     * 控制器运行前的动作
     * @param \yii\base\Action $action
     * @return boolean
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $app = Yii::$app;
            $route = $app->controller->id . '/' . $action->id;
            $publicPages = array(
                'default/login',
                'default/error',
            );
            if ($app->user->isGuest && !in_array($route, $publicPages)) {
                $app->user->loginRequired();
            } else if (!$app->user->isGuest && $route == 'default/login') {
                $app->response->redirect(Url::toRoute('default/index'));
            } else {
                return true;
            }
        }
        return false;
    }
}
