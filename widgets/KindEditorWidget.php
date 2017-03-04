<?php

/**
 * KindEditor编辑器Widgets
 *
 * @author ChenBin
 * @version $Id:FlexiGrid.php, v2.0 2017-01-04 06:54+100 ChenBin $
 * @package app\widgets
 * @since 2017-01-08 11:19
 * @copyright 2011(C)ChenBin, All rights reserved.
 */
namespace app\widgets;

use app\assets\KindEditorAsset;
use app\helpers\UrlHelper;
use app\helpers\Validator;
use yii\base\Widget;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * KindEditor InputWidget.
 */
class KindEditorWidget extends Widget
{
    /** @var string $id */
    public $id;
    /** @var array $items KindEditor配置项. */
    public $items = array();
    /** @var array $_defaultItems KindEditor默认配置项. */
    private $_defaultItems = array();
    /** @var int $_thumbWidth 缩略图宽度 */
    private $_thumbWidth = null;
    /** @var int $_thumbHeight 缩略图高度 */
    private $_thumbHeight = null;
    /** @var bool $_allowFileManager 是否启用文件管理器 */
    private $_allowFileManager = true;
    /** @var string $_fileManagerJson 文件管理器获取文件信息的URL */
    private $_fileManagerJson;
    /** @var string $_uploadJson 设置文件管理器上传文件的URL */
    private $_uploadJson;
    /** @var string $_category 上传的文件所属的类别 */
    private $_category = null;
    /** @var bool 是否创建创建编辑器 */
    public $createEditor = true;
    /** @var bool 是否创建缩略图 */
    public $createThumb = true;
    /** @var bool 是否自动创建上传子目录(按年月) */
    public $autoCreateSubDirectory = true;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->_defaultItems = [
            'width' => '100%',
            'height' => '500',
            'resizeType' => '2',
            'afterBlur' => 'onAfterBlur',
            'allowFileManager' => 'true',
            'fileManagerJson' => Url::toRoute('fileManager/'),
            'uploadJson' => Url::toRoute('fileManager/upload'),
            'filePostName' => 'uploadFile',
            'createThumb' => true,
            'autoCreateSubDirectory' => true,
            'extraFileUploadParams' => [
                'sid' => session_id()
            ]
        ];
        $request = Yii::$app->getRequest();
        if ($request->enableCsrfValidation) {
            $this->_defaultItems['extraFileUploadParams'][$request->csrfParam] = $request->getCsrfToken();
        }
        KindEditorAsset::register($this->getView());
    }

    /**
     * 设置缩略图宽度．
     *
     * @param int $thumbWidth
     * @return object $this
     */
    public function setThumbWidth($thumbWidth)
    {
        if (Validator::isInt($thumbWidth)) {
            $this->_thumbWidth = intval($thumbWidth);
        }
        return $this;
    }

    /**
     * 设置缩略图高度.
     *
     * @param int $thumbHeight
     * @return object $this
     */
    public function setThumbHeight($thumbHeight)
    {
        if (Validator::isInt($thumbHeight)) {
            $this->_thumbHeight = intval($thumbHeight);
        }
        return $this;
    }

    /**
     * 设置是否启用文件管理器.
     *
     * @param bool $allowFileManager
     * @return object $this
     */
    public function setAllowFileManager($allowFileManager = true)
    {
        if (is_bool($allowFileManager)) {
            $this->_allowFileManager = $allowFileManager;
        }
        return $this;
    }

    /**
     * 设置文件管理器获取文件信息的URL.
     *
     * @param string $url
     * @return object $this
     */
    public function setFileManagerUrl($url)
    {
        if (is_string($url)) {
            $this->_fileManagerJson = $url;
        }
        return $this;
    }

    /**
     * 设置文件管理器上传文件的URL．
     *
     * @param string $url
     * @return object $this
     */
    public function setUploadUrl($url)
    {
        if (is_string($url)) {
            $this->_uploadJson = $url;
        }
        return $this;
    }

    /**
     * 设置上传文件所属的类别.
     *
     * @param string $category
     * @return object $this
     */
    public function setCategory($category)
    {
        if (is_string($category)) {
            $this->_category = $category;
        }
        return $this;
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        if (empty($this->_uploadJson)) {
            $this->_uploadJson = $this->_defaultItems['uploadJson'];
        }
        if (empty($this->_fileManagerJson)) {
            $this->_fileManagerJson = $this->_defaultItems['fileManagerJson'];
        }
        $urlParams = array();
        if (!empty($this->_category)) {
            $urlParams['category'] = $this->_category;
        }
        $urlParams['autoCreateSubDirectory'] = $this->autoCreateSubDirectory;
        $urlParams['createThumb'] = $this->createThumb;
        if (!empty($urlParams)) {
            $this->_uploadJson = UrlHelper::attachUrlParams($this->_uploadJson, $urlParams);
            $this->_fileManagerJson = UrlHelper::attachUrlParams($this->_fileManagerJson, $urlParams);
        }
        $this->_defaultItems['uploadJson'] = $this->_uploadJson;
        $this->_defaultItems['fileManagerJson'] = $this->_fileManagerJson;

        $urlParams = array();
        if ($this->createThumb) {
            if (is_int($this->_thumbWidth)) {
                $urlParams['thumbWidth'] = $this->_thumbWidth;
            }
            if (is_int($this->_thumbHeight)) {
                $urlParams['thumbHeight'] = $this->_thumbHeight;
            }
            $this->items['uploadImageJson'] = UrlHelper::attachUrlParams($this->_uploadJson, $urlParams);;
            $this->items['imageManagerJson'] = UrlHelper::attachUrlParams($this->_fileManagerJson, $urlParams);
        }

        $items = array_merge($this->_defaultItems, $this->items);
        if (!$this->createEditor) {
            unset($items['width'], $items['height'], $items['resizeType'], $items['afterBlur']);
        }
        $jsonString = Json::encode($items);
        if ($this->createEditor) {
            $search = array('"onAfterBlur"');
            $replace = array('function(){this.sync();}');
            $jsonString = str_replace($search, $replace, $jsonString);
            $script = 'KindEditor.ready(function(K){editor=K.create("textarea[id=' . $this->id . ']", ' . $jsonString . ')});';
        } else {
            $script = 'KindEditor.ready(function(K){editor=K.editor(' . $jsonString . ')});';
        }
        $this->getView()->registerJs($script, View::POS_END, $this->id);
    }
}