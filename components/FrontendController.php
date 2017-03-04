<?php
/**
 * 前端控制器基础抽象类
 *
 * @author ChenBin
 * @version $Id: FrontendController.php, 1.0 2016-12-04 21:50+100 ChenBin$
 * @package: tellhim.net
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\components;


use yii\filters\AccessControl;
use yii\filters\VerbFilter;

abstract class FrontendController extends \app\base\Controller
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
            if (is_array($this->_authenticated_user_actions) && count($this->_authenticated_user_actions)) {
                $this->_rules[] = [
                    'actions' => $this->_authenticated_user_actions,
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
            if (!count($this->_rules)) {
                $rule = [
                    'allow' => true,
                    'roles' => ['@'],
                ];
                if (isset($access['only'])) {
                    $rule['actions'] = $access['only'];
                }
                $this->_rules[] = $rule;
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
}