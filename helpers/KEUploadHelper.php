<?php
/**
 * KindEditor上传助手
 *
 * @author ChenBin
 * @version $Id: KEUploadHelper.php, 1.0 2016-09-18 17:19+100 ChenBin$
 * @package: app\helpers
 * @since 1.0
 * @see app\helpers\ArrayHelper, app\helpers\ImageHelper
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\helpers;

use Exception;
use Yii;

class KEUploadHelper
{
    const DS = DIRECTORY_SEPARATOR;
    /** 缩略图默认目录 */
    const THUMB_BASE_DIR = '_thumbs';
    const DIR_MODE = 0777;
    const FILE_MODE = 0644;

    /** @var string $_resourceType 　管理的资源类型 */
    private $_resourceType;
    /** @var null|integer 允许上传图片的宽 */
    private $_imageWidth = null;
    /** @var null|integer 允许上传图片的高 */
    private $_imageHeight = null;
    /** @var bool 强制图片必须符合指定大小 */
    private $_forceImageSize = false;
    /** @var int $_thumbWith 　缩略图宽度 */
    private $_thumbWith;
    /** @var int $_thumbHeight 缩略图高度 */
    private $_thumbHeight;
    /** @var string $_uploadDir 上传目录 */
    private $_uploadDir;
    /** @var string $_thumbDir 当类型为图片时，缩略图存放位置 */
    private $_thumbDir;
    /** @var string $_uploadBaseUrl 上传基本URL */
    private $_uploadBaseUrl;
    /** @var string $_uploadUrl 上传URL地址 */
    private $_uploadUrl;
    /** @var string $_thumbBaseUrl 缩略图存放基本URL */
    private $_thumbBaseUrl;
    /** @var string $_thumbUrl 当类型为图片时，缩略图URL图径 */
    private $_thumbUrl;
    /** @var array $_extensions 有效的扩展名 */
    private $_extensions = array();
    /** @var array */
    private $_imageExtensions = array('jpg', 'png', 'gif', 'jpeg');
    /** @var string $_moveUpDirPath 相对于根目录的上一级目录 */
    private $_moveUpDirPath;
    /** @var string $_currentDirPath 相对于根目录的当前目录 */
    private $_currentDirPath;
    /** @var boolean 是否创建缩略图 */
    private $_createThumb = false;
    /** @var boolean 是否自动创建子目录 */
    private $_autoCreateSubDirectory = true;
    /** @var array 配置信息 */
    private $_config;

    public function __construct(array $config = [])
    {
        try {
            $this->_config = $config;

            $uploadBaseDir = $this->_getPathOfAlias($this->_getConfig('uploadPath'));
            if ($uploadBaseDir === false) {
                $uploadBaseDir = $this->_getConfig('uploadPath');
            }
            if (!is_dir($uploadBaseDir) || !is_writable($uploadBaseDir)) {
                throw new Exception('上传目录不存在或不可写');
            }
            $uploadBaseDir = rtrim($uploadBaseDir, self::DS);

            $uploadBaseUrl = $this->_getConfig('uploadBaseUrl');
            if (!$uploadBaseUrl) {
                $webRoot = $this->_getPathOfAlias('@webroot');
                $uploadBaseUrl = substr($uploadBaseDir, strlen($webRoot));
            }
            $uploadRelativePath = '';

            $category = $this->_getQuery('category');
            if ($category) {
                $uploadRelativePath .= self::DS . $category;
            }
            $resourceType = strtolower($this->_getQuery('dir'));
            if (!in_array($resourceType, array('image', 'flash', 'media', 'file'))) {
                throw new Exception('无效的上传文件类型');
            }
            $this->_resourceType = $resourceType;
            $uploadRelativePath .= self::DS . $this->_resourceType . 's';

            $extensions = $this->_getConfig($resourceType . 'Format');
            if (!empty($extensions)) {
                if (is_string($extensions)) {
                    $this->_extensions = ArrayHelper::fromString($extensions);
                } else if (is_array($extensions)) {
                    $this->_extensions = $extensions;
                }
            }
            $path = $this->_getQuery('path', true);
            if ($path) {
                $uploadRelativePath .= self::DS . $path;
                $this->_currentDirPath = $path;
                $this->_moveUpDirPath = preg_replace('/(.*?)[^\/]+\/$/', '$1', $this->_currentDirPath);
            } else {
                $this->_currentDirPath = '';
                $this->_moveUpDirPath = '';
            }
            $uploadRelativePath = rtrim($uploadRelativePath, self::DS);
            $uploadDir = $uploadBaseDir . $uploadRelativePath;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, self::DIR_MODE, true);
            }
            $uploadUrl = $uploadBaseUrl . $uploadRelativePath;

            $this->_createThumb = $this->_getQuery('createThumb', false, false);
            $this->_autoCreateSubDirectory = $this->_getQuery('autoCreateSubDirectory', false, true);

            if ($this->_resourceType === 'image' && $this->_createThumb) {
                $thumbBaseUrl = $uploadBaseUrl . self::DS . '_thumbs';
                $thumbDir = $uploadBaseDir . self::DS . '_thumbs' . $uploadRelativePath;
                $thumbUrl = $thumbBaseUrl . $uploadRelativePath;
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, self::DIR_MODE, true);
                }
                $this->_thumbBaseUrl = $thumbBaseUrl . self::DS;
                $this->_thumbWith = $this->_getQuery('thumbWidth', false, $this->_getConfig($resourceType . 'ThumbWidth'));
                $this->_thumbHeight = $this->_getQuery('thumbHeight', false, $this->_getConfig($resourceType . 'ThumbHeight'));
                $this->_thumbDir = $thumbDir . self::DS;
                $this->_thumbUrl = $thumbUrl . self::DS;
            }

            $this->_forceImageSize = $this->_getConfig('forceImageSize', false);
            if($this->_forceImageSize){
                $this->_imageWidth = $this->_getConfig('imageWidth', 500);
                $this->_imageHeight = $this->_getConfig('imageHeight', 500);
            }

            $this->_uploadBaseUrl = $uploadBaseUrl;
            $this->_uploadDir = $uploadDir . self::DS;
            $this->_uploadUrl = $uploadUrl . self::DS;
            return true;
        } catch (Exception $e) {
            $this->_sendError($e->getMessage());
        }
    }

    /**
     * 获取GET参数，并检查参数的规范性．
     *
     * @param string $name
     * @param bool $withBackslash 是否匹配正斜线
     * @param null|string $default 当参数未设定时，返回null,如果符合规范则返回实际值
     * @return mixed|null
     * @throws Exception 当参数设定但是不符合规范时，抛出异常
     */
    private function _getQuery($name, $withBackslash = false, $default = null)
    {
        $value = Yii::$app->request->getQueryParam($name);
        if ($value === null) {
            return $default;
        }
        if (is_string($value)) {
            if ($withBackslash) {
                $pattern = '/^([0-9a-z_]+\/?)+$/i';
            } else {
                $pattern = '/^[0-9a-z_]+$/i';
            }
            if (preg_match($pattern, $value)) {
                return $value;
            }else if($value === ''){
                return $default;
            }
        }

        throw new Exception('参数错误(name:' . $name . ', value:' . (string)$value . ')');
    }

    /**
     * 输出文件列表(可返回)
     *
     * @param bool $return
     * @return array
     */
    public function createList($return = false)
    {
        try {
            //遍历目录取得文件信息
            $fileList = array();
            if ($handle = opendir($this->_uploadDir)) {
                $i = 0;
                while (false !== ($fileName = readdir($handle))) {
                    if ($fileName{0} == '.') {
                        continue;
                    }
                    $file = $this->_uploadDir . $fileName;
                    if (is_dir($file)) {
                        $fileList[$i]['is_dir'] = true; //是否文件夹
                        $fileList[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                        $fileList[$i]['filesize'] = 0; //文件大小
                        $fileList[$i]['is_photo'] = false; //是否图片
                        $fileList[$i]['filetype'] = ''; //文件类别，用扩展名判断
                    } else {
                        $fileList[$i]['is_dir'] = false;
                        $fileList[$i]['has_file'] = false;
                        $fileList[$i]['filesize'] = filesize($file);
                        $fileList[$i]['dir_path'] = '';
                        $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $fileList[$i]['is_photo'] = in_array($fileExt, $this->_imageExtensions);
                        $fileList[$i]['filetype'] = $fileExt;
                    }
                    $fileList[$i]['filename'] = $fileName; //文件名，包含扩展名
                    $fileList[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                    $i++;
                }
                closedir($handle);
            }
            usort($fileList, array($this, '_cmp'));

            $response = array();
            //相对于根目录的上一级目录
            $response['moveup_dir_path'] = $this->_moveUpDirPath;
            //相对于根目录的当前目录
            $response['current_dir_path'] = $this->_currentDirPath;
            //当前目录的URL
            $response['current_url'] = $this->_uploadUrl;

            $response['create_thumb'] = $this->_createThumb;
            //当资源类型为图片时，缩略图URL
            if ($this->_resourceType === 'image' && $this->_createThumb) {
                $response['current_thumb_url'] = $this->_thumbUrl;
            }
            //文件数
            $response['total_count'] = count($fileList);
            //文件列表数组
            $response['file_list'] = $fileList;
            if ($return) {
                return $response;
            } else {
                $this->_sendJson($response);
            }
        } catch (Exception $e) {
            $this->_sendError($e->getMessage());
        }
    }

    /**
     * 上传文件处理
     */
    public function upload()
    {
        try {
            $filePostName = 'uploadFile';
            if (empty($_FILES) || !isset($_FILES[$filePostName])) {
                throw new Exception('未指定有效的上传文件');
            }
            $postFile = $_FILES[$filePostName];
            //PHP上传失败
            if (!empty($postFile['error'])) {
                switch ($postFile['error']) {
                    case '1':
                        $error = '超过php.ini允许的大小。';
                        break;
                    case '2':
                        $error = '超过表单允许的大小。';
                        break;
                    case '3':
                        $error = '只有部分文件被上传。';
                        break;
                    case '4':
                        $error = '请选择文件！';
                        break;
                    case '6':
                        $error = '找不到临时目录。';
                        break;
                    case '7':
                        $error = '写文件到硬盘出错。';
                        break;
                    case '8':
                        $error = 'File upload stopped by extension。';
                        break;
                    default:
                        $error = '未知错误。';
                }
                throw new Exception($error);
            }
            //原文件名
            $fileName = $postFile['name'];
            //服务器上临时文件
            $tmpName = $postFile['tmp_name'];
            //文件大小
            $fileSize = $postFile['size'];
            //检查文件名
            if (!$fileName) {
                throw new Exception("请选择文件!");
            }
            $size = $this->_getConfig($this->_resourceType . 'Size', 0);
            if (!is_numeric($size)) {
                throw new Exception('无效的上传文件大小设置(' . $size . ')');//autoCreateSubDirectory
            }
            $maxSize = $size * 1024;
            //检查文件大小
            if ($fileSize > $maxSize) {
                throw new Exception("上传文件大小超过限制。");
            }
            if (!is_dir($this->_uploadDir)) {
                throw new Exception("上传目录不存在。");
            }
            //检查目录写权限
            if (!is_writable($this->_uploadDir)) {
                throw new Exception("上传目录没有写权限。");
            }
            if (empty($this->_currentDirPath) && $this->_autoCreateSubDirectory) {
                $ym = date('Ym');
                $this->_uploadDir .= $ym . self::DS;
                if (!is_dir($this->_uploadDir)) {
                    mkdir($this->_uploadDir, self::DIR_MODE, true);
                }
                $this->_uploadUrl .= $ym . self::DS;
                if ($this->_resourceType == 'image' && $this->_createThumb) {
                    $this->_thumbDir .= $ym . self::DS;
                    if (!is_dir($this->_thumbDir)) {
                        mkdir($this->_thumbDir, self::DIR_MODE, true);
                    }
                    $this->_thumbUrl .= $ym . self::DS;
                }
            }
            //检查是否已上传
            if (!is_uploaded_file($tmpName)) {
                throw new Exception("上传失败。");
            }
            //获得文件扩展名
            $pathParts = pathinfo($fileName);
            $fileExt = strtolower($pathParts['extension']);
            //检查扩展名
            if (!in_array($fileExt, $this->_extensions)) {
                throw new Exception("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $this->_extensions) . "格式。");
            }

            if ($this->_resourceType === 'image' && $this->_forceImageSize){
                if(!ImageHelper::checkSize($tmpName, $this->_imageWidth, $this->_imageHeight)){
                    throw new Exception("图片尺寸不正确(限制图片尺寸为宽:".$this->_imageWidth.'像素 高:'.$this->_imageHeight.'像素)');
                }
            }
            //新文件名
            $newFileName = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileExt;
            //移动文件
            $filePath = $this->_uploadDir . $newFileName;
            if (move_uploaded_file($tmpName, $filePath) === false) {
                throw new Exception("上传文件失败。");
            }
            @chmod($filePath, self::FILE_MODE);
            $fileUrl = $this->_uploadUrl . $newFileName;
            if ($this->_resourceType === 'image' && $this->_createThumb) {
                $options = array();
                $options['targetIsAbsolute'] = true;
                $options['targetFile'] = $this->_thumbDir . $newFileName;
                ImageHelper::createThumbnail($filePath, $this->_thumbWith, $this->_thumbHeight, false, $options);
            }

            $response = array();
            $response['error'] = 0;
            $response['url'] = $fileUrl;
            $this->_sendJson($response);
        } catch (Exception $e) {
            $this->_sendError($e->getMessage());
        }
    }

    /**
     * 对文件进行比较.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    private function _cmp($a, $b)
    {
        global $order;
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

    /**
     * 发送错误信息
     *
     * @param string $message
     */
    private function _sendError($message)
    {
        $response = array();
        $response['error'] = 1;
        $response['message'] = $message;
        $this->_sendJson($response);
    }

    /**
     * 路径中的别名转换.
     * @param string $path
     * @return bool|mixed|string
     */
    private function _getPathOfAlias($path)
    {
        return Yii::getAlias($path);
    }

    /**
     * 获取配置值.
     * 需要定义的配置有：
     * uploadPath：上传路径
     * uploadBaseUrl: 上传基本URL
     * imageFormat: 上传图片格式
     * flashFormat: 上传Flash格式
     * mediaFormat: 媒体文件格式
     * fileFormat: 上传文件格式
     * imageSize: 上传图片大小
     * flashSize: 上传Flash大小
     * mediaSize: 上传媒体文件大小
     * fileSize: 上传文件大小
     * ThumbWidth: 缩略图宽
     * ThumbHeight: 缩略图高
     * forceImageSize 强制限定图片大小为指定大小
     * imageWidth: 图片宽
     * imageHeight: 图片高
     *
     * @param string $name
     * @param mixed $default
     * @return string
     */
    private function _getConfig($name, $default = null)
    {
        if (isset($this->_config[$name])) {
            return $this->_config[$name];
        } else {
            return $default;
        }
    }

    /**
     * 以JSON格式输出数据.
     *
     * @param mixed $data
     */
    private function _sendJson($data)
    {
        ResponseHelper::sendJson($data);
    }
}