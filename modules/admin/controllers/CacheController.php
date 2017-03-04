<?php

/**
 * Cache管理
 *
 * @author ChenBin
 * @version $Id:CacheController.php, 1.0 2014-12-23 11:27+100 ChenBin$
 * @package: app\modules\admin\controllers
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\Cache;
use app\helpers\ResponseHelper;
use app\modules\admin\components\BackendController;
use yii\helpers\Url;
use Yii;
use Exception;

class CacheController extends BackendController
{
    /**
     * Cache清除
     */
    public function actionIndex()
    {
        $title = '缓存管理';
        $this->_setTitle($title);
        $this->_addBreadcrumb($title, Url::toRoute(('index')));

        $request = Yii::$app->request;
        if ($request->isPost) {
            $action = $request->post('action');
            $response = array();
            try {
                switch ($action) {
                    case 'clean':
                        Cache::flush();
                        break;
                    default:
                        throw new Exception('未知操作');
                        break;
                }
                $response['status'] = 'success';
                $response['message'] = '清除成功';
            } catch (\Exception $e) {
                $response['status'] = 'error';
                $response['message'] = $e->getMessage();
            }
            return ResponseHelper::getJsonResponse($response);
        } else {
            return $this->render('index');
        }
    }
}