<?php

/**
 * 页面管理
 *
 * @author ChenBin
 * @version $Id:PageController.php, v1.0 2015-04-07 07:13 ChenBin $
 * @category app\modules\admin\controllers
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\Config;
use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\models\Page;
use app\modules\admin\components\BackendController;
use app\widgets\ActiveForm;
use app\helpers\KEUploadHelper;
use yii\helpers\Url;
use Yii;
use Exception;

class PageController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '页面管理';

            $this->_setTitle($title);
            $this->_addBreadcrumb($title, Url::toRoute(('index')));

            return true;
        }
        return false;
    }
    /**
     * 权限依赖表定义.
     *
     * @return array
     */
    protected function _getDependentList(){
        return [
            ['create,edit', ['imagelist', 'upload']]
        ];
    }
    /**
     * 页面管理入口
     */
    public function actionIndex()
    {
        $params = [];
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 10);
        $params['searchWord'] = RequestHelper::fetch('searchWord');
        $params['published'] = RequestHelper::fetch('published');
        $params['sortName'] = RequestHelper::fetch('sortName');
        $params['sortOrder'] = RequestHelper::fetch('sortOrder');

        $params['model'] = new Page();

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

            $sortName = RequestHelper::fetch('sortName', 'created_at', ['id', 'title', 'alias', 'created_at', 'published', 'modified_at']);
            $sortOrder = RequestHelper::fetch('sortOrder', 'desc', ['desc', 'asc']);
            $searchWord = RequestHelper::fetch('searchWord');
            $published = RequestHelper::fetch('published');

            $query = Page::find();


            $offset = ($page - 1) * $limit;
            $order = "$sortName $sortOrder";

            if (!empty($searchWord)) {
                $query->andFilterWhere(['or', ['like', 'alias', $searchWord], ['like', 'title', $searchWord]]);
            }
            if (Validator::isInt($published)) {
                $query->andFilterWhere(['published' => $published]);
            }
            list($total, $rows) = Page::getList($query, $order, $limit, $offset);

            $params = [];
            $params['page'] = $page;
            $params['total'] = $total;
            $params['rows'] = $rows;
            $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }

    /**
     * 创建页面
     */
    public function actionCreate()
    {
        $title = '新增页面';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $params = [];
        $model = new Page();
        $model->setScenario(Page::CREATE);
        $model->published = 1;
        $params['model'] = $model;

        return $this->render('form', $params);
    }


    /**
     * 修改页面资料
     */
    public function actionEdit($id)
    {
        $title = '修改页面资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $page = Page::findOne($id);
            if ($page) {
                $page->setScenario(Page::EDIT);
                $params = array();
                $params['model'] = $page;
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的页面编号', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 页面数据保存
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $model = new Page();
                $id = $model->getFormValue('id', 0);
                if ($id) {
                    $model = Page::findOne($id);
                    if (!$model) {
                        throw new Exception('错误的页面编号');
                    }
                    $model->setScenario(Page::EDIT);
                    $act = '修改';
                } else {
                    $act = '新增';
                    $model->setScenario(Page::CREATE);
                }
                if ($model->load($request->post())) {
                    $result = ActiveForm::validateAndSave($model);
                    if (!is_array($result)) {
                        if ($result) {
                            $result = ResponseHelper::getSuccessMessage("页面{$act}成功", $this->_redirectUrl);
                        } else {
                            throw new Exception("页面{$act}失败");
                        }
                    }
                } else {
                    throw new Exception('无效的请求');
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
     * 删除
     */
    public function actionRemove()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $model = new Page();
            $count = $model->remove($cid);
            if ($count) {
                return ResponseHelper::sendSuccessRedirect('成功删除' . $count . '条数据', $this->_redirectUrl);
            } else {
                return ResponseHelper::sendErrorRedirect('删除操作失败', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 调整页面状态
     */
    public function actionPublish()
    {
        $cid = RequestHelper::getPost('cid', []);

        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new Page();
                $count = $model->changeState($id);
                if ($count) {
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect('状态更新失败', $this->_redirectUrl);
                }
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
    }
    /**
     * 显示文件及文件夹列表
     */
    public function actionImagelist()
    {
        $uploadHelper = new KEUploadHelper(Config::get('upload'));
        $uploadHelper->createList();
    }

    /**
     * 处理文件上传.
     */
    public function actionUpload()
    {
        $uploadHelper = new KEUploadHelper(Config::get('upload'));
        $uploadHelper->upload();
    }
}