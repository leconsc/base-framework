<?php
/**
 * EasyUi Tree下拉框组件
 *
 * @author ChenBin
 * @version $Id:EasyUiTreeDropDown.php, v1.0 2017-01-12 14:40 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\widgets;

use app\assets\EasyUiAsset;
use yii\base\Exception;
use yii\bootstrap\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

class EasyUiTreeDropDown extends InputWidget
{
    /**
     * tree数据
     * [
     *   ['id'=>1, 'name'='tree item 1', 'parent'=>'0']
     *   ['id'=>2, 'name'='tree item 2', 'parent'=>'0']
     *   ['id'=>3, 'name'='tree item 3', 'parent'=>'1']
     * ]
     * @var array
     */
    private $_data = [];

    /**
     * @var bool 是否有Root节点
     */
    private $_hasRoot = true;

    /**
     * @var array Root节点数据
     */
    private $_rootData = ['id' => '0', 'name' => '--所有项--'];
    /**
     * @var array 忽略掉的节点项
     */
    private $_ignoreItems = [];

    /** @var string 顶层Parent Id的值 */
    private $_topParentId = '0';

    /**
     * 设置树形节点数据.
     *
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function setData(array $data)
    {
        foreach ($data as &$item) {
            if (!isset($item['id']) || !isset($item['name'])) {
                throw new Exception('错误的节点数据定义');
            }
            if (!isset($item['parent'])) {
                $item['parent'] = 0;
            }
        }
        $this->_data = $data;
        return $this;
    }

    /**
     * 是否有Root节点.
     *
     * @param boolean $hasRoot
     * @return $this
     */
    public function setHasRoot($hasRoot)
    {
        $this->_hasRoot = (boolean)$hasRoot;
        return $this;
    }

    /**
     * 设置Root节点数据.
     *
     * @param array $rootData
     * @return $this
     */
    public function setRootData(array $rootData)
    {
        if (isset($rootData['id']) && isset($rootData['name'])) {
            $this->_rootData = $rootData;
        }
        return $this;
    }

    /**
     * 设备忽略项
     * @param int|string|array $ignore
     * @return $this
     */
    public function setIgnore($ignore)
    {
        if (is_scalar($ignore)) {
            $this->_ignoreItems = (array)$ignore;
        } else if (is_array($ignore)) {
            $this->_ignoreItems = $ignore;
        }
        return $this;
    }

    /**
     * 设置顶层Parent的值.
     *
     * @param int|string $id
     * @return $this
     */
    public function setTopParentId($id)
    {
        if (is_scalar($id)) {
            $this->_topParentId = $id;
        }
        return $this;
    }

    /**
     * 设置渲染元素ID.
     *
     * @param string $id
     * @return $this
     */
    public function setElementId($id)
    {
        if (is_string($id)) {
            $this->_elementId = $id;
        }
        return $this;
    }

    /**
     * 构建Tree数据.
     *
     * @param array $tree
     * @param array $treeData
     * @return mixed
     */
    private function _buildTreeData(array $tree, array $treeData)
    {
        foreach ($tree as &$item) {
            if (isset($treeData[$item['id']])) {
                $item['children'] = $this->_buildTreeData($treeData[$item['id']], $treeData);
            }
        }
        return $tree;
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $items = [];
        if (count($this->_data)) {
            foreach ($this->_data as $item) {
                //忽略掉当前分类自身及下属分类，防止循环挂靠
                if (in_array($item['id'], $this->_ignoreItems)) {
                    continue;
                }
                if (!isset($items[$item['parent']])) {
                    $items[$item['parent']] = array();
                }
                $items[$item['parent']][] = array(
                    'id' => $item['id'],
                    'text' => $item['name'],
                );
            }
        }
        $treeItems = [];
        if (isset($items[$this->_topParentId])) {
            $treeItems = $this->_buildTreeData($items[$this->_topParentId], $items);
        }
        if ($this->_hasRoot) {
            $treeData = [
                [
                    'id' => $this->_rootData['id'],
                    'text' => $this->_rootData['name']
                ]
            ];
            if (!empty($treeItems)) {
                $treeData[0]['children'] = $treeItems;
            }
        } else {
            $treeData = [];
            if (!empty($treeItems)) {
                $treeData = $treeItems;
            }
        }

        $data = Json::htmlEncode($treeData);
        if (isset($this->options['class'])) {
            $this->options['class'] = 'easyui-combotree ' . $this->options['class'];
        } else {
            $this->options['class'] = 'easyui-combotree';
        }

        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
            $this->options['id'] = Html::getInputId($this->model, $this->attribute);
            $jsScript = "jQuery('#{$this->options['id']}').combotree('loadData', $data);";
            $position = View::POS_READY;
        } else {
            if (!isset($this->options['id'])) {
                $this->options['id'] = $this->name;
            }
            $input = Html::textInput($this->name, $this->value, $this->options);
            $jsScript = "jQuery('#{$this->options['id']}').combotree().combotree('loadData', $data);";
            $position = View::POS_LOAD;
        }
        echo $input;
        $view = $this->getView();
        EasyUiAsset::register($view);
        $view->registerJs($jsScript, $position);
    }
}