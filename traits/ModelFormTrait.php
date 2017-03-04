<?php
/**
 * 用于Model的Trait
 *
 * @author ChenBin
 * @version $Id:ModelHelper.php, v1.0 2016-12-08 17:50 ChenBin $
 * @category app\traits
 * @copyright 2016(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\traits;

use app\helpers\FilterHelper;
use app\helpers\UtilHelper;

trait ModelFormTrait
{
    /**
     * 从模型表单数据获取表单值.
     *
     * @access public
     * @param string $name 表单变量名称，为空返回所有表单变量值
     * @param mixed $default
     * @param mixed $filter 过滤器(三种取值类型, 回调函数, 有效的PHP调用函数, 函数名称, 取值范围)
     * @return mixed
     */
    public function getFormValue($name = null, $default = null, $filter = null, array $params = [])
    {
        $className = UtilHelper::getClassShortName($this);
        if (isset($_POST[$className]) && isset($_POST[$className][$name])) {
            $value = $_POST[$className][$name];
            $type = null;
            if(isset($params['type'])){
                $type = $params['type'];
            }
            return FilterHelper::filter($value, $default, $type, $filter);
        }
        return $default;
    }

    /**
     * 设置模型表单数据值
     * @access public
     * @param $name
     * @param $value
     * @return bool
     */
    public function setFormValue($name, $value)
    {
        $className = UtilHelper::getClassShortName($this);
        if (!isset($_POST[$className])) {
            $_POST[$className] = array();
        }
        $_POST[$className][$name] = $value;
        return true;
    }

    /**
     * 设置模型表单
     *
     * @param array $data
     * @return bool
     */
    public function setFormValues(array $data)
    {
        $className = UtilHelper::getClassShortName($this);
        if (!isset($_POST[$className])) {
            $_POST[$className] = array();
        }
        $_POST[$className] = $data;
        return true;
    }

    /**
     * 获取模型表单的所有值.
     *
     * @return array
     */
    public function getFormValues()
    {
        $className = UtilHelper::getClassShortName($this);
        if (isset($_POST[$className])) {
            return $_POST[$className];
        }
        return [];
    }
    /**
     * 获取指性属性的值.
     *
     * @return mixed|string
     */
    protected function _getAttributeValue($attributeName)
    {
        $value = $this->getFormValue($attributeName);
        if (empty($value)) {
            $value = $this->$attributeName;
        }
        return $value;
    }
} 