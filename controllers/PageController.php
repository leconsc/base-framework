<?php
/**
 * 页面展示功能
 *
 * @author ChenBin
 * @version $Id:PageController.php, v1.0 2017-01-18 09:44 ChenBin $
 * @package app\controllers
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\controllers;


use app\components\FrontendController;
use app\helpers\RequestHelper;
use app\helpers\ResponseHelper;
use app\models\Page;
use yii\helpers\Url;

class PageController extends FrontendController
{
    protected $_enableAccessControl=false;
    /**
     * 显示页面信息.
     *
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $alias = RequestHelper::get('alias');
        if ($alias) {
            $page = Page::find()->where(['alias'=>$alias, 'published'=>1])->one();
            if ($page) {
                return $this->render('index', ['page' => $page]);
            }
        }
        return ResponseHelper::sendErrorRedirect('无效的请求', Url::toRoute(['site/index']));
    }
}