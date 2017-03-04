<?php
/**
 * 资源包抽像层定义，方便查询其它资源文件
 *
 * @author ChenBin
 * @version $Id:AssetAbstract.php, v1.0 2016-12-29 11:08 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\assets;

use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\AssetManager;
use yii\web\View;
use Yii;

abstract class AssetAbstract extends AssetBundle
{
    /** @var bool 记录静态方法调用前是否进行了初始化 */
    private static $_initialized = false;
    /** @var View 视图对象 */
    private static $_view;
    /** @var AssetManager 资源管理器 */
    private static $_manager;
    /** @var array 资源包集合 */
    private static $_assetBundles;

    /**
     * 初始化
     * @return bool
     */
    private static function _init()
    {
        if (!self::$_initialized) {
            self::$_view = Yii::$app->view;
            self::register(self::$_view);
            self::$_manager = self::$_view->getAssetManager();

            self::$_initialized = true;
        }
        return self::$_initialized;
    }

    /**
     * 获取AssetBundle对象.
     *
     * @return AssetBundle
     */
    private static function getAssetBundle(){
        $assetBundleClass = get_called_class();
        if (!isset(self::$_assetBundles[$assetBundleClass])) {
            self::$_assetBundles[$assetBundleClass] = new $assetBundleClass();
            self::$_assetBundles[$assetBundleClass]->init();
        }
        return self::$_assetBundles[$assetBundleClass];
    }
    /**
     * 向视图注入JS文件.
     *
     * @param string $asset
     * @param array $options 选项
     * @param string $key 唯一标识KEY
     */
    public static function registerJsFile($asset, $options = [], $key = null)
    {
        $url = self::getJsUrl($asset);
        self::$_view->registerJsFile($url, $options, $key);
    }

    /**
     * 向视图注入Css文件.
     *
     * @param string $asset
     * @param array $options
     */
    public static function registerCssFile($asset, $options = [], $key = null)
    {
        $url = self::getCssUrl($asset);
        self::$_view->registerCssFile($url, $options, $key);
    }

    /**
     * 获取JS地址.
     *
     * @param string $asset
     * @return string
     */
    public static function getJsUrl($asset)
    {
        if (strtolower(substr($asset, -3)) !== '.js') {
            $asset .= '.js';
        }

        $assetBundle = self::getAssetBundle();
        if (!$assetBundle instanceof LibraryAsset) {
            $asset = ltrim($asset, DIRECTORY_SEPARATOR);
            $asset = 'js/' . $asset;
        }
        return self::getUrl($asset);
    }

    /**
     * 获取CSS地址.
     *
     * @param string $asset
     * @return string
     */
    public static function getCssUrl($asset)
    {
        if (strtolower(substr($asset, -4)) !== '.css') {
            $asset .= '.css';
        }
        $assetBundle = self::getAssetBundle();
        if (!$assetBundle instanceof LibraryAsset) {
            $asset = ltrim($asset, DIRECTORY_SEPARATOR);
            $asset = 'css/' . $asset;
        }
        return self::getUrl($asset);
    }

    /**
     * 获取图片URL地址.
     *
     * @param string $asset
     * @return string
     */
    public static function getImageUrl($asset)
    {
        $assetBundle = self::getAssetBundle();
        if (!$assetBundle instanceof LibraryAsset) {
            $asset = ltrim($asset, DIRECTORY_SEPARATOR);
            $asset = 'images/' . $asset;
        }
        return self::getUrl($asset);
    }

    /**
     * 获取标准URL地址.
     *
     * @param string $asset
     * @return string
     */
    public static function getUrl($asset)
    {
        if (!Url::isRelative($asset)) {
            return $asset;
        }
        self::_init();
        $asset = ltrim($asset, DIRECTORY_SEPARATOR);
        $assetBundle = self::getAssetBundle();
        return self::$_manager->getAssetUrl($assetBundle, $asset);
    }
}