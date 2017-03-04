<?php
/**
 * 图像处理助手
 *
 * @author ChenBin
 * @version $Id:ImageHelper.php, v1.0 2016-6-30 12:20+100 ChenBin $
 * @package Helper
 * @copyright 2016(C)Copyright By ChenBin, all rights reserved.
 */
namespace app\helpers;

final class ImageHelper
{
    /**
     * 生成縮略圖
     *
     * @param string $sourceFile 要生成的縮略圖的文件的实际路径
     * @param int $maxWidth 最大寬度
     * @param int $maxHeight 最大高度
     * @param boolean $removeSource 是否移除原文件
     * @param array $options 选项
     */
    public static function createThumbnail($sourceFile, $maxWidth, $maxHeight, $removeSource = true, array $options = array())
    {

        $result = self::getAttributes($sourceFile, $maxWidth, $maxHeight, $options);

        if (!$result) {
            return false;
        }

        $targetFile = isset($options['targetFile']) ? $options['targetFile'] : null;
        $targetIsAbsolute = isset($options['targetIsAbsolute']) ? $options['targetIsAbsolute'] : false;

        $force = isset($options['force']) ? $options['force'] : false;

        if (empty($targetFile)) {
            $targetFile = dirname($sourceFile) . DIRECTORY_SEPARATOR . $result['baseName'] . "_thumb";
        } else {
            if(!$targetIsAbsolute) {
                $targetFile = dirname($sourceFile) . DIRECTORY_SEPARATOR . $targetFile;
            }
        }
        if (isset($result['resize']) && $result['resize'] || $force) {
            $imgResource = null;
            switch ($result['type']) {
                case 'gif':
                    $imgResource = imagecreatefromgif($sourceFile);
                    $fileExt = 'gif';
                    break;
                case 'png':
                    $imgResource = imagecreatefrompng($sourceFile);
                    $fileExt = 'png';
                    break;
                default:
                    $imgResource = imagecreatefromjpeg($sourceFile);
                    $fileExt = 'jpg';
                    break;
            }
            if (empty($imgResource)){
                return false;
            }
            if(!$targetIsAbsolute){
                $targetFile .= '.'.$fileExt;
            }
            if (function_exists('imagecopyresampled')) {
                $newImgResource = imagecreatetruecolor($result['reWidth'], $result['reHeight']);
                imagecopyresampled($newImgResource, $imgResource, 0, 0, 0, 0, $result['reWidth'], $result['reHeight'], $result['width'], $result['height']);
            } else {
                $newImgResource = imagecreate($result['reWidth'], $result['reHeight']);
                imagecopyresized($newImgResource, $imgResource, 0, 0, 0, 0, $result['reWidth'], $result['reHeight'], $result['width'], $result['height']);
            }
            switch ($result['type']) {
                case 'gif':
                    imagegif($newImgResource, $targetFile);
                    break;
                case 'png':
                    imagepng($newImgResource, $targetFile);
                    break;
                default:
                    imagejpeg($newImgResource, $targetFile);
                    break;
            }
            imagedestroy($newImgResource);
            imagedestroy($imgResource);
        } else {
            if(!$targetIsAbsolute) {
                $targetFile .= '.' . $result['type'];
            }
            copy($sourceFile, $targetFile);
        }
        chmod($targetFile, 0755);
        if ($removeSource) {
            @unlink($sourceFile);
        }
        $result['file'] = $sourceFile;
        $result['thumbFile'] = $targetFile;

        return $result;
    }

    /**
     * 获取图像基本信息
     *
     * @static
     * @access public
     * @param string $sourceFile 文件名
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @return array|bool
     */
    public static function getAttributes($sourceFile, $maxWidth = null, $maxHeight = null, array $options = array())
    {
        if (!is_file($sourceFile)) {
            return false;
        }

        list($width, $height, $type,) = getimagesize($sourceFile);

        if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            return false;
        }
        $result = array(
            'width' => $width,
            'height' => $height
        );
        switch ($type) {
            case IMAGETYPE_GIF:
                $result['baseName'] = basename($sourceFile, '.gif');
                $result['type'] = 'gif';
                break;
            case IMAGETYPE_PNG:
                $result['baseName'] = basename($sourceFile, '.png');
                $result['type'] = 'png';
                break;
            default:
                $result['baseName'] = basename($sourceFile, '.jpg');
                $result['type'] = 'jpg';
                break;
        }
        if ($maxWidth || $maxHeight) {
            if (!is_numeric($maxWidth) && !is_numeric($maxHeight)) {
                return false;
            }

            $forceWidth = isset($options['forceWidth']) ? $options['forceWidth'] : false;
            $forceHeight = isset($options['forceHeight']) ? $options['forceHeight'] : false;

            $resizeWidth = $resizeHeight = false;
            $resize = true;
            $widthRatio = $heightRatio = $ratio = 1;
            if (($maxWidth && $width > $maxWidth) || ($maxHeight && $height > $maxHeight)) {
                if ($maxWidth && $width > $maxWidth) {
                    $widthRatio = $maxWidth / $width;
                    $resizeWidth = true;
                }
                if ($maxHeight && $height > $maxHeight) {
                    $heightRatio = $maxHeight / $height;
                    $resizeHeight = true;
                }
                if ($forceWidth && $forceHeight) {
                    $newWidth = $width * $widthRatio;
                    $newHeight = $height * $heightRatio;
                } else if ($forceWidth) {
                    $newWidth = $width * $widthRatio;
                    $newHeight = $height * $widthRatio;
                } else if ($forceHeight) {
                    $newWidth = $width * $heightRatio;
                    $newHeight = $height * $heightRatio;
                } else {
                    if ($resizeWidth && $resizeHeight) {
                        if ($widthRatio < $heightRatio) {
                            $ratio = $widthRatio;
                        } else {
                            $ratio = $heightRatio;
                        }
                    } elseif ($resizeWidth) {
                        $ratio = $widthRatio;
                    } elseif ($resizeHeight) {
                        $ratio = $heightRatio;
                    }
                    $newWidth = $width * $ratio;
                    $newHeight = $height * $ratio;
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
                $resize = false;
            }
            $result['resize'] = $resize;
            $result['reWidth'] = $newWidth;
            $result['reHeight'] = $newHeight;
        }
        return $result;
    }

    /**
     * 檢測圖片大小
     *
     * @static
     * @param string $sourceFile
     * @param int $width
     * @param int $height
     */
    public static function checkSize($sourceFile, $width, $height)
    {
        if (!is_file($sourceFile)) {
            return false;
        }

        list($imageWidth, $imageHeight, ,) = getimagesize($sourceFile);

        if ($imageWidth == $width && $imageHeight == $height) {
            return true;
        }
        return false;
    }
}