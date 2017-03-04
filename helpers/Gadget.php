<?php

/**
 * 页面中块内容处理与显示助手
 *
 * @author chenbin
 * @version $Id:Gadget.php, 1.0 2014-09-25 17:38+100 chenbin$
 * @package: wegames
 * @since 1.0
 * @copyright 2014(C)Copyright By XiaoShiJie, All rights Reserved.
 */
final class Gadget
{
    /**
     * @var ClientScript 脚配处理对象
     */
    private $_assetManager;
    /**
     * @var string Gadget名称
     */
    private $_name;

    /** @var  string Gadget组件存放目录 */
    private $_basePath;

    /** @var  string Gadget模板存放目录 */
    private $_viewPath;

    /** @var  string 子目录 */
    private $_subDir;
    /**
     * @var array 參數
     */
    protected $_params = array();
    /**
     * @var array 模板数据
     */
    protected $_data = array();


    /**
     * 初始化相关变量
     */
    private function __construct()
    {
        $this->_assetManager = Yii::app()->getAssetManager();
    }

    /**
     * 辅助调用当前模块下的Block，用于页面布局.
     *
     * @param string 名称
     * @param array 传递的参数
     * @return string 响应结果
     */
    public static function load($name, array $params = array(),$return=false)
    {
        $gadget = self::getInstance();
        $res = $gadget->call($name, $params);
        if($return){
            return $res;
        }else{
            echo $res;
        }
    }
    /**
     * 获取静态单一实例化对象.
     *
     * @return GadgetHelper
     */
    public static function getInstance()
    {
        static $instance;
        if (!$instance instanceof self) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * 加载并渲染gadget.
     *
     * @param $name
     * @param array $params
     */
    public function call($name, array $params = array())
    {
        $this->_name = $name;
        $this->_setParams($params);
        $basePath = $this->_getBasePath();
        $gadgetFile = $basePath . $name . '.php';
        if (is_file($gadgetFile)) {
            $this->_subDir = null;
            include $gadgetFile;
        } else {
            $gadgetFile = $basePath . $name . DIRECTORY_SEPARATOR . $name . '.php';
            if (is_file($gadgetFile)) {
                $this->_subDir = $name;
                include $gadgetFile;
            }
        }
        $view = $name;
        if (isset($params['view'])) {
            $view = $params['view'];
        }
        return $this->_render($view, $this->_data, true);
    }

    /**
     * 返回指定參數信息
     */
    public function get($name, $default = null)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        return $default;
    }

    /**
     * 设置参数值
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * 设置View中使用的变量值
     *
     * @param $name
     * @param $value
     * @return object
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->_data = array_merge($this->_data, $spec);
        } else {
            $this->_data[$spec] = $value;
        }
        return $this;
    }

    /**
     * 发布JS文件.
     *
     * @param $path
     */
    public function publish($path)
    {
        $orgAssetBaseUrl = $this->_assetManager->getBaseUrl();
        $orgAssetBasePath = $this->_assetManager->getBasePath();

        $assetBasePath = AssetHelper::getAssetPath(true);
        $assetBaseUrl = AssetHelper::getAssetUrl(true);
        if ($this->_subDir !== null) {
            $gadgetAssetPath = $this->_getBasePath() . DIRECTORY_SEPARATOR . $this->_subDir . DIRECTORY_SEPARATOR . ltrim($path);
            $assetBasePath .= DIRECTORY_SEPARATOR . $this->_subDir;
            $assetBaseUrl .= DIRECTORY_SEPARATOR . $this->_subDir;
        }else{
            $gadgetAssetPath = $this->_getBasePath() . ltrim($path);
        }
        if (!is_dir($assetBasePath)) {
            mkdir($assetBasePath, $this->_assetManager->newDirMode, true);
        }

        $this->_assetManager->setBaseUrl($assetBaseUrl);
        $this->_assetManager->setBasePath($assetBasePath);
        $assetUrl = $this->_assetManager->publish($gadgetAssetPath, false);
        $this->_assetManager->setBaseUrl($orgAssetBaseUrl);
        $this->_assetManager->setBasePath($orgAssetBasePath);
        return $assetUrl;
    }

    /**
     * 設置參數
     */
    private function _setParams($params)
    {
        if (is_array($params)) {
            $this->_params = $params;
        }
        return $this;
    }

    /**
     * 处理模板.
     *
     * @param $view
     * @param $data
     * @param bool $return
     * @return mixed|string
     */
    private function _render($view, $data, $return = false)
    {
        if (($viewFile = $this->_getViewFile($view)) !== false) {
            $output = $this->_renderFile($viewFile, $data, true);
            if ($return) {
                return $output;
            } else {
                echo $output;
            }
        }
    }

    /**
     * 渲染视图文件.
     *
     * @param $viewFile
     * @param null $data
     * @param bool $return
     * @return mixed|string
     */
    private function _renderFile($viewFile, $data = null, $return = false)
    {
        if (($renderer = Yii::app()->getViewRenderer()) !== null && $renderer->fileExtension === '.' . CFileHelper::getExtension($viewFile)) {
            $content = $renderer->renderFile($this, $viewFile, $data, $return);
        } else {
            $content = $this->_renderInternal($viewFile, $data, $return);
        }
        return $content;
    }

    /**
     * 内嵌视图文件渲染.
     *
     * @param $_viewFile_
     * @param null $_data_
     * @param bool $_return_
     * @return string
     */
    private function _renderInternal($_viewFile_, $_data_ = null, $_return_ = false)
    {
        // we use special variable names here to avoid conflict when extracting data
        if (is_array($_data_)) {
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        } else {
            $data = $_data_;
        }
        if ($_return_) {
            ob_start();
            ob_implicit_flush(false);
            require($_viewFile_);
            return ob_get_clean();
        } else
            require($_viewFile_);
    }

    /**
     * 获取View文件实际存放路径
     *
     * @param $view
     * @return bool|string
     */
    private function _getViewFile($view)
    {
        $viewPath = $this->_getViewBasePath();
        if ($this->_subDir !== null) {
            $viewPath .= $this->_subDir . '/';
        }
        $viewFile = $viewPath . $view . '.php';
        if (is_file($viewFile)) {
            return $viewFile;
        }
        return false;
    }

    /**
     * 获取Gadget主文件存放目录
     *
     * @param bool $withGadgetPath
     * @return string
     */
    private function _getBasePath($withGadgetPath = true)
    {
        if ($this->_basePath === null) {
            $basePath = Yii::getPathOfAlias('application');
            list($isGameModule, $moduleName) = Helper::isGameModule();
            if ($isGameModule) {
                Yii::app()->getModule($moduleName);
                $basePath = Yii::getPathOfAlias('application.games.' . $moduleName);
            } else {
                if ($moduleName) {
                    $basePath = Yii::getPathOfAlias('application.modules.' . $moduleName);
                }
            }
            $this->_basePath = $basePath . DIRECTORY_SEPARATOR;
        }
        if ($withGadgetPath) {
            return $this->_basePath . 'gadget/';
        } else {
            return $this->_basePath;
        }
    }

    /**
     * 获取Gadget模板文件存放目录.
     *
     * @return string
     */
    private function _getViewBasePath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->_getBasePath(false) . 'views/gadget/';
        }
        return $this->_viewPath;
    }

    /**
     * 呼叫当前控制器中相关方法.
     *
     * @param $method
     * @param array $args
     * @return bool|mixed
     */
    public function __call($method, array $args)
    {
        if (isset(Yii::app()->controller) && Yii::app()->controller instanceof CController) {
            if (method_exists(Yii::app()->controller, $method)) {
                return call_user_func_array(array(Yii::app()->controller, $method), $args);
            }
        }
        return false;
    }
}