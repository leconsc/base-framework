<?php
/**
 *
 *
 * @author ChenBin
 * @version $Id:NormalRule.php, v1.0 2017-01-10 11:47 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2017(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;
use Yii;

class NormalRule extends Object implements UrlRuleInterface
{

    /**
     * 创建URL.
     *
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return string
     */
    public function createUrl($manager, $route, $params)
    {
        if (empty($params)) {
            return false;
        }
        if ($this->hasModuleName($route)) {
            $count = 2;
        } else {
            $count = 1;
        }
        $backslashCount = substr_count($route, '/');
        $defaultAction = Yii::$app->controller->defaultAction;
        if ($backslashCount === $count - 1) {
            $route .= '/' . $defaultAction;
        }
        $queryString = '';
        foreach ($params as $name => $value) {
            if(is_scalar($value)) {
                $queryString .= sprintf('/%s/%s', $name, $value);
            }
        }
        return $route . $queryString;
    }

    /**
     * 解析请求URL.
     *
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (!empty($pathInfo)) {
            if ($this->hasModuleName($pathInfo)) {
                $count = 3;
            } else {
                $count = 2;
            }
            $items = explode('/', $pathInfo);
            $itemCount = count($items);
            if ($itemCount > $count) {
                $route = join('/', array_slice($items, 0, $count));
                $params = [];
                for ($i = $count; $i < $itemCount; $i = $i + 2) {
                    if (isset($items[$i]) && isset($items[$i + 1])) {
                        $params[$items[$i]] = $items[$i + 1];
                    }
                }
                return [$route, $params];
            } else {
                return [$pathInfo, []];
            }

        }
        return false;
    }

    /**
     * 判断URL创建参数或路由参数中是否包含模块名称.
     *
     * @param string $route
     * @return bool
     */
    private function hasModuleName($route)
    {
        if (!empty($route)) {
            $name = substr($route, 0, strpos($route, '/'));
            $modules = Yii::$app->getModules();
            if (isset($modules[$name])) {
                return true;
            }
        }
        return false;
    }
}