<?php

/**
 * 文章管理
 *
 * @author ChenBin
 * @version $Id:ContentController.php, v1.0 2015-04-07 07:13 ChenBin $
 * @category app\modules\admin\controllers
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\ArrayHelper;
use app\helpers\Config;
use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\models\Category;
use app\models\Content;
use app\modules\admin\components\BackendController;
use app\widgets\ActiveForm;
use app\helpers\KEUploadHelper;
use yii\helpers\Url;
use Yii;
use Exception;

class ContentController extends BackendController
{
    /**
     * 动作调用开始时执行,预处理.
     *
     * @access protect
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '文章管理';

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
    protected function _getDependentList()
    {
        return [
            ['create,edit', ['imagelist', 'upload', 'recommend']]
        ];
    }

    /**
     * 文章管理入口
     */
    public function actionIndex()
    {
        $params = [];
        $params['page'] = RequestHelper::fetch('page', 1);
        $params['limit'] = RequestHelper::fetch('limit', 10);
        $params['searchWord'] = RequestHelper::fetch('searchWord');
        $params['published'] = RequestHelper::fetch('published');
        $params['catId'] = RequestHelper::fetch('catId');
        $params['sortName'] = RequestHelper::fetch('sortName');
        $params['sortOrder'] = RequestHelper::fetch('sortOrder');
        $params['categories'] = Category::find()->asArray()->all();
        $params['model'] = new Content();

        return $this->render('index', $params);
    }

    /**
     * 为查询列表提供Ajax数据源
     */
    public function actionGet()
    {
        try {
            RequestHelper::setDataSource(RequestHelper::POST);
            $page = RequestHelper::fetch('page', 1);
            $limit = RequestHelper::fetch('limit', 20);

            $sortName = RequestHelper::fetch('sortName', 'created_at', ['id', 'title', 'cat_id', 'ordering', 'created_at', 'published', 'modified_at']);
            $sortOrder = RequestHelper::fetch('sortOrder', 'desc', ['desc', 'asc']);
            $searchWord = RequestHelper::fetch('searchWord');
            $published = RequestHelper::fetch('published');
            $catId = RequestHelper::fetch('catId');

            $query = Content::find();


            $offset = ($page - 1) * $limit;
            $order = "$sortName $sortOrder";

            if (!empty($searchWord)) {
                $query->andFilterWhere(['like', 'title', $searchWord]);
            }
            if (Validator::isInt($published)) {
                $query->andWhere(['published' => $published]);
            }
            if (Validator::isInt($catId)) {
                $query->andWhere(['cat_id' => $catId]);
            }
            list($total, $rows) = Content::getList($query, $order, $limit, $offset);

            $params = [];
            $params['page'] = $page;
            $params['total'] = $total;
            $params['rows'] = $rows;
            $params['sortName'] = $sortName;
            $params['categories'] = Category::getItems();
            if ($sortName === 'cat_id') {
                $query->select(['cat_id', 'COUNT(id) as count'])->groupBy('cat_id');
                $params['stats'] = Content::createPairs('cat_id', 'count', $query, false);
            }
            $this->renderPartial('get', $params);
        } catch (Exception $e) {
            $response = array();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            return ResponseHelper::getJsonResponse($response);
        }
    }

    /**
     * 创建文章
     */
    public function actionCreate()
    {
        $title = '新增文章';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $params = [];
        $model = new Content();
        $model->setScenario(Content::CREATE);
        $model->published = 1;
        $model->recommend = 0;
        $model->click = 0;
        $model->ordering = $model->getMaxOrdering();
        $params['model'] = $model;
        $params['categories'] = Category::find()->asArray()->all();

        return $this->render('form', $params);
    }


    /**
     * 修改文章资料
     */
    public function actionEdit()
    {
        $title = '修改文章资料';

        $this->_setTitle($title);
        $this->_addBreadcrumb($title);

        $id = RequestHelper::get('id', 0);
        if ($id) {
            $content = Content::findOne($id);
            if ($content) {
                $content->setScenario(Content::EDIT);
                $params = array();
                $params['model'] = $content;
                $params['categories'] = Category::find()->asArray()->all();
                return $this->render('form', $params);
            } else {
                return ResponseHelper::sendErrorRedirect('无效的文章编号', $this->_redirectUrl);
            }
        } else {
            return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
        }
    }

    /**
     * 文章数据保存
     */
    public function actionSave()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            try {
                $model = new Content();
                $id = $model->getFormValue('id', 0);
                if ($id) {
                    $model = Content::findOne($id);
                    if (!$model) {
                        throw new Exception("错误的文章编号");
                    }
                    $model->setScenario(Content::EDIT);
                    $act = '修改';
                } else {
                    $act = '新增';
                    $model->setScenario(Content::CREATE);
                }
                if ($model->load($request->post())) {
                    $result = ActiveForm::validateAndSave($model);
                    if (!is_array($result)) {
                        if ($result) {
                            $result = ResponseHelper::getSuccessMessage("文章{$act}成功", $this->_redirectUrl);
                        } else {
                            throw new Exception("文章{$act}失败");
                        }
                    }
                } else {
                    throw new Exception("无效的请求");
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
     * 删除
     */
    public function actionRemove()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $model = new Content();
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
     * 调整文章状态
     */
    public function actionPublish()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new Content();
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
     * 调整推荐状态
     */
    public function actionRecommend()
    {
        $cid = RequestHelper::getPost('cid', []);
        if ($cid) {
            $id = intval($cid[0]);
            if ($id) {
                $model = new Content();
                $count = $model->changeState($id, [], 'recommend');
                if ($count) {
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect('推荐失败', $this->_redirectUrl);
                }
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
    }

    /**
     * 移动排列顺序
     */
    public function actionOrder()
    {
        try {
            $cid = RequestHelper::getPost('cid', []);
            if ($cid) {
                $id = intval($cid[0]);
                $content = Content::findOne($id);
                if ($content) {
                    $sortName = RequestHelper::fetch('sortName');
                    if ($sortName === 'cat_id') {
                        $condition = ['cat_id' => $content->cat_id];
                    } else {
                        $condition = null;
                    }
                    $content = new Content();
                    $content->changeOrder($id, 'asc', $this->_actual_action, $condition);
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    throw new Exception('错误的文章编号');
                }
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            return ResponseHelper::sendErrorRedirect($e->getMessage(), $this->_redirectUrl);
        }
    }

    /**
     * 保存排列顺序
     */
    public function actionSaveorder()
    {
        try {
            $cid = RequestHelper::getPost('cid', []);
            $ordering = RequestHelper::getPost('ordering', []);
            if ($cid && $ordering) {
                $idArray = ArrayHelper::toInteger($cid);
                $orderArray = ArrayHelper::toInteger($ordering);
                if ($idArray && $orderArray) {
                    $sortName = RequestHelper::fetch('sortName');
                    $model = new Content();
                    if ($sortName === 'cat_id') {
                        $orderList = array_combine($idArray, $orderArray);
                        $items = Content::find()->select(['id', 'cat_id'])->where(['id' => $idArray])->asArray()->all();
                        $orderData = [];
                        foreach ($items as $item) { //整理出排序数据
                            if (isset($orderList[$item['id']])) {
                                if (!isset($orderData[$item['cat_id']])) {
                                    $orderData[$item['cat_id']] = [];
                                }
                                $orderData[$item['cat_id']][$item['id']] = $orderList[$item['id']];
                            }
                        }
                        foreach ($orderData as $catId => $orderList) {
                            $idArray = array_keys($orderList);
                            $orderArray = array_values($orderList);
                            $query = Content::find()->where(['cat_id' => $catId]);
                            $model->updateOrder($idArray, $orderArray, $query);
                        }
                    } else {
                        $model->updateOrder($idArray, $orderArray);
                    }
                    return ResponseHelper::sendSuccessRedirect(null, $this->_redirectUrl);
                } else {
                    throw new Exception('无效的请求');
                }
            } else {
                throw new Exception('无效的请求');
            }
        } catch (Exception $e) {
            return ResponseHelper::sendErrorRedirect($e->getMessage(), $this->_redirectUrl);
        }
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