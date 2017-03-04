<?php

/**
 * FlexiGrid表格创建Widgets
 *
 * @author ChenBin
 * @version $Id:FlexiGrid.php, v2.0 2017-01-04 06:54+100 ChenBin $
 * @package app\widgets
 * @since 2017-01-04 06:54
 * @copyright 2011(C)ChenBin, All rights reserved.
 */
namespace app\widgets;

use app\base\Model;
use yii\base\Controller;
use yii\base\Widget;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

class FlexiGrid extends Widget
{
    /** @var array 列模型 */
    private $_colModel = [];
    /** @var array 按钮结构数组 */
    private $_buttons = [];
    /** @var array 搜索项结构数组 */
    private $_searchItems = [];
    /** @var boolean */
    private $_toggleBox = false;
    /** @var array */
    private $_postData = [];
    /** @var string 放置表格的样式 */
    public $containerClass;
    /** @var string 获取数据的URL地址 */
    public $url;
    /** @var string 返回数据的格式类型 */
    public $dataType = 'json';
    /** @var string 搜索按钮Class */
    public $searchButtonClass = 'btn btn-warning';
    /** @var string 搜索文本 */
    public $searchText = "搜索";
    /** @var boolean 是否显示搜索条 */
    public $showSearchBar = true;
    /** @var string 设置页数指示牌文字 * */
    public $pageStat = "显示：从{from}条 到 {to}条，共计 {total}条";
    /** @var string processing msg */
    public $processingMsg = "处理中，请稍候……";
    /** @var string show message if data not found */
    public $noFoundDataMsg = "沒有找到任何数据";
    /** @var string 请求数据出现连接错误时 */
    public $errorMsg = "数据获取错误";
    /** @var string 快速搜索 */
    public $quickSearchText = "快速搜索";
    /** @var string 清除搜索按钮Class */
    public $clearButtonClass = 'btn btn-info';
    /** @var string 清除搜索按钮文字 */
    public $clearText = "清除搜索";
    /** @var string 页码显示文本结构 */
    public $pageText = "第{currentPage}页，共计{totalPage}页";
    /** @var string 默认排序列 */
    public $sortName;
    /** @var string 默认排序方式 */
    public $sortOrder;
    /** @var string 刷新按钮文字 */
    public $refreshText = "刷新";
    /** @var boolean 是否让数据分页 */
    public $usePager = true;
    /** @var string 表格标题 */
    public $title;
    /** @var bool 自动调整列宽 */
    public $fitColumns = false;
    /** @var bool 是否显示行号 */
    public $rowNumbers = false;
    /** @var boolean 是否使用每页显示项选择器 */
    public $useLimit = true;
    /** @var int 每页显示数量 */
    public $limit = 100;
    /** @var array 分页数目列表定义 */
    public $limitOptions = [10, 15, 20, 25, 50, 100];
    /** @var boolean */
    public $showToggleBtn = true;
    /** @var boolean */
    public $showTableToggleBtn = true;
    /** @var int|string 表格宽度 */
    public $width = 'auto';
    /** @var int|string 表格高度 */
    public $height = "auto";
    /** @var array toggleBox显示范围 */
    private $_scope = ['edit' => true, 'remove' => true];
    /** @var int 起始頁 */
    public $startPage = 1;
    /** @var string */
    public $searchWord = "";
    /** @var string */
    public $formName = 'flexiGridForm';
    /** @var string form action */
    public $action;
    /** @var object model */
    public $model;
    /** @var boolean */
    public $forceAddForm = true;
    /** @var boolean */
    public $forceShowOperateColumn = false;
    /** @var array action与控制器之间的映射关系 */
    public $actionMaps = [];
    /** @var bool 禁用清除搜索按钮 */
    public $disableClearSearchButton = false;
    /** @var array 附加的按钮 */
    private $_attachButtons = [];

    /**
     * Initializes the flexigrid view.
     */
    public function init()
    {
        parent::init();
        $view = $this->getView();
        FlexiGridAsset::register($view);
        $request = Yii::$app->getRequest();
        if ($request->enableCsrfValidation) {
            $this->setPostData([$request->csrfParam => $request->getCsrfToken()]);
        }
    }

    /**
     * 添加参数.
     *
     * @param array $postData Post数据项
     * @return $this
     */
    public function setPostData(array $postData)
    {
        foreach ($postData as $name => $value) {
            $this->_postData[] = ['name' => $name, 'value' => $value];
        }
        return $this;
    }

    /**
     * 通过数组设置列模型
     * @access public
     * @param array $colModels
     * @return object self Object
     */
    public function setColModels(array $colModels)
    {
        $validModel = false;
        if ($this->model instanceof Model) {
            $validModel = true;
        }
        foreach ($colModels as $colModel) {
            $i = 0;
            $columnTitle = null;
            if (!$validModel) {
                $columnTitle = isset($colModel['columnTitle']) ? $colModel['columnTitle'] : (isset($colModel[$i]) ? $colModel[$i++] : null);
                if (is_null($columnTitle)) {
                    continue;
                }
            }
            $columnName = isset($colModel['columnName']) ? $colModel['columnName'] : (isset($colModel[$i]) ? $colModel[$i++] : null);
            if (is_null($columnName)) {
                continue;
            }
            if ($validModel) {
                if ($columnName === 'toggleBox') {
                    $columnTitle = Html::checkbox('toggle');
                } else if ($columnName === 'operate') {
                    $columnTitle = '操作';
                } else {
                    $columnTitle = $this->model->getAttributeLabel($columnName);
                }
            }
            $width = intval(isset($colModel['width']) ? $colModel['width'] : (isset($colModel[$i]) ? $colModel[$i++] : 100));
            $sortable = isset($colModel['sortable']) ? $colModel['sortable'] : (isset($colModel[$i]) ? $colModel[$i++] : true);
            $hide = isset($colModel['hide']) ? $colModel['hide'] : (isset($colModel[$i]) ? $colModel[$i++] : false);
            $enableToggle = isset($colModel['enableToggle']) ? $colModel['enableToggle'] : (isset($colModel[$i]) ? $colModel[$i++] : true);
            $showFullTitle = isset($colModel['showFullTitle']) ? $colModel['showFullTitle'] : (isset($colModel[$i]) ? $colModel[$i++] : false);
            $valid = isset($colModel['valid']) ? $colModel['valid'] : (isset($colModel[$i]) ? $colModel[$i++] : true);
            $align = isset($colModel['align']) ? $colModel['align'] : (isset($colModel[$i]) ? $colModel[$i++] : 'left');
            $this->addColModel($columnTitle, $columnName, $width, $sortable, $hide, $enableToggle, $showFullTitle, $valid, $align);
        }
        return $this;
    }

    /**
     *
     * 添加列模型
     *
     * @access public
     * @param string $columnTitle 列标题
     * @param string $columnName 列名称
     * @param integer $width 整型
     * @param boolean $sortable 列是否支持排序
     * @param boolean $valid 是否有效
     * @param string $align 对齐方式
     * @return object self Object
     */
    public function addColModel($columnTitle, $columnName, $width = 100, $sortable = true, $hide = false, $enableToggle = true, $showFullTitle = false, $valid = true, $align = "left")
    {
        if ($valid) {
            if ($columnName == "toggleBox") {
                $this->_toggleBox = false;
                foreach ($this->_scope as $action => $value) {
                    if ($this->_accessCheck($action)) {
                        $this->_toggleBox = true;
                        break;
                    }
                }
                if (!$this->_toggleBox) {
                    return $this;
                }
            }
            if (!strcasecmp($columnName, 'operate') && !$this->forceShowOperateColumn && !$this->_toggleBox) {
                return $this;
            }
            $col = array();
            $col['display'] = $columnTitle;
            $col['name'] = $columnName;
            $col['width'] = intval($width);
            $col['sortable'] = (boolean)$sortable;
            $col['hide'] = (boolean)$hide;
            $col['enableToggle'] = (boolean)$enableToggle;
            $col['showFullTitle'] = (boolean)$showFullTitle;
            $col['align'] = $align;

            $this->_colModel[] = $col;
            if (is_int($this->width)) {
                $this->width += $width;
            }
        }
        return $this;
    }

    /**
     * 设置accessCheck的范围
     *
     * @param array $scopes
     */
    public function setScope(array $scopes)
    {
        foreach ($scopes as $scope) {
            $firstChar = substr($scope, 0, 1);
            if ($firstChar === '-') {
                $this->removeScope(substr($scope, 1));
            } elseif ($firstChar === '+') {
                $this->addScope(substr($scope, 1));
            } else {
                $this->addScope($scope);
            }
        }
    }

    /**
     *
     * 添加支持行选择的action范围
     *
     * @access public
     * @param string $scope
     * @return $this
     */
    public function addScope($scope)
    {
        $this->_scope[$scope] = true;
        return $this;
    }

    /**
     * 移除范围.
     *
     * @access public
     * @param string $scope
     * @return $this
     */
    public function removeScope($scope)
    {
        if (isset($this->_scope[$scope])) {
            unset($this->_scope[$scope]);
        }
        return $this;
    }

    /**
     * 配置Buttons信息
     * @access public
     * @param array $buttons
     * @return object self Object
     */
    public function setButtons(array $buttons)
    {
        foreach ($buttons as $button) {
            if (!is_array($button) && $button === '|') {
                $this->addSeparator();
                continue;
            }
            $i = 0;
            $title = isset($button['title']) ? $button['title'] : (isset($button[$i]) ? $button[$i++] : null);
            if (is_null($title)) {
                continue;
            }
            $name = isset($button['name']) ? $button['name'] : (isset($button[$i]) ? $button[$i++] : null);
            if (is_null($name)) {
                continue;
            }
            $description = isset($button['description']) ? $button['description'] : (isset($button[$i]) ? $button[$i++] : null);
            $class = isset($button['class']) ? $button['class'] : (isset($button[$i]) ? $button[$i++] : null);
            $event = isset($button['event']) ? $button['event'] : (isset($button[$i]) ? $button[$i++] : 'handle');
            $valid = isset($button['valid']) ? $button['valid'] : (isset($button[$i]) ? $button[$i++] : true);

            $this->addButton($title, $name, $description, $class, $event, $valid);
        }
        return $this;
    }

    /**
     * 添加操作按钮
     *
     * @access public
     * @param string $title 按钮文字
     * @param string $name 按钮名称
     * @param string $description 按钮描述
     * @param string $class 按钮style class
     * @param string $event 按钮事件处理function
     * @param boolean $valid 是否有效
     * @return object self Object
     */
    public function addButton($title, $name, $description = null, $class = null, $event = "handle", $valid = true)
    {
        if ($valid && $this->_accessCheck($name)) {
            $button = [];
            $button['title'] = $title;
            $button['name'] = $name;
            if (!empty($description)) {
                $button['description'] = $description;
            }
            if (empty($class)) {
                $class = $name;
            }
            $button['buttonClass'] = $class;
            $button['onPress'] = $event;
            $this->_buttons[] = $button;
        }
        return $this;
    }

    /**
     * 添加按钮分隔符
     * @access public
     * @return object self Object
     */
    public function addSeparator()
    {
        if (count($this->_buttons) > 0) {
            $separator = [];
            $separator['separator'] = true;
            $this->_buttons[] = $separator;
        }
        return $this;
    }

    /**
     * 设置搜索项
     * @access public
     * @param array $searchItems
     * @return object self Object
     */
    public function setSearchItems(array $searchItems)
    {
        foreach ($searchItems as $searchItem) {
            $i = 0;
            $field = isset($searchItem['field']) ? $searchItem['field'] : (isset($searchItem[$i]) ? $searchItem[$i++] : null);
            if (empty($field)) {
                continue;
            }
            $html = isset($searchItem['html']) ? $searchItem['html'] : (isset($searchItem[$i]) ? $searchItem[$i++] : null);
            if (empty($html)) {
                continue;
            }
            $type = isset($searchItem['type']) ? $searchItem['type'] : (isset($searchItem[$i]) ? $searchItem[$i++] : 'text');
            $default = isset($searchItem['default']) ? $searchItem['default'] : (isset($searchItem[$i]) ? $searchItem[$i++] : null);
            $valid = isset($searchItem['valid']) ? $searchItem['valid'] : (isset($searchItem[$i]) ? $searchItem[$i++] : true);
            $this->addSearchItem($field, $html, $type, $default, $valid);
        }
    }

    /**
     * 添加搜索项
     * @param string $field 搜索字段名称
     * @param string $html 搜索项HTML代码
     * @param string $type 搜索项表单元素类型
     * @param mixed  $default 默认类型
     * @param boolean $valid 是否有效
     * @return FlexiGrid
     */
    public function addSearchItem($field, $html, $type = 'text', $default = '', $valid = true)
    {
        if ($valid) {
            if (in_array($type, array('text', 'checkbox', 'radio', 'select', 'hidden'))) {
                $this->_searchItems[$field] = array('html' => $html, 'type' => $type, 'default' => $default);
            }
        }
        return $this;
    }

    /**
     * 设置附加到搜索区域的按钮.
     *
     * @param array $attachButtons
     */
    public function setAttachToSearchRegionButtons(array $attachButtons)
    {
        foreach ($attachButtons as $attachButton) {
            $name = isset($attachButton['name']) ? $attachButton['name'] : (isset($attachButton[0]) ? $attachButton[0] : null);
            if (empty($name)) {
                continue;
            }
            $text = isset($attachButton['text']) ? $attachButton['text'] : (isset($attachButton[1]) ? $attachButton[1] : null);
            if (is_null($text)) {
                continue;
            }
            $htmlOptions = isset($attachButton['htmlOptions']) ? $attachButton['htmlOptions'] : (isset($attachButton[2]) ? $attachButton[2] : array());
            if (!is_array($htmlOptions)) {
                $htmlOptions = array();
            }
            $htmlOptions['name'] = $name;
            if (!isset($htmlOptions['class'])) {
                $htmlOptions['class'] = 'btn btn-warning';
            }
            $this->_attachButtons[$name] = Html::button($text, $htmlOptions);
        }
    }

    /**
     * 设置默认排序名称与方式
     * @param string $columnName 排序名称
     * @param string $sortOrder 排序方式
     * @return $this
     */
    public function setDefaultSort($columnName, $sortOrder = 'desc')
    {
        if (is_array($columnName)) {
            if (isset($columnName[0])) {
                $this->sortName = $columnName[0];
            }
            if (isset($columnName[1])) {
                $this->sortOrder = $columnName[1];
            } else {
                $this->sortOrder = $sortOrder;
            }
        } else {
            $this->sortName = $columnName;
            $this->sortOrder = $sortOrder;
        }
        return $this;
    }

    /**
     * 設置起始頁
     * @param int $startPage
     * @return $this
     */
    public function setStartPage($startPage)
    {
        $startPage = intval($startPage);
        if ($startPage) {
            $this->startPage = $startPage;
        }
        return $this;
    }

    /**
     * 处理Buttons,移除Buttons中多余的分隔符
     * @return array
     */
    private function _getButtons()
    {
        $groups = array();
        $gid = 0;
        for ($i = 0, $n = count($this->_buttons); $i < $n; $i++) {
            $button = $this->_buttons[$i];
            if (isset($button['separator']) && $button['separator']) {
                $gid++;
                $groups[$gid] = [];
            } else {
                $groups[$gid][] = $button;
            }
        }
        $buttons = array();
        for ($i = 0, $n = count($groups); $i < $n; $i++) {
            $group = $groups[$i];
            if (!empty($group)) {
                if (count($buttons) > 0) {
                    $buttons[] = ['separator' => true];
                }
                $buttons = array_merge($buttons, $group);
            }
        }
        return $buttons;
    }

    /**
     * 根据条件项生成Flexigrid结构
     */
    public function run()
    {
        $params = [];
        if (empty($this->url)) {
            exit("数据来源URL是必須的");
        }
        $params['url'] = $this->url;
        if (!in_array($this->dataType, ["json", "xml"])) {
            exit("数据类型不正确，只能是JSON和XML格式");
        }
        $params['dataType'] = $this->dataType;
        if (is_array($this->_colModel) && count($this->_colModel) > 0) {
            $params['colModel'] = $this->_colModel;
        } else {
            exit("列模型不能为空");
        }
        if (is_array($this->_buttons) && count($this->_buttons) > 0) {
            $params['buttons'] = $this->_getButtons();
        }
        if (is_array($this->_searchItems) && count($this->_searchItems) > 0) {
            $params['searchItems'] = $this->_searchItems;
        }
        $params['disableClearSearchButton'] = $this->disableClearSearchButton;
        if (is_array($this->_attachButtons) && count($this->_attachButtons) > 0) {
            $params['attachButtons'] = $this->_attachButtons;
        }
        if (is_array($this->_postData) && count($this->_postData)) {
            $params['params'] = $this->_postData;
        }
        $params['showSearchBar'] = $this->showSearchBar;
        $params['searchText'] = $this->searchText;
        $params['pageStat'] = $this->pageStat;
        $params['processingMsg'] = $this->processingMsg;
        $params['noFoundDataMsg'] = $this->noFoundDataMsg;
        $params['errorMsg'] = $this->errorMsg;
        $params['quickSearchText'] = $this->quickSearchText;
        $params['clearText'] = $this->clearText;
        $params['pageText'] = $this->pageText;
        $params['sortName'] = $this->sortName;
        $params['sortOrder'] = $this->sortOrder;
        $params['refreshText'] = $this->refreshText;
        $params['usePager'] = $this->usePager;
        $params['title'] = $this->title;
        $params['fitColumns'] = $this->fitColumns;
        $params['rowNumbers'] = $this->rowNumbers;
        $params['useLimit'] = $this->useLimit;
        $params['limit'] = $this->limit;
        $params['limitOptions'] = $this->limitOptions;
        $params['showToggleBtn'] = $this->showToggleBtn;
        $params['width'] = $this->width;
        $params['height'] = $this->height;
        $params['startPage'] = $this->startPage;
        $onSuccess = 'function(){$("div.bDiv .action").click(function(event) {event.stopPropagation();});if(typeof successCallback === "function"){successCallback()}}';
        $params['onSuccess'] = 'onSuccessCallback';
        $onToggleCol = 'function(c, a){if(typeof toggleColCallback==="function"){toggleColCallback(c, a)}}';
        $params['onToggleCol'] = 'onToggleColCallback';
        $onSubmit = 'function(){if(typeof submitCallback==="function"){return submitCallback();}else{return true;}}';
        $params['onSubmit'] = 'onSubmitCallback';
        $onLoad = 'function(){if(typeof loadSuccess==="function"){return loadSuccess();}else{return true;}}';
        $params['onLoad'] = 'onLoadCallback';

        $containerId = $this->_renderContainer();
        $result = '$("#' . $containerId . '").flexigrid(' . json_encode($params) . ')';
        $result = str_replace(
            array('"onSuccessCallback"', '"onToggleColCallback"', '"onSubmitCallback"', '"onLoadCallback"'),
            array($onSuccess, $onToggleCol, $onSubmit, $onLoad),
            $result
        );
        $result = preg_replace('/"onPress"\s*:\s*"([a-z]+)"/i', '"onPress":$1', $result);
        $submitCode = <<<EOF
           $("#{$this->formName}").submit(function () {
              var ref = $.trim($(this).attr("ref"));
              if (ref) {
                  var action = $(":hidden[name=action]").val();
                  var execSuccessed = true;
                  if (typeof formOnSubmit === "function") {
                        execSuccessed = formOnSubmit(action);
                  }
                  if (execSuccessed) {
                       $(this).attr("action", ref.replace(/\{action\}/, action));
                       return true;
                  }
                  return false;
              }
              return true;
           });
EOF;
        $flexigridInitJs = array();
        if (!empty($this->searchButtonClass)) {
            $flexigridInitJs[] = '$("#startSearchButton").addClass("' . $this->searchButtonClass . '");';
        }
        if (!empty($this->clearButtonClass)) {
            $flexigridInitJs[] = '$("#clearSearchButton").addClass("' . $this->clearButtonClass . '");';
        }
        $view = $this->getView();
        $view->registerJs($result, View::POS_READY, 'flexigrid');
        $view->registerJs($submitCode, View::POS_READY, 'submitCode');
        $view->registerJs(join("\n", $flexigridInitJs), View::POS_READY, 'flexigridInitJs');
    }

    /**
     * 权限检查
     * @access private
     * @param string $action 权限名称
     * @return boolean
     */
    private function _accessCheck($action)
    {
        $controller = $this->getView()->context;
        if ($controller instanceof Controller) {
            $inController = null;
            if (isset($this->actionMaps[$action])) {
                $map = $this->actionMaps[$action];
                if (is_array($map)) {
                    list($action, $inController) = each($map);
                } else {
                    $inController = $map;
                }
            }
            if (method_exists($controller, 'accessCheck')) {
                return $controller->accessCheck($action, $inController);
            }
        }
        return true;
    }

    /**
     * 创建一个外部容器
     * @access private
     * @return string
     */
    private function _renderContainer()
    {
        $containerHtml = '';
        if ($this->_toggleBox || $this->forceAddForm) {
            $htmlOptions = array();
            $htmlOptions['name'] = $this->formName;
            $htmlOptions['id'] = $this->formName;
            if (empty($this->action)) {
                $htmlOptions['ref'] = Url::toRoute(('{action}'));
                $this->action = 'javascript:void(0)';
            } else {
                if (strpos($this->action, '{action}') !== false) {
                    $htmlOptions['ref'] = $this->action;
                    $this->action = 'javascript:void(0)';
                }
            }
            $containerHtml .= Html::beginForm($this->action, 'post', $htmlOptions);
        }
        $containerId = substr(md5(uniqid()), 5, 8);
        $containerHtml .= '<table id="' . $containerId . '" style="display:none"></table>';
        if ($this->_toggleBox || $this->forceAddForm) {
            $containerHtml .= Html::hiddenInput('boxChecked', '0');
            $containerHtml .= Html::hiddenInput('action', '');
            if (is_array($this->_postData) && count($this->_postData)) {
                foreach ($this->_postData as $item) {
                    $containerHtml .= Html::hiddenInput($item['name'], $item['value']);
                }
            }
            $containerHtml .= Html::endForm();
        }
        echo $containerHtml;
        return $containerId;
    }
}