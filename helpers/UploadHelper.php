<?php

/**
 * 上傳處理
 *
 * @author leconsc
 * @version $Id:Upload.php, v1.0 12-7-2 07:01+100 leconsc $
 * @package LitePHP
 * @copyright leconsc
 */
final class UploadHelper
{
    const UPLOAD_ERR_FILE_LARGE = 1;
    const UPLOAD_ERR_FORM_SIZE = 2;
    const UPLOAD_ERR_PARTIAL = 3;
    const UPLOAD_ERR_NO_FILE = 4;
    const UPLOAD_ERR_NO_TMP_DIR = 6;
    const UPLOAD_ERR_CANT_WRITE = 7;
    const MISSING_DIR = 8;
    const IS_NOT_DIR = 9;
    const NO_WRITE_PERMS = 9;
    const BAD_FORM = 10;
    const E_FAIL_COPY = 11;
    const E_FAIL_MOVE = 12;
    const FILE_EXISTS = 12;
    const CANNOT_OVERWRITE = 13;
    const NOT_ALLOWED_EXTENSION = 14;
    const DEV_NO_DEF_FILE = 15;
    const UPLOAD_ERROR = 16;
    const ALLOW = 'allow';
    const DENY = 'deny';

    /** @var int Contains the desired chmod for uploaded files */
    private $_chmod = 0660;
    /** @var int 允许上传文件最大尺寸 */
    private $_maxSize;
    /** @var string 請求內容類型 */
    private $_contentType;
    /** @var array 上傳文件基本信息 */
    private $_postFile;
    /** @var array It's a common security risk in pages who has the upload dir under the document root (remember the hack of the Apache web?) */
    private $_extensionsCheck = array('php', 'phtm', 'phtml', 'php3', 'inc');
    private $_extensionsMode = self::DENY;

    /**@var array 錯誤信息定義 */
    private $_errors = array(
        self::UPLOAD_ERR_FILE_LARGE => '文件太大，文件最大尺寸是：%s个字节.',
        self::UPLOAD_ERR_FORM_SIZE => '文件太大，文件最大尺寸是：%s个字节.',
        self::UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
        self::UPLOAD_ERR_NO_FILE => '你没有选择任何上传档!',
        self::UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        self::UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        self::MISSING_DIR => '不正确的目的档案夹。',
        self::IS_NOT_DIR => '目的档案夹不存在或者是一个档',
        self::NO_WRITE_PERMS => '目的档案夹不可写!',
        self::BAD_FORM => 'HTML表单没有包含“method="post" enctype="multipart/form-data"',
        self::E_FAIL_COPY => '复制暂存档案失败!',
        self::E_FAIL_MOVE => '文件不可移动!',
        self::FILE_EXISTS => '目的档案已存在.',
        self::CANNOT_OVERWRITE => '目的档案已存在，不允许被覆盖.',
        self::NOT_ALLOWED_EXTENSION => '无效的档案类型.',
        self::UPLOAD_ERROR => '上传错误：',
        self::DEV_NO_DEF_FILE => '在表单中档案名没有定义: &lt;input type="file" name=?&gt;.'
    );


    /**
     * 構造函數，初始化一系列文件上傳參數
     *
     * @param string $file 上傳文件在表單中的名字
     * @param int $index 當通過同一個名字上傳多個文件時，標識文件位置
     */
    public function __construct($file, $index = 0)
    {
        $this->setMaxSize();
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $this->_contentType = $_SERVER['CONTENT_TYPE'];
        }
        if (!isset($this->_contentType) || strpos($this->_contentType, 'multipart/form-data') !== 0) {
            $this->_throwException(self::BAD_FORM);
        }
        $postFiles =& $_FILES;

        if (count($postFiles) < 1) {
            $this->_throwException(self::UPLOAD_ERR_NO_FILE);
        }
        if (!isset($postFiles[$file])) {
            $this->_throwException(self::DEV_NO_DEF_FILE);
        }

        $postFile =& $_FILES[$file];
        $this->_postFile = array();
        if (is_array($postFile['name'])) {
            if (!isset($postFile['name'][$index])) {
                $this->_throwException(self::DEV_NO_DEF_FILE);
            }
            if (isset($postFile['error'][$index]) && $postFile['error'][$index] != 0) {
                $this->_throwException($postFile['error'][$index]);
            }
            $this->_postFile['name'] = $postFile['name'][$index];
            $this->_postFile['tmp_name'] = $postFile['tmp_name'][$index];
            $this->_postFile['size'] = $postFile['size'][$index];
            $this->_postFile['type'] = $postFile['type'][$index];
        } else {
            if (isset($postFile['error']) && $postFile['error'] != 0) {
                $this->_throwException($postFile['error']);
            }
            $this->_postFile['name'] = $postFile['name'];
            $this->_postFile['tmp_name'] = $postFile['tmp_name'];
            $this->_postFile['size'] = $postFile['size'];
            $this->_postFile['type'] = $postFile['type'];
        }
        $this->_postFile['name'] = date('Ymd_') . $this->_postFile['name'];
        if (($pos = strrpos($this->_postFile['name'], '.')) !== false) {
            $this->_postFile['ext'] = strtolower(substr($this->_postFile['name'], $pos + 1));
        }
    }

    /**
     * 設置允許上傳文件的大小.
     *
     * @access public
     * @param int $maxSize 上傳文件大小
     */
    public function setMaxSize($maxSize = null)
    {
        if (!is_numeric($maxSize)) {
            if (isset($_POST['MAX_FILE_SIZE'])) {
                $maxSize = intval($_POST['MAX_FILE_SIZE']);
            }
        }

        $iniSize = preg_replace('/m/i', '000000', ini_get('upload_max_filesize'));
        if (empty($maxSize) || ($maxSize > $iniSize)) {
            $maxSize = $iniSize;
        }
        $this->_maxSize = $maxSize;

        if (isset($this->_postFile['size']) && $this->_postFile['size'] > $this->_maxSize) {
            throw new Exception(sprintf($this->_errors[self::UPLOAD_ERR_FILE_LARGE], $this->_maxSize));
        }
    }

    /**
     * 拋出錯誤信息.
     *
     * @param $errorNo
     */
    private function _throwException($errorNo)
    {
        if (isset($this->_errors[$errorNo])) {
            throw new Exception($this->_errors[$errorNo]);
        } else {
            throw new Exception($this->_errors[self::UPLOAD_ERROR]);
        }
    }

    /**
     * Sets the chmod to be used for uploaded files
     *
     * @param int $mode mode
     */
    public function setChmod($mode)
    {
        $this->_chmod = $mode;
    }

    /**
     * Sets the name of the destination file
     *
     * @access public
     * @param string $name file name
     */
    public function setName($name)
    {
        if (!empty($name) && is_string($name)) {
            $this->_postFile['name'] = (string)$name;
        }
    }

    /**
     * Retrive properties of the uploaded file
     * @param string $name The property name. When null an assoc array with
     *                       all the properties will be returned
     * @return mixed         A string or array
     * @access public
     */
    function getProp($name = null)
    {
        if ($name === null) {
            return $this->_postFile;
        }
        return $this->_postFile[$name];
    }

    /**
     * Moves the uploaded file to its destination directory.
     *
     * @param    string $dir_dest Destination directory
     * @param    bool $overwrite Overwrite if destination file exists?
     * @return   mixed   True on success or Pear_Error object on error
     * @access public
     */
    function moveTo($destDir, $overwrite = true)
    {
        //Valid extensions check
        if (!$this->_evalValidExtensions()) {
            $this->_throwException(self::NOT_ALLOWED_EXTENSION);
        }
        $errCode = $this->_checkDestDir($destDir);
        if ($errCode !== false) {
            $this->_throwException($errCode);
        }

        $descFile = $destDir . DIRECTORY_SEPARATOR . $this->_postFile['name'];

        if (@is_file($descFile)) {
            if ($overwrite !== true) {
                $this->_throwException(self::FILE_EXISTS);
            } elseif (!is_writable($descFile)) {
                $this->_throwException(self::CANNOT_OVERWRITE);
            }
        }

        // copy the file and let php clean the tmp
        if (!@move_uploaded_file($this->_postFile['tmp_name'], $descFile)) {
            $this->_throwException(self::E_FAIL_MOVE);
        }
        @chmod($descFile, $this->_chmod);
        return $this->_postFile['name'];
    }

    /**
     * Check for a valid destination dir
     *
     * @param    string $dir_dest Destination dir
     * @return   mixed   False on no errors or error code on error
     */
    function _checkDestDir($destDir)
    {
        if (!$destDir) {
            return self::MISSING_DIR;
        }
        if (!@is_dir($destDir)) {
            return self::IS_NOT_DIR;
        }
        if (!is_writeable($destDir)) {
            return self::NO_WRITE_PERMS;
        }
        return false;
    }


    /**
     * Function to restrict the valid extensions on file uploads
     *
     * @param array $exts File extensions to validate
     * @param string $mode The type of validation:
     *                       1) 'deny'   Will deny only the supplied extensions
     *                       2) 'accept' Will accept only the supplied extensions
     *                                   as valid
     * @access public
     */
    public function setValidExtensions($exts, $mode = self::DENY)
    {
        $this->_extensionsCheck = $exts;
        $this->_extensionsMode = $mode;
    }

    /**
     * Evaluates the validity of the extensions set by setValidExtensions
     *
     * @return bool False on non valid extension, true if they are valid
     * @access private
     */
    public function _evalValidExtensions()
    {
        $exts = $this->_extensionsCheck;
        settype($exts, 'array');

        $ext = isset($this->_postFile['ext']) ? $this->_postFile['ext'] : '';

        $regex = str_replace(array('\\*', ';'), array('.*', '|'), preg_quote(join(';', $exts), '/'));
        $matched = in_array($ext, $exts) || preg_match('/^' . $regex . '$/i', $this->_postFile['name']);
        if ($this->_extensionsMode == self::DENY) {
            if ($matched) {
                return false;
            }
            // mode == 'accept'
        } else {
            if (!$matched) {
                return false;
            }
        }
        return true;
    }
}
