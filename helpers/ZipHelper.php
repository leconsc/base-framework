<?php
/**
 * 压缩助手
 *
 * @author ChenBin
 * @version $Id:ZipHelper.php, v1.0 2016-10-31 17:24 ChenBin $
 * @package Application\Helpers
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin,all rights reserved.
 */

namespace Application\Helpers;


use ZipArchive;
use Exception;

class ZipHelper
{
    /**
     * 添加文件到压缩包.
     *
     * @param $path
     * @param $zip
     */
    protected static function _addFileToZip($path, ZipArchive $zip, $relativePath, $mapName = null)
    {
        if(is_dir($path)) {
            $handler = opendir($path); //打开当前文件夹由$path指定。
            while (($name = readdir($handler)) !== false) {
                if ($name != "." && $name != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                    if (is_dir($path . "/" . $name)) {// 如果读取的某个对象是文件夹，则递归
                        self::_addFileToZip($path . "/" . $name, $zip, $relativePath, $mapName);
                    } else {
                        //将文件加入zip对象
                        $fileFullPath = $path . "/" . $name;
                        $localName = substr($fileFullPath, strlen($relativePath) + 1);
                        if ($mapName) {
                            $localName = $mapName . '/' . $localName;
                        }
                        $zip->addFile($fileFullPath, $localName);
                    }
                }
            }
            @closedir($path);
        }else{
            throw new Exception('无效的待压缩文件夹');
        }
    }

    /**
     * 压缩文件
     *
     * @param $zipFileName
     * @param $zipPath string | array
     */
    public static function zipFolder($zipFileName, $zipItems)
    {
        $status = false;
        try {
            $zip = new ZipArchive();
            if ($zip->open($zipFileName, ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
                if (is_array($zipItems)) {
                    foreach ($zipItems as $mapName => $zipFolder) {
                        $zipFolder = rtrim($zipFolder, '/');
                        if(is_numeric($mapName)){
                            $mapName = basename($zipFolder);
                        }
                        self::_addFileToZip($zipFolder, $zip, $zipFolder, $mapName);
                    }
                } else {
                    $zipFolder = rtrim($zipItems, '/');
                    self::_addFileToZip($zipFolder, $zip, $zipFolder);
                }
                $zip->close(); //关闭处理的zip文件
                $status = true;
                $message = 'success';
            } else {
                $message = 'error';
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return array($status, $message);
    }

    /**
     * 压缩文件
     *
     * @param string $zipFileName
     * @param string | array $zipItems
     */
    public static function zipFiles($zipFileName, $zipItems)
    {
        $status = false;
        try {
            $zip = new ZipArchive();
            if ($zip->open($zipFileName, ZipArchive::CREATE || ZipArchive::OVERWRITE) === true) {
                $zipItems = (array)$zipItems;
                foreach ($zipItems as $zipItem) {
                    if(is_file($zipItem)) {
                        $localName = basename($zipItem);
                        $zip->addFile($zipItem, $localName);
                    }else{
                        throw new Exception('无效的待压缩文件');
                    }
                }
                $zip->close(); //关闭处理的zip文件
                $status = true;
                $message = 'success';
            } else {
                $message = 'error';
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return array($status, $message);
    }
}