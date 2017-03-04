<?php

/**
 * 管理员管理控制器
 *
 * @author ChenBin
 * @version $Id:AdministratorController.php, 1.0 2014-12-05 14:52+100 ChenBin$
 * @package: app\modules\admin\controllers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\modules\admin\authorization\RoleType;
use app\modules\admin\components\BackendController;
use app\modules\admin\models\AdminGroup;
use app\modules\admin\models\Administrator;
use app\modules\admin\services\AdminIdentity;
use app\widgets\ActiveForm;
use Exception;
use yii\helpers\Url;
use Yii;

class AdministratorController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '管理员管理';

            $this->_setTitle($title);
            $this->_addBreadcrumb($title, Url::toRoute(('index')));

            return true;
        }
        return false;
    }

    /**
     * 管理入口页
     */
    public function actionIndex()
    {
        $params = array();

        $params['roleTypes'] = RoleType::getRoleTypes();
        $params['groups'] = AdminGroup::getGroupPairs();

        $params['sortName'] = RequestHelper::fetch('sortName', 'created_at');
        $params['sortOrder'] = RequestHelper::fetch('sortOrder', 'desc');
        $params['searchWord'] = RequestHelper::fetch('searchWord');
        $params['group'] = RequestHelper::fetch('group');
        $params['roleType'] = RequestHelper::fetch('roleType');
        $params['freeze'] = RequestHelper::fetch('freeze');
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 20);
        $params['model'] = new Administrator();

        return $this->render('index', $params);
    }

    /**
     * 为查询列表提供Ajax数据源
     */
    public function actionGet()
    {
        try {
            RequestHelper::setDataSource('POST');

            $page = RequestHelper::fetch('page', 1);
            $limit = RequestHelper::fetch('limit', 20);
            $sortName = RequestHelper::fetch('sortName', 'created_at', ['username', 'gid', 'created_at', 'modified_at', 'freeze']);
            $sortOrder = RequestHelper::fetch('sortOrder', 'desc', ['desc', 'asc']);
            $searchWord = RequestHelper::fetch('searchWord');
            $roleType = RequestHelper::fetch('roleType');
            $group = RequestHelper::fetch('group');
            $freeze = RequestHelper::fetch('freeze');

            $query = Administrator::find();

            $offset = (($page - 1) * $limit);
            $order = "$sortName $sortOrder";

            if ($searchWord) {
                $query->andFilterWhere(['or', ['like', 'username', $searchWord], ['like', 'truename', $searchWord]]);
            }
            if (Validator::isInt($roleType)) {
                $groups = AdminGroup::getGroupsByRole($roleType);
                if ($groups) {
                    $query->andWhere(['gid' => array_keys($groups)]);
                }
            }
            if ($group) {
                $query->andWhere(['gid' => $group]);
            }
            if (Validator::isInt($freeze)) {
                $query->andWhere(['freeze' => $freeze]);
            }
            list($total, $rows) = Administrator::getList($query, $order, $limit, $offset);

            $params = array();
            $params['page'] = $page;
            $params['total'] = $total;
            $params['rows'] = $rows;
            $params['canFreeze'] = $this->accessCheck('freeze');

            return $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }

    /**
     * 创建资料
     */
    public function actionCreate()
    {
        $title = '新增管理员';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $params = [];
        $model = new Administrator();
        $model->setScenario(Administrator::CREATE);
        $canFreeze = $this->accessCheck('freeze');
        $params['model'] = $model;
        $params['groups'] = AdminGroup::getGroups();
        $params['groupPairs'] = AdminGroup::getGroupPairs();
        $params['canFreeze'] = $canFreeze;
        $model->gid = key($params['groupPairs']);
        if ($canFreeze) {
            $model->freeze = 0;
        } else {
            $model->freeze = 1;
        }

        return $this->render('form', $params);
    }

    /**
     * 修改资料
     */
    public function actionEdit()
    {
        $title = '修改管理员资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $model = Administrator::findOne($id);
            if ($model) {
                $model->setScenario(Administrator::EDIT);
                $model->password = '';

                $params = [];
                $params['model'] = $model;
                $params['groups'] = AdminGroup::getGroups();
                $params['groupPairs'] = AdminGroup::getGroupPairs();
                $params['canFreeze'] = $this->accessCheck('freeze');
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的管理员编号', $this->_redirectUrl);
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
                $model = new Administrator();
                $uid = $model->getFormValue('uid', 0);
                if ($uid) {
                    $model = Administrator::findOne($uid);
                    if (!$model) {
                        throw new Exception('错误的管理员编号');
                    }
                    $model->setScenario(Administrator::EDIT);
                    $act = '修改';
                } else {
                    $act = '新增';
                    $model->setScenario(Administrator::CREATE);
                }
                if ($model->load($request->post())) {
                    $result = ActiveForm::validateAndSave($model);
                    if (!is_array($result)) {
                        if ($result) {
                            AdminIdentity::removeCache($model->uid);
                            $result = ResponseHelper::getSuccessMessage("管理员{$act}成功", $this->_redirectUrl);
                        } else {
                            throw new Exception("管理员{$act}失败");
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
     * 调整状态
     */
    public function actionFreeze()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new Administrator();
                $count = $model->changeState($id, [], 'freeze');
                if ($count) {
                    AdminIdentity::removeCache($id);
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
            $model = new Administrator();
            try {
                $count = $model->remove($cid);
                if ($count) {
                    AdminIdentity::removeCache($cid);
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