<?php
/**
 * 会员资料操作控制器
 *
 * @author ChenBin
 * @version $Id:MemberController.php, v1.0 2017-01-18 09:32 ChenBin $
 * @package app\controllers
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\controllers;


use app\components\FrontendController;
use app\helpers\ResponseHelper;
use app\models\Member;
use app\services\MemberIdentity;
use app\widgets\ActiveForm;
use Yii;
use yii\helpers\Url;
use Exception;

class MemberController extends FrontendController
{
    /**
     * 显示用户当前基本信息.
     *
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $member = $this->_memberCheck();
        $title = '我的信息';
        $this->_setTitle($title);
        $this->_addBreadcrumb($title);
        return $this->render('index', ['member' => $member]);
    }

    /**
     * 密码修改.
     *
     * @return string
     */
    public function actionPassword()
    {
        $member = $this->_memberCheck();
        $member->setScenario(Member::CHANGE_PASSWORD);

        if ($member->load(Yii::$app->request->post())) {
            $result = ActiveForm::validateAndSave($member);
            if (!is_array($result)) {
                if ($result) {
                    $result = ResponseHelper::getSuccessMessage('密码修改成功', Url::toRoute('index'));
                } else {
                    $result = ResponseHelper::getErrorMessage('密码修改成功失败！');
                }
            }
            return ResponseHelper::getJsonResponse($result);
        } else {
            $title = '修改我的登录密码';
            $this->_setTitle($title);
            $this->_addBreadcrumb($title);
            $member->password = '';
            return $this->render('password', ['member' => $member]);
        }
    }

    /**
     * 帐号编辑.
     *
     * @return string
     */
    public function actionEdit()
    {
        $member = $this->_memberCheck();
        $member->setScenario(Member::EDIT);

        $title = '编辑我的信息';
        $this->_setTitle($title);
        $this->_addBreadcrumb($title);
        return $this->render('edit', ['member' => $member]);
    }

    /**
     * 数据保存
     * @return array|mixed|\yii\web\Response
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $member = $this->_memberCheck();
                $member->setScenario(Member::EDIT);
                if ($member->load($request->post())) {
                    $result = ActiveForm::validateAndSave($member);
                    if (!is_array($result)) {
                        if ($result) {
                            MemberIdentity::removeCache($member->uid);
                            $result = ResponseHelper::getSuccessMessage("您的信息修改成功", $this->_redirectUrl);
                        } else {
                            throw new Exception("您的信息修改失败");
                        }
                    }
                } else {
                    throw new Exception('无效的请求');
                }
            } catch (Exception $e) {
                $result = ResponseHelper::getErrorMessage($e->getMessage());
            }
            return ResponseHelper::getJsonResponse($result);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * 帐号验证。
     *
     * @return array|null|\yii\db\ActiveRecord|\yii\web\Response
     */
    private function _memberCheck()
    {
        try {
            $uid = Yii::$app->user->getId();
            $member = Member::find()->where(['uid' => $uid])->one();
            if ($member) {
                if ($member->freeze) {
                    throw new Exception('帐号因为某些原因被管理唢冻结，请联系管理员');
                }
                return $member;
            } else {
                throw new Exception('帐号不存在或者被删除！');
            }
        } catch (Exception $e) {
            return ResponseHelper::sendErrorRedirect($e->getMessage(), Url::toRoute('site/index'));
        }
    }
}