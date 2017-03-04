<?php
/**
 * 资讯展示
 *
 * @author ChenBin
 * @version $Id:ContentController.php, v1.0 2017-01-18 09:45 ChenBin $
 * @package app\controllers
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\controllers;


use app\components\FrontendController;
use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\helpers\Validator;
use app\models\Category;
use app\models\Content;
use yii\helpers\Url;

class ContentController extends FrontendController
{
    const PAGE_LIMIT = 30;
    /** @var bool 禁用访问控制 */
    protected $_enableAccessControl = false;

    /**
     * 动作调用开始时执行,预处理
     *
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $title = '资讯动态';

            $this->_setTitle($title);
            $this->_addBreadcrumb($title, Url::toRoute(('index')));

            return true;
        }
        return false;
    }

    /**
     * 显示资讯动态列表.
     *
     * @return string
     */
    public function actionIndex()
    {
        $catId = RequestHelper::get('cat_id');
        $searchWorld = RequestHelper::get('k');

        $query = Content::find()
                 ->select('id, title, title_color')
                 ->where(['published'=>1])
                 ->orderBy(['ordering' => SORT_DESC]);

        if (Validator::isInt($catId)) {
            $catId = intval($catId);
            $query->andWhere(['cat_id' => $catId]);
        }else{
            $catId = null;
        }
        if (!empty($searchWorld)) {
            $query->andFilterWhere(['or', ['like', 'title', $searchWorld], ['like', 'content', $searchWorld]]);
        }
        $categories = Category::getItems();
        list($pagination, $contents) = Content::getPageItems($query, self::PAGE_LIMIT);

        return $this->render('index', [
            'contents' => $contents,
            'categories' => $categories,
            'pagination' => $pagination,
            'catId' => $catId,
            'searchWorld' => $searchWorld
        ]);
    }
    /**
     * 显示资讯信息.
     *
     * @return string|\yii\web\Response
     */
    public function actionView()
    {
        $id = RequestHelper::get('id', 0);
        if ($id) {
            $content = Content::findOne($id);
            if ($content) {
                $title = '查看记录';
                Content::click($id);

                $this->_setTitle($title);
                $this->_addBreadcrumb($title);
                return $this->render('view', ['content' => $content]);
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', $this->_redirectUrl);
    }
}