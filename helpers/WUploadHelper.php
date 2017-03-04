<?php
/**
 * 百度webUploader 文件上传服务
 *
 * @author ChenBin
 * @version $Id: UploadService.php, 1.0 2016-06-27 09:34+100 ChenBin$
 * @package: Application\Services
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace app\helpers;

use Exception;

class WUploadHelper
{
    const MAX_FILE_AGE = 18000;

    private $_tmpDir;
    private $_uploadDir;
    private $_extensions = ['jpg', 'png', 'gif'];
    private $_fileSize = 1000 * 1024;
    private $_cleanupTargetDir = true;
    private $_filePrefix = '';

    /**
     * 初始化函数
     */
    protected function initialize(array $options)
    {
        @set_time_limit(0);
        if (isset($options['tmpDir'])) {
            $this->_tmpDir = $options['tmpDir'];
            if (!is_dir($this->_tmpDir)) {
                @mkdir($this->_tmpDir);
            }
        } else {
            $this->_tmpDir = "/tmp";
        }
        if (!isset($options['uploadDir'])) {
            throw new Exception('必须指有定有效的上传目录', 106);
        }
        $this->_uploadDir = $options['uploadDir'];
        if (!is_dir($this->_uploadDir)) {
            @mkdir($this->_uploadDir);
        }
        if (!is_dir($this->_tmpDir) || !is_writable($this->_tmpDir)) {
            throw new Exception('临时文件存放目录不存在或者不可写', 107);
        }
        if (!is_dir($this->_uploadDir) || !is_writable($this->_uploadDir)) {
            throw new Exception('上传文件存放目录不存在或者不可写', 107);
        }
        $this->_tmpDir = rtrim($this->_tmpDir, '/') . '/';
        $this->_uploadDir = rtrim($this->_uploadDir, '/') . '/';
        if(isset($options['extensions'])){
            if(is_string($options['extensions'])){
                $options['extensions'] = ArrayHelper::fromString(trim($options['extensions']));
            }
            $this->_extensions = $options['extensions'];
            if(!is_array($this->_extensions)){
                throw new Exception('无效的扩展名设定', 108);
            }
        }
        if(isset($options['fileSize'])){
            $this->_fileSize = intval($options['fileSize']) * 1024;
        }
        if(isset($options['filePrefix']) && is_string($options['filePrefix'])){
            $this->_filePrefix = $options['filePrefix'];
        }
    }

    /**
     * 清理目标目录.
     *
     * @param string $filePath
     * @param integer $chunk
     */
    protected function _cleanUpTargetDir($filePath, $chunk)
    {
        if ($this->_cleanupTargetDir) {
            if (!$dir = opendir($this->_tmpDir)) {
                throw new Exception('打开临时文件存储目录失败', 100);
            }

            while (($file = readdir($dir)) !== false) {
                $tmpFilePath = $this->_tmpDir . $file;
                // If temp file is current file proceed to the next
                if ($tmpFilePath == "{$filePath}_{$chunk}.part" || $tmpFilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpFilePath) < time() - self::MAX_FILE_AGE)) {
                    @unlink($tmpFilePath);
                }
            }
            closedir($dir);
        }
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        if(!empty($this->_filePrefix)) {
            $fileName = $this->_filePrefix . '_' . $fileName;
        }

        $tmpFileName = $this->_tmpDir . $fileName;
        $uploadFileName = $this->_uploadDir . $fileName;
        $pathinfo = pathinfo($uploadFileName);

        //检查文件名是否有效.
        $extension = strtolower($pathinfo['extension']);
        array_map('strtolower', $this->_extensions);
        if (!in_array($extension, $this->_extensions)) {
            throw new Exception('上传文件类型不正确.', 105);
        }

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

        $this->_cleanUpTargetDir($tmpFileName, $chunk);

        // Open temp file
        if (!$out = @fopen("{$tmpFileName}_{$chunk}.parttmp", "wb")) {
            throw new Exception('创建临时文件失败', 102);
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                throw new Exception('上传文件不可移动', 103);
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                throw new Exception('上传文件读取失败', 101);
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                throw new Exception('上传文件读取失败', 101);
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$tmpFileName}_{$chunk}.parttmp", "{$tmpFileName}_{$chunk}.part");

        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$tmpFileName}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (!$out = @fopen($uploadFileName, "wb")) {
                throw new Exception('上传文件写入失败', 102);
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$tmpFileName}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$tmpFileName}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
            $fileSize = filesize($uploadFileName);
            if($fileSize > $this->_fileSize){
                unlink($uploadFileName);
                throw new Exception('文件太大!', 150);
            }
        }
        $result = [
            'uploadFile' => $uploadFileName,
            'fileName' => $fileName,
            'done' => $done
        ];
        return $result;
    }

    /**
     * 文件上传处理.
     * @param array $options
     * @return array
     */
    public static function process(array $options)
    {
        $response = ['jsonrpc' => '2.0'];
        try {
            $uploadService = new self($options);
            $result = $uploadService->upload();
            $response['result'] = $result;
        } catch (Exception $e) {
            $response['error'] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        return $response;
    }
}