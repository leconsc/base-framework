<?php
/**
 * 系统访问的基本控制器
 *
 * @author ChenBin
 * @version $Id: Controller.php, 1.0 2016-10-13 07:22+100 ChenBin$
 * @package: tellhim.net
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\base;

use Yii;
use yii\captcha\CaptchaAction;
use yii\helpers\Url;
use yii\web\ErrorAction;

abstract class Controller extends \yii\web\Controller
{
    /** @var string $title 操作行为标题 */
    public $title;
    /** @var string $_actual_action 实际动作 */
    protected $_actual_action;
    /** @var string $redirectUrl*/
    protected $_redirectUrl;

    /**
     * 对一系列类似行为分配相同的处理方法.
     *
     * @param string $id
     * @param array $params
     * @return mixed
     */
    public function runAction($id, $params = [])
    {
        if (in_array($id, array('orderUp', 'orderDown'))) {
            $this->_actual_action = $id;
            $id = 'order';
        }
        $this->_redirectUrl = Url::toRoute('index');
        return parent::runAction($id, $params);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::className(),
            ],
            'captcha' => [
                'class' => CaptchaAction::className(),
                'padding' => 0,
                'height' => 40,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    /**
     * 设置页面标题
     *
     * @param string $title
     * @return $this
     */
    protected function _setPageTitle($title)
    {
        Yii::$app->view->title = $title;
        return $this;
    }

    /**
     * 获取页面标题
     *
     * @return mixed|string
     */
    protected function _getPageTitle()
    {
        return Yii::$app->view->title;
    }

    /**
     * 附加信息到页面标题
     * @param $value
     */
    public function appendPageTitle($value)
    {
        $this->_setPageTitle($value . ' - ' . $this->_getPageTitle());
    }

    /**
     * 添加面包屑导航
     *
     * @param string $label
     * @param string|null $url
     * @return $this self
     */
    protected function _addBreadcrumb($label, $url = null)
    {
        if (empty($label)) {
            return $this;
        }
        $item = ['label' => $label];

        if (!empty($url)) {
            $item['url'] = $url;
        }
        Yii::$app->view->params['breadcrumbs'][] = $item;
        return $this;
    }

    /**
     * 设置当前行为操作标题.
     *
     * @param string $title
     */
    protected function _setTitle($title)
    {
        $this->title = $title;
    }
}