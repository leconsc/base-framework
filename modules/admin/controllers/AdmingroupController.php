<?php
/**
 * 管理组管理
 *
 * @author ChenBin
 * @version $Id:AdmingroupController.php, v1.0 2016-12-03 22:49 ChenBin $
 * @category app\modules\admin\controllers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\modules\admin\authorization\RoleType;
use app\modules\admin\components\BackendController;
use app\modules\admin\models\AdminGroup;
use app\modules\admin\services\AdminIdentity;
use app\widgets\ActiveForm;
use Exception;
use yii\helpers\Url;
use Yii;

class AdmingroupController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '管理组管理';

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
        $params = array();

        $params['roleTypes'] = RoleType::getRoleTypes();
        $params['sortName'] = RequestHelper::fetch('sortName', 'created_at');
        $params['sortOrder'] = RequestHelper::fetch('sortOrder', 'desc');
        $params['searchWord'] = RequestHelper::fetch('searchWord');
        $params['roleType'] = RequestHelper::fetch('roleType');
        $params['freeze'] = RequestHelper::fetch('freeze');
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 20);
        $params['model'] = new AdminGroup();

        return $this->render('index', $params);
    }

    /**
     * 获取管理组数据.
     *
     * @throws Exception
     */
    public function actionGet()
    {
        try {
            RequestHelper::setDataSource(RequestHelper::POST);
            $page = RequestHelper::fetch('page', 1);
            $limit = RequestHelper::fetch('limit', 20);
            $sortName = RequestHelper::fetch('sortName', 'created_at', ['group_name', 'role_type', 'created_at', 'modified_at', 'freeze']);
            $sortOrder = RequestHelper::fetch('sortOrder', 'desc', ['desc', 'asc']);
            $searchWord = RequestHelper::fetch('searchWord');
            $roleType = RequestHelper::fetch('roleType');
            $freeze = RequestHelper::fetch('freeze');

            $query = AdminGroup::find();

            $offset = (($page - 1) * $limit);
            $order = "$sortName $sortOrder";

            if ($searchWord) {
                $query->andFilterWhere(['like', 'group_name', $searchWord]);
            }
            if (Validator::isInt($roleType)) {
                $query->andWhere(['role_type' => $roleType]);
            }
            if (Validator::isInt($freeze)) {
                $query->andWhere(['freeze' => $freeze]);
            }
            list($total, $rows) = AdminGroup::getList($query, $order, $limit, $offset);

            $params = array();
            $params['page'] = $page;
            $params['total'] = $total;
            $params['rows'] = $rows;
            $params['canFreeze'] = $this->accessCheck('freeze');
            $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }

    /**
     * 创建管理组
     */
    public function actionCreate()
    {
        $title = '新增管理组';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $params = [];
        $model = new AdminGroup();
        $model->setScenario(AdminGroup::CREATE);

        $canFreeze = $this->accessCheck('freeze');
        $params['model'] = $model;
        $params['canFreeze'] = $canFreeze;
        if ($canFreeze) {
            $model->freeze = 0;
        } else {
            $model->freeze = 1;
        }

        return $this->render('form', $params);
    }

    /**
     * 修改管理组资料
     */
    public function actionEdit()
    {
        $title = '修改管理组资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $model = AdminGroup::findOne($id);
            if ($model) {
                $model->setScenario(AdminGroup::EDIT);

                $params = [];
                $params['model'] = $model;
                $params['canFreeze'] = $this->accessCheck('freeze');
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的管理组编号', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 管理组数据保存
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $model = new AdminGroup();
                $gid = $model->getFormValue('gid', 0);
                if ($gid) {
                    $model = AdminGroup::findOne($gid);
                    if (!$model) {
                        throw new Exception('错误的管理组编号');
                    }
                    $model->setScenario(AdminGroup::EDIT);
                    $act = '修改';
                } else {
                    $act = '新增';
                    $model->setScenario(AdminGroup::CREATE);
                }
                if ($model->load($request->post())) {
                    $result = ActiveForm::validateAndSave($model);
                    if (!is_array($result)) {
                        if ($result) {
                            AdminIdentity::removeCache($model->gid, true);
                            $result = ResponseHelper::getSuccessMessage("管理组{$act}成功", $this->_redirectUrl);
                        } else {
                            throw new Exception("管理组{$act}失败");
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
     * 调整管理组状态
     */
    public function actionFreeze()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new AdminGroup();
                $count = $model->changeState($id, [], 'freeze');
                if ($count) {
                    AdminIdentity::removeCache($id, true);
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect('状态更新失败', $this->_redirectUrl);
                }
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
    }

    /**
     * 删除管理组
     */
    public function actionRemove()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $model = new AdminGroup();
            try {
                $count = $model->remove($cid);
                if ($count) {
                    AdminIdentity::removeCache($cid, true);
                    return ResponseHelper::sendSuccessRedirect('成功删除' . $count . '条数据', $this->_redirectUrl);
                } else {
                    throw new Exception('删除操作失败');
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                if (strpos($message, '23000') !== false) {
                    $message = '删除失败，存在依赖于该管理组的管理员';
                }
                return ResponseHelper::sendErrorRedirect($message, $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 授权处理行为
     */
    public function actionAuthorize()
    {
        try {
            $act = RequestHelper::getPost('act');
            if ($act === 'saveAuthorize') {
                list($status, $message) = AdminGroup::saveAuthorize($_POST);
                if ($status) {
                    return ResponseHelper::sendSuccessRedirect($message, $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect($message, $this->_redirectUrl);
                }
            } else {
                $id = RequestHelper::getPost('id', 0);
                if ($id) {
                    list($adminGroup, $authList) = AdminGroup::getAuthList($id);
                    if (is_bool($adminGroup)) {
                        return ResponseHelper::sendErrorRedirect($authList, $this->_redirectUrl);
                    } else {
                        $title = '给组「' . $adminGroup->group_name . '」授权';
                        $this->_setTitle($title);
                        $this->_addBreadcrumb($title);

                        $params = [];
                        $params['adminGroup'] = $adminGroup;
                        $params['authList'] = $authList;
                        return $this->render('authorize', $params);
                    }
                } else {
                    return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
                }
            }
        } catch (Exception $e) {
            return ResponseHelper::sendErrorRedirect($e->getMessage(), $this->_redirectUrl);
        }
    }
} 