// Called by the submit button to start the upload
function doSubmit(callback, errorCallback) {
    try {
        swfu.callback = callback;
        swfu.errorCallback = errorCallback;
        swfu.startUpload();
    } catch (ex) {
    }
    return false;
}
function fileQueueError(file, errorCode, message) {
    try {
        if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
            $.dialog.messageBox("你试图添加多个文件到上传队列，\n" + (message === 0 ? "但是你的上传队列已经到达最大值," : "你仅可以选择" + (message > 1 ? "上传" + message + " 文件。" : "一个文件。")));
            return;
        }
        createProgressTarget(this);
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                progress.setStatus("文件太大。");
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                progress.setStatus("不能上传零字节大小的文件");
                break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                progress.setStatus("无效的文件类型。");
                break;
            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                $.dialog.messageBox("你选择了太多的文件，" + (message > 1 ? "你仅能添加" + message + " 文件" : "你不能添加任何文件."));
                break;
            default:
                if (file !== null) {
                    progress.setStatus("未知错误");
                }
                break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}
function createProgressTarget(obj) {
    if ($('#' + obj.customSettings.progressTarget).length == 0) {
        $("#" + obj.customSettings.progressSiblingElement).html('<div id="' + obj.customSettings.progressTarget + '"></div>');
    }
}
function fileQueued(file) {
    try {
        createProgressTarget(this);
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("准备中...");
        progress.toggleCancel(true, this);
    } catch (e) {
        if (typeof this.errorCallback == 'function') {
            this.errorCallback();
        }
    }

}
function uploadStart(file) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("上传中...");
        progress.toggleCancel(false);
    } catch (ex) {
        if (typeof this.errorCallback == 'function') {
            this.errorCallback();
        }
    }
    return true;
}
function uploadProgress(file, bytesLoaded, bytesTotal) {
    try {
        var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setProgress(percent);
        progress.setStatus("上传中...");
    } catch (e) {
        if (typeof this.errorCallback == 'function') {
            this.errorCallback();
        }
    }
}

function uploadSuccess(file, serverData) {
    try {
        var response = eval("(" + serverData + ")");
        if (response.status == "error") {
            this.customSettings.uploadSucceeded = false;
            obj = this;
            $.dialog.messageBox(response.message, null, false, function () {
                if (typeof obj.errorCallback == 'function') {
                    obj.errorCallback();
                }
            });
            return false;
        } else {
            this.customSettings.uploadSucceeded = true;
            if (this.customSettings.successReceiver) {
                var responseData = this.customSettings.serverResponseKey ? response[this.customSettings.serverResponseKey] : response;
                if (typeof this.customSettings.successReceiver == 'function') {
                    this.customSettings.successReceiver(responseData);
                } else {
                    document.getElementById(this.customSettings.successReceiver).value = responseData;
                }
            }
            var progress = new FileProgress(file, this.customSettings.progressTarget);
            progress.setComplete();
            progress.setStatus("已完成");
            return true;
        }
    } catch (e) {
        if (typeof this.errorCallback == 'function') {
            this.errorCallback();
        }
        return false;
    }
}

function uploadComplete(file) {
    try {
        if (this.customSettings.uploadSucceeded) {
            if (this.getStats().files_queued !== 0) {
                this.startUpload();
            } else {
                if (typeof this.callback == 'function') {
                    this.callback();
                }
            }
        } else {
            var progress = new FileProgress(file, this.customSettings.progressTarget);
            progress.setError();
            progress.toggleCancel(false);
            progress.setStatus("上传错误");
            if (typeof this.errorCallback == 'function') {
                this.errorCallback();
            }
        }
    } catch (e) {
        if (typeof this.errorCallback == 'function') {
            this.errorCallback();
        }
    }
}
function uploadError(file, errorCode, message) {
    try {
        createProgressTarget(this);

        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
                progress.setStatus("上传错误: " + message);
                break;
            case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
                progress.setStatus("配置错误");
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
                progress.setStatus("上传失败");
                break;
            case SWFUpload.UPLOAD_ERROR.IO_ERROR:
                progress.setStatus("服务器(IO)错误");
                break;
            case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
                progress.setStatus("安全限制错误");
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                progress.setStatus("过强的安全限制错误");
                break;
            case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
                progress.setStatus("文件没有找到");
                break;
            case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
                progress.setStatus("验证失败，跳过");
                break;
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                if (this.getStats().files_queued === 0) {
                    document.getElementById(this.customSettings.cancelButtonId).disabled = true;
                }
                progress.setStatus("已取消");
                progress.setCancelled();
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                progress.setStatus("已停止");
                break;
            default:
                progress.setStatus("未知错误: " + error_code);
                break;
        }
    } catch (ex) {
        $.dialog.messageBox(ex);
    }
    if (typeof this.errorCallback == 'function') {
        this.errorCallback();
    }
}