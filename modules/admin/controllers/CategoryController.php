<?php

/**
 * 文章分类管理
 *
 * @author chenbin
 * @version $Id:CategoryController.php, v1.0 2015-04-07 07:13 chenbin $
 * @category zpqz114.com
 * @since 1.0
 * @copyright 2015(C)Copyright By Chenbin, all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\models\Category;
use app\modules\admin\authorization\Operation;
use app\modules\admin\components\BackendController;
use app\widgets\ActiveForm;
use yii\helpers\Url;
use Yii;
use Exception;

class CategoryController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '文章分类管理';

            $this->_setTitle($title);
            $this->_addBreadcrumb($title, Url::toRoute(('index')));

            return true;
        }
        return false;
    }

    /**
     * 文章分类管理入口
     */
    public function actionIndex()
    {
        $params = array();
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 10);
        $params['model'] = new Category();
        $params['enabledEdit'] = $this->accessCheck(Operation::O_EDIT);
        $params['enabledCreate'] = $this->accessCheck(Operation::O_CREATE);
        $params['enabledRemove'] = $this->accessCheck(Operation::O_REMOVE);
        return $this->render('index', $params);
    }

    /**
     * 为查询列表提供Ajax数据源
     */
    public function actionGet()
    {
        try {
            $page = RequestHelper::fetch('page', 1);
            $parent = RequestHelper::get('id', 0);

            if ($parent > 0) {
                $parent = intval($parent);
                $start = $limit = null;
            } else {
                $parent = 0;
                $limit = RequestHelper::fetch('rows', 10);
                $start = (($page - 1) * $limit);
            }
            $query = Category::find()
                ->alias('c')
                ->select('c.*, count(sc.id) as sub_count')
                ->leftJoin('category as sc', 'sc.parent=c.id')
                ->where(['c.parent' => $parent])
                ->groupBy('c.id')
                ->orderBy('c.parent, c.ordering')
                ->offset($start)
                ->limit($limit)
                ->asArray();
            $params = array();
            $params['page'] = $page;
            $params['total'] = $query->count();
            $params['rows'] = $query->all();
            $params['parent'] = $parent;
            $params['enabledEdit'] = $this->accessCheck(Operation::O_EDIT);
            return $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }

    /**
     * 创建文章分类
     */
    public function actionCreate()
    {
        $title = '新增文章分类';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);
        $parent = RequestHelper::get('parent', 0);

        $params = array();
        $model = new Category();
        $model->setScenario(Category::CREATE);
        $model->published = 1;
        $model->parent = $parent;
        $model->ordering = $model->getMaxOrdering();
        $params['model'] = $model;
        $params['categories'] = Category::find()->asArray()->all();

        return $this->render('form', $params);
    }


    /**
     * 修改文章分类资料
     */
    public function actionEdit($id)
    {
        $title = '修改文章分类资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $category = Category::findOne($id);
            if ($category) {
                $category->setScenario(Category::EDIT);
                $params = array();
                $params['model'] = $category;
                $params['categories'] = Category::find()->asArray()->all();
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的文章分类编号', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 文章分类数据保存
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $model = new Category();
                $id = $model->getFormValue('id', 0);
                if ($id) {
                    $model = Category::findOne($id);
                    if (!$model) {
                        throw new Exception('错误的分类编号');
                    }
                    $model->setScenario(Category::EDIT);
                    $act = '修改';
                } else {
                    $act = '新增';
                    $model->setScenario(Category::CREATE);
                }
                if ($model->load($request->post())) {
                    $result = ActiveForm::validateAndSave($model);
                    if (!is_array($result)) {
                        if ($result) {
                            $result = ResponseHelper::getSuccessMessage("文章分类{$act}成功", Url::toRoute('index'));
                        } else {
                            throw new Exception("文章分类{$act}失败");
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
     * 移动排列顺序
     */
    public function actionOrder()
    {
        $response = array();
        try {
            $id = RequestHelper::getPost('id', 0);
            if ($id) {
                $parent = RequestHelper::getPost('parent', 0);
                if (Validator::isInt($parent)) {
                    $condition = ['parent' => $parent];
                } else {
                    $condition = null;
                }
                $category = new Category();
                $category->changeOrder($id, 'asc', $this->_actual_action, $condition);
                $response['status'] = 'success';
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return ResponseHelper::getJsonResponse($response);
    }

    /**
     * 保存排列顺序
     */
    public function actionSaveOrder()
    {
        $response = array();
        try {
            $orderData = RequestHelper::getPost('orderData', []);
            if ($orderData) {
                $model = new Category();
                foreach ($orderData as $parent => $orderList) {
                    $idArray = array_keys($orderList);
                    $orderArray = array_values($orderList);
                    $parent = intval($parent);
                    $query = Category::find()->where(['parent' => $parent]);
                    $model->updateOrder($idArray, $orderArray, $query);
                }
                $response['status'] = 'success';
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return ResponseHelper::getJsonResponse($response);
    }

    /**
     * 删除
     */
    public function actionRemove()
    {
        $response = array();
        try {
            $cid = RequestHelper::get('cid', []);
            if ($cid) {
                $model = new Category();
                $count = $model->remove($cid);
                if ($count) {
                    $response['status'] = 'success';
                    $response['message'] = '成功删除' . $count . '条数据';
                } else {
                    throw new Exception('删除操作失败');
                }
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return ResponseHelper::getJsonResponse($response);
    }

    /**
     * 调整文章分类状态
     */
    public function actionPublish()
    {
        $response = array();
        try {
            $id = RequestHelper::get('id', 0);
            if ($id) {
                $model = new Category();
                $count = $model->changeState($id);
                if ($count) {
                    $response['status'] = 'success';
                } else {
                    throw new Exception('状态更新失败');
                }
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return ResponseHelper::getJsonResponse($response);
    }
}