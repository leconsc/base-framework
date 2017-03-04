<?php
/**
 * 下载助手
 *
 * @author ChenBin
 * @version $Id:DownloadHelper.php, v1.0 2016-12-30 14:30 ChenBin $
 * @package
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin,all rights reserved.
 */

namespace app\helpers;


class DownloadHelper
{
    /**
     * 实现文件下载.
     *
     * @param $fileDir
     * @param $filename
     * @return bool
     */
    public static function download($fileName, $fancyName = '', $forceDownload = true, $speedLimit = 0, $contentType = '')
    {
        if (!is_readable($fileName)) {
            header("HTTP/1.1 404 Not Found");
            return false;
        }

        $fileStat = stat($fileName);
        $lastModified = $fileStat['mtime'];

        $md5 = md5($fileStat['mtime'] . '=' . $fileStat['ino'] . '=' . $fileStat['size']);
        $etag = '"' . $md5 . '-' . crc32($md5) . '"';

        header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
        header("ETag: $etag");

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }

        if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }

        if ($fancyName == '') {
            $fancyName = basename($fileName);
        }

        if ($contentType == '') {
            $contentType = 'application/octet-stream';
        }

        $fileSize = $fileStat['size'];

        $contentLength = $fileSize;
        $isPartial = false;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
                $startPos = $matches[1];
                $endPos = $matches[2];

                if ($startPos == '' && $endPos == '') {
                    return false;
                }

                if ($startPos == '') {
                    $startPos = $fileSize - $endPos;
                    $endPos = $fileSize - 1;
                } else if ($endPos == '') {
                    $endPos = $fileSize - 1;
                }

                $startPos = $startPos < 0 ? 0 : $startPos;
                $endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;

                $length = $endPos - $startPos + 1;

                if ($length < 0) {
                    return false;
                }

                $contentLength = $length;
                $isPartial = true;
            }
        }

        // send headers
        if ($isPartial) {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $startPos-$endPos/$fileSize");

        } else {
            header("HTTP/1.1 200 OK");
            $startPos = 0;
            $endPos = $contentLength - 1;
        }

        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-type: ' . $contentType);
        header('Content-Length: ' . $contentLength);

        if ($forceDownload) {
            header('Content-Disposition: attachment; filename="' . $fancyName . '"');
        }

        header("Content-Transfer-Encoding: binary");

        $bufferSize = 2048;

        if ($speedLimit != 0) {
            $packetTime = floor($bufferSize * 1000000 / $speedLimit);
        }

        $bytesSent = 0;
        $fp = fopen($fileName, "rb");
        fseek($fp, $startPos);
        while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0) {
            if ($speedLimit != 0) {
                list($usec, $sec) = explode(" ", microtime());
                $outputTimeStart = ((float)$usec + (float)$sec);
            }

            $readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
            $buffer = fread($fp, $readBufferSize);

            echo $buffer;

            ob_flush();
            flush();

            $bytesSent += $readBufferSize;

            if ($speedLimit != 0) {
                list($usec, $sec) = explode(" ", microtime());
                $outputTimeEnd = ((float)$usec + (float)$sec);

                $useTime = ((float)$outputTimeEnd - (float)$outputTimeStart) * 1000000;
                $sleepTime = round($packetTime - $useTime);
                if ($sleepTime > 0) {
                    usleep($sleepTime);
                }
            }
        }
        return true;
    }
}