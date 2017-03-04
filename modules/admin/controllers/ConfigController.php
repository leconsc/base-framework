<?php
/**
 * 系统配置入口页面(登入)
 *
 * @author ChenBin
 * @version $Id:ConfigController.php, v1.0 2013-08-28 21:57+100 ChenBin $
 * @package app\modules\admin\controllers
 * @since 1.0
 * @copyright 2015(C)Ayla,all rights reserved.
 */
namespace app\modules\admin\controllers;

use app\helpers\Config;
use app\components\SystemConfig;
use app\helpers\ResponseHelper;
use app\models\Configuration;
use app\modules\admin\components\BackendController;
use Exception;
use yii\helpers\Url;
use Yii;

class ConfigController extends BackendController
{
    /**
     * 配置显示与更改
     */
    public function actionIndex()
    {
        try {
            $canModify = $this->accessCheck('edit');
            if (!Yii::$app->request->isPost) {
                $this->_addBreadcrumb('系统配置');

                $params = array();
                $params['configItems'] = SystemConfig::getConfigItems();
                $params['canModify'] = $canModify;

                return $this->render('index', $params);
            } else {
                if ($canModify) {
                    Configuration::store($_POST);
                    Config::clean();
                    return ResponseHelper::sendSuccessRedirect('配置修改成功', $this->_redirectUrl);
                } else {
                    return ResponseHelper::sendErrorRedirect('未经许可的授权操作', Url::toRoute('default/index'));
                }
            }
        } catch (Exception $e) {
            return ResponseHelper::sendErrorRedirect($e->getMessage(), Url::toRoute('default/index'));
        }
    }
}