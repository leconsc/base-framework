<?php
/**
 *
 *  后台控制器基础控制类
 *
 * @author ChenBin
 * @version $Id: BackendController.php, 1.0 2016-12-04 21:52+100 ChenBin$
 * @package: app\module\admin\components
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\modules\admin\components;


use app\helpers\ArrayHelper;
use app\modules\admin\authorization\AuthItem;
use app\modules\admin\authorization\Operation;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\base\Controller;
use Yii;
use yii\helpers\Url;

abstract class BackendController extends Controller
{
    /** @var boolean 是否啟用訪問控制 */
    protected $_enableAccessControl = true;
    protected $_only = [];
    protected $_except = [];
    protected $_authenticated_user_actions = [];
    protected $_visitor_actions = [];
    protected $_rules = [];
    protected $_verbs_actions = [];
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        if ($this->_enableAccessControl) {
            $access = [
                'class' => AccessControl::className()
            ];
            if (is_array($this->_only) && count($this->_only)) {
                $access['only'] = $this->_only;
            }
            if (is_array($this->_except) && count($this->_except)) {
                $access['except'] = $this->_except;
            }
            if (!is_array($this->_rules)) {
                $this->_rules = [];
            }
            $actions = $this->_getAllowActions();
            if (!empty($actions)) {
                $this->_rules[] = [
                    'actions' => $actions,
                    'allow' => true,
                    'roles' => ['@'],
                ];
            }
            if (is_array($this->_visitor_actions) && count($this->_visitor_actions)) {
                $this->_rules[] = [
                    'actions' => $this->_visitor_actions,
                    'allow' => true,
                    'roles' => ['?'],
                ];
            }
            $access['rules'] = $this->_rules;
            $behaviorsConfig['access'] = $access;
            if (is_array($this->_verbs_actions) && count($this->_verbs_actions)) {
                $behaviorsConfig['verbs'] = [
                    'class' => VerbFilter::className(),
                    'actions' => $this->_verbs_actions
                ];
            }
        } else {
            $behaviorsConfig = [];
        }
        return $behaviorsConfig;
    }

    /**
     * 获取允许的动作
     *
     * @param null|string $action
     * @param null|string $controller
     * @param null|string $module
     * @return array|bool
     */
    protected function _getAllowActions($action = null, $controller = null, $module = null)
    {
        if (!Yii::$app->user->isGuest) {
            $actions = $this->_authenticated_user_actions;
            //取得有效的动作名称
            if (empty($action) || !is_string($action)) {
                $action = $this->action->id;
            }
            //取得有效的控制器名称
            if (empty($controller) || !is_string($controller)) {
                $controller = $this->id;
            }

            if ($module) {
                $resource = $module . '.' . $controller;
            } else {
                $resource = $controller;
            }
            /** @var \app\modules\admin\services\AdminIdentity $identity */
            $identity = Yii::$app->user->identity;
            $permissions = $identity->permission;
            $authItems = array();
            if (is_array($permissions)) {
                $authItems = $permissions;
            } else {
                if (is_string($permissions)
                    && !strcasecmp($permissions, AuthItem::FULL_PERMISSIONS)
                ) {
                    $authItems = AuthItem::getAuthItems($identity->roleType);
                }
            }
            //  var_dump($identity);die();
            //以上对权限与控制器动作进行映射转换
            if (!empty($authItems)) {
                $controller = strtolower($controller);
                $resource = strtolower($resource);
                $authItems = array_change_key_case($authItems);

                $canUsePermissions = array();
                if (isset($authItems[$resource])) {
                    $canUsePermissions = $authItems[$resource];
                } elseif (isset($authItems[$controller])) {
                    $canUsePermissions = $authItems[$controller];
                }
                //常见依赖规则配置
                foreach ($canUsePermissions as $action => $status) {
                    $actions[] = $action;
                    switch ($action) {
                        case Operation::O_VIEW:
                            $actions[] = 'index';
                            $actions[] = 'get';
                            $actions[] = 'order';
                            $actions[] = 'saveorder';
                            break;
                        case Operation::O_EDIT:
                        case Operation::O_CREATE:
                            $actions[] = 'save';
                            $actions[] = 'publish';
                            break;
                    }
                }
            }
            //添加与某个action依赖的其它actions
            if (!strcasecmp($controller, $this->id)) {
                if (method_exists($this, '_getDependentList')) {
                    $dependentDefinitionList = $this->_getDependentList();
                    if (!empty($dependentDefinitionList)) {
                        $dependentActions = array();
                        foreach ($dependentDefinitionList as $dependentDefinitionItem) {
                            list($actionList, $dependentActionList) = $dependentDefinitionItem;

                            $actionListArray = ArrayHelper::fromString($actionList);
                            foreach ($actionListArray as $action) {
                                if (in_array(strtolower($action), $actions)) {
                                    $dependentActions = array_merge($dependentActions, $dependentActionList);
                                }
                            }
                        }
                        $actions = array_merge($actions, $dependentActions);
                        unset($dependentActions);
                    }
                }
            }
            return array_unique($actions);
        }
        return false;
    }

    /**
     * 检查操作权限.
     *
     * @access public
     * @param string $action 动作
     * @param string $controller 所属控制器
     * @return boolean
     */
    public function accessCheck($action, $controller = null, $module = null)
    {
        $result = false;
        if (empty($action)) {
            $action = Operation::O_VIEW;
        }
        if ($actions = $this->_getAllowActions($action, $controller, $module)) {
            $result = in_array($action, $actions);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }
}