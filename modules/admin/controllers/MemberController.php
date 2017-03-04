<?php

/**
 * 用户列表
 *
 * @author ChenBin
 * @version $Id:MemberController.php, v1.0 2014-12-08 22:24 ChenBin $
 * @category Controllers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\models\Member;
use app\modules\admin\components\BackendController;
use app\services\MemberIdentity;
use app\widgets\ActiveForm;
use Exception;
use yii\helpers\Url;
use Yii;

class MemberController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '用户管理';

            $this->_setTitle($title);
            $this->_addBreadcrumb($title, Url::toRoute(('index')));

            return true;
        }
        return false;
    }

    /**
     * 管理员入口页
     */
    public function actionIndex()
    {
        $params = [];

        RequestHelper::set('sortName', null);
        $params['sortName'] = RequestHelper::fetch('sortName', 'registration_time');
        $params['sortOrder'] = RequestHelper::fetch('sortOrder', 'desc');
        $params['searchField'] = RequestHelper::fetch('searchField', 'uid');
        $params['searchWord'] = RequestHelper::fetch('searchWord');
        $params['freeze'] = RequestHelper::fetch('freeze');
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 20);
        $params['model'] = new Member();

        return $this->render('index', $params);
    }

    /**
     * 获取用户列表数据.
     *
     * @throws Exception
     */
    public function actionGet()
    {
        try {
            RequestHelper::setDataSource('POST');
            $page = RequestHelper::fetch('page', 1);
            $limit = RequestHelper::fetch('limit', 20);
            $sortName = RequestHelper::fetch('sortName', 'registration_time', ['uid', 'registration_time', 'last_login_time', 'modified_at']);
            $sortOrder = RequestHelper::fetch('sortOrder', 'desc', ['desc', 'asc']);
            $searchField = RequestHelper::fetch('searchField', 'uid', ['uid', 'nickname', 'openid']);
            $searchWord = RequestHelper::fetch('searchWord');
            $freeze = RequestHelper::fetch('freeze');

            $query = Member::find();

            $offset = (($page - 1) * $limit);
            $order = "$sortName $sortOrder";

            if ($searchWord) {
                switch ($searchField) {
                    case 'uid':
                        $uid = intval($searchWord);
                        $query->where([$searchField => $uid]);
                        break;
                    case 'email':
                    case 'name':
                        $query->andFilterWhere(['like', $searchField, $searchWord]);
                        break;
                    case 'mobile':
                        if (Validator::isInt($searchWord)) {
                            $query->andFilterWhere(['like', $searchField, $searchWord]);
                        }
                        break;
                    default:
                        break;
                }
            }
            if (Validator::isInt($freeze)) {
                $query->andWhere(['freeze' => $freeze]);
            }

            list($total, $rows) = Member::getList($query, $order, $limit, $offset);

            $params = array();
            $params['page'] = $page;
            $params['total'] = $total;
            $params['rows'] = $rows;

            return $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }
    /**
     * 修改资料
     */
    public function actionEdit()
    {
        $title = '修改用户资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $model = Member::findOne($id);
            if ($model) {
                $model->setScenario(Member::CHANGE);
                $model->password = '';

                $params = [];
                $params['model'] = $model;
                $params['canFreeze'] = $this->accessCheck('freeze');
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的用户编号', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 数据保存
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $model = new Member();
                $uid = $model->getFormValue('uid', 0);
                if ($uid) {
                    $model = Member::findOne($uid);
                    if (!$model) {
                        throw new Exception('错误的用户编号');
                    }
                    $model->setScenario(Member::CHANGE);
                    if ($model->load($request->post())) {
                        $result = ActiveForm::validateAndSave($model);
                        if (!is_array($result)) {
                            if ($result) {
                                MemberIdentity::removeCache($model->uid);
                                $result = ResponseHelper::getSuccessMessage("用户资料修改成功", $this->_redirectUrl);
                            } else {
                                throw new Exception("用户资料修改失败");
                            }
                        }
                    } else {
                        throw new Exception('无效的请求');
                    }
                } else {
                    throw new Exception('无效的用户编号', $this->_redirectUrl);
                }
            }catch (Exception $e){
                $result = ResponseHelper::getErrorMessage($e->getMessage());
            }
            return ResponseHelper::getJsonResponse($result);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * 调整状态
     */
    public function actionFreeze()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new Member();
                $count = $model->changeState($id, [], 'freeze');
                if ($count) {
                    MemberIdentity::removeCache($id);
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect('状态更新失败', $this->_redirectUrl);
                }
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
    }

    /**
     * 删除资料
     */
    public function actionRemove()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $model = new Member();
            try {
                $count = $model->remove($cid);
                if ($count) {
                    MemberIdentity::removeCache($cid);
                    return ResponseHelper::sendSuccessRedirect('成功删除' . $count . '条数据', $this->_redirectUrl);
                } else {
                    throw new Exception('删除操作失败');
                }
            } catch (Exception $e) {
                return ResponseHelper::sendErrorRedirect($e->getMessage(), $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }
} 