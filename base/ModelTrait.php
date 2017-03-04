<?php
/**
 * 用于Model的Trait
 *
 * @author ChenBin
 * @version $Id:ModelTrait.php, v1.0 2014-12-08 17:50 ChenBin $
 * @category app\base
 * @since 1.0
 * @copyright 2014(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\base;

trait ModelTrait {
    /**
     * 从模型表单数据获取表单值.
     *
     * @access public
     * @param string $name  表单变量名称，为空返回所有表单变量值
     * @param mixed $default
     * @return mixed
     */
    public function getFormValue($name = null, $default = null)
    {
        $className = get_class($this);
        if (isset($_POST[$className])) {
            if (empty($name)) {
                return $_POST[$className];
            }
            if (isset($_POST[$className][$name])) {
                return $_POST[$className][$name];
            }
        }
        return $default;
    }

    /**
     * 设置模型表单数据值
     * @access public
     * @param $name
     * @param $value
     * @return bool 设置成功返回true,失败返回false
     */
    public function setFormValue($name, $value)
    {
        $className = get_class($this);
        if (isset($_POST[$className])) {
            $_POST[$className][$name] = $value;
            return true;
        }
        return false;
    }
} 