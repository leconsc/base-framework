//todo
function initFormWithUpload(formId, callback) {
    redirectUrl = redirectUrl || false;
    formId = formId || 'adminForm';

    $("#" + formId).validator().submit(function (e) {
        if (!e.isDefaultPrevented()) {
            $('#savebtn,#cancelbtn').button("option", "disabled", true);
            var nextStep = true;
            if (typeof callback == 'function') {
                nextStep = callback();
            }
            if (nextStep) {
                var submitFunction = function () {
                    $('#selectFiles').hide();
                    $('#' + formId).ajaxSubmit({
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'error') {
                                showMessage(response.message);
                                $('#savebtn,#cancelbtn').button("option", "disabled", false);
                                $('#selectFiles').show();
                            } else {
                                if (redirectUrl) {
                                    showMessage(response.message, null, false, function () {
                                        gotoUrl(redirectUrl);
                                    });
                                } else {
                                    showMessage(response.message);
                                }
                            }
                        }
                    });
                };
                try {
                    var stats = swfu.getStats();
                    if (stats.files_queued > 0) {
                        doSubmit(submitFunction, function () {
                            $('#savebtn,#cancelbtn').button("option", "disabled", false);
                            $('#selectFiles').show();
                        });
                    } else {
                        submitFunction();
                    }
                } catch (e) {
                    submitFunction();
                }
            }
            e.preventDefault();
        }
        return false;
    });
}
function initFlashUploadComponent(uploadUrl, sessionId, options) {
    options = options || {};
    var defaultOptions = {
        uploadFilesType: '*.zip',
        successReceiver: 'zip_path',
        serverResponseKey: 'path',
        uploadLimit: 10,
        queueLimit: 1,
        buttonText: '(10 MB Max)',
        overButtonText: false,
        limitSize: '10 MB',
        postName: "resume_file"
    };
    for (var optionName in defaultOptions) {
        options[optionName] = options[optionName] || defaultOptions[optionName];
    }
    swfu = new SWFUpload({
        // Backend settings
        upload_url: uploadUrl,
        post_params: {sid: sessionId},
        file_post_name: options.postName,

        // Flash file settings
        file_size_limit: options.limitSize,
        file_types: options.uploadFilesType,
        file_types_description: "All Files",
        file_upload_limit: options.uploadLimit,
        file_queue_limit: options.queueLimit,

        // Event handler settings
        file_queued_handler: fileQueued,
        file_queue_error_handler: fileQueueError,

        upload_start_handler: uploadStart,
        upload_progress_handler: uploadProgress,
        upload_error_handler: uploadError,
        upload_success_handler: uploadSuccess,
        upload_complete_handler: uploadComplete,

        // Button Settings
        button_image_url: "/assets/libs/swfupload/select_17x18.png",
        button_placeholder_id: "spanButtonPlaceholder",
        button_width: 180,
        button_height: 18,
        button_text: options.overButtonText ? '<span class="button">' + options.buttonText + '</span>' : '<span class="button">选择文件 <span class="buttonSmall">' + options.buttonText + '</span></span>',
        button_text_style: '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 12pt; }',
        button_text_top_padding: 0,
        button_text_left_padding: 18,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor: SWFUpload.CURSOR.HAND,

        // Flash Settings
        flash_url: "/assets/libs/swfupload/swf/swfupload.swf",
        flash9_url: "/assets/libs/swfupload/swf/swfupload_FP9.swf",

        custom_settings: {
            progressTarget: "fsUploadProgress",
            progressTargetContainer: "fsUploadProgressContainer",
            progressSiblingElement: "selectFiles",
            uploadSuccessful: false,
            successReceiver: options.successReceiver,
            serverResponseKey: options.serverResponseKey
        },
        // Debug settings
        debug: false
    });
    return swfu;
}