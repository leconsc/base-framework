var formElement;
var submitting = false;

/**
 * 初始化表单提交操作.
 *
 * @param {string} formId
 * @param {object} options
 */
function initFormRequest(formId, options) {
    options = options || {};

    $form = $('#' + formId);
    if ($form.length === 1) {
        var submitting = $form.data('submitting');
        $form.submit(function (e) {
            e.preventDefault();
            if (submitting) {
                return false;
            }
            $form.data('submitting', 1);
            var validate = options['validate'] || null;
            var beforeStart = options['beforeSubmit'] || null;
            var enableRequest = true;
            if ($.isFunction(validate)) {
                enableRequest = validate();
                if ($.type(enableRequest) !== 'boolean') {
                    enableRequest = true;
                }
            }
            if (enableRequest && $.isFunction(beforeStart)) {
                //发送Ajax请求开始之前,用于一些数据的初始化, 及条件判断,如果返回false, 请求不会被发送
                enableRequest = beforeStart();
                if ($.type(enableRequest) !== 'boolean') {
                    enableRequest = true;
                }
            }
            if (enableRequest) {
                var settings = {};
                var showMessage = options['showMessage'] || null;
                if ($.type(showMessage) !== 'boolean') {
                    showMessage = true;
                }

                settings['url'] = $form.attr('action');
                settings['type'] = $form.attr('method');
                settings['data'] = $form.serialize();
                //这里表示可以由外部提供表单之外的元素提交
                var data = options['data'] || {};
                extData = [];
                $.each(data, function (name, value) {
                    extData.push(name + '=' + value);
                });
                if (extData.length > 0) {
                    settings['data'] += '&' + extData.join('&');
                }
                settings['dataType'] = 'json';
                var complete = options['complete'] || null;
                var success = options['success'] || null;
                var error = options['error'] || null;
                settings['success'] = function (response) {
                    if (showMessage) {
                        if ($.isPlainObject(response)) {
                            if (response.status == 'error') {
                                messageBox(response.message, '错误', 'warn', error);
                            } else {
                                if ($.isFunction(success)) {
                                    success(response.data);
                                } else {
                                    if (response.message) {
                                        messageBox(response.message, '信息', 'info', function () {
                                            if (response.redirectUrl) {
                                                location.href = response.redirectUrl;
                                            }
                                        });
                                    } else if (response.redirectUrl) {
                                        location.href = response.redirectUrl;
                                    }
                                }
                            }
                        } else {
                            $.dialog.messageBox(response);
                        }
                    } else if ($.isFunction(success)) {
                        success(response);
                    }
                };
                var beforeSend = options['beforeSend'] || null;
                if ($.isFunction(beforeSend)) {
                    settings['beforeSend'] = beforeSend;
                }
                settings['error'] = function (xhr, status, error) {
                    var message = '内容：' + xhr.responseText + '<br />' + '错误：' + error;
                    if (showMessage) {
                        messageBox(message, '错误', 'warn', error);
                    } else if ($.isFunction(error)) {
                        error(message);
                    }
                };

                settings['complete'] = function (xhr, status) {
                    $form.data('submitting', 0);
                    if ($.isFunction(complete)) {
                        complete(xhr, status);
                    }
                };
                $.ajax(settings);
            }
            return false;
        });
    }
}

function remove(url, params, callback) {
    if ($.isFunction(params)) {
        callback = params;
        params = {};
    }
    confirm('确认删除？', function () {
        post(url, params, callback);
    });
}
function post(url, params, callback) {
    if ($.isFunction(params)) {
        callback = params;
        params = {};
    }
    $.post(url, params, function (response) {
        parseResponse(response, callback);
    });
}

function get(url, params, callback) {
    if ($.isFunction(params)) {
        callback = params;
        params = {};
    }
    $.get(url, params, function (response) {
        parseResponse(response, callback);
    });
}
function parseResponse(response, callback) {
    if ($.isPlainObject(response)) {
        if (response.status) {
            var func;
            if ($.isFunction(callback)) {
                func = function () {
                    callback(response.data);
                }
            } else {
                func = function () {
                    if(response.redirectUrl){
                        window.location.href = response.redirectUrl;
                    }else {
                        window.location.reload();
                    }
                }
            }
            if (response.status == 'error') {
                messageBox(response.message, '错误', 'warn', func);
            } else if (response.message) {
                messageBox(response.message, '信息', 'info', func);
            } else {
                func();
            }
        } else {
            if ($.isFunction(callback)) {
                callback(response);
            } else if (response.redirectUrl) {
                window.location.href = response.redirectUrl;
            }
        }
    } else {
        if ($.isFunction(callback)) {
            callback(response);
        } else {
            console.log(response);
        }
    }
}
function messageBox(message, title, status, callback) {
    if ($.dialog) {
        $.dialog.setState(status).messageBox(message, title, false, callback);
    } else if ($.weui) {
        $.weui.alert(message, title, callback);
    } else {
        alert(message);
    }
}

function confirm(message, callback) {
    if ($.dialog) {
        $.dialog.setState('remove').confirm(message, callback);
    } else if ($.weui) {
        $.weui.confirm(message, callback);
    }
}
function setForm(formId) {
    formElement = $('#' + formId)[0];
}

/**
 * 提交请求前的检查与预处理.
 *
 * @param {string} action
 * @returns {boolean}
 */
function submitButton(action) {
    if (submitting) {
        return false;
    }
    submitting = true;
    var execute = true;
    switch (action) {
        case "edit":
        case "remove":
        case "view":
        case "authorize":
            if ($(':hidden[name=boxChecked]').val() <= 0) {
                submitting = false;
                messageBox('请选择待操作条目！', '错误', 'warn');
                return false;
            }

            if (action == 'remove') {
                execute = false;
                confirm('确认删除？', function () {
                    submitForm(action);
                });
            }
            break;
        case "clean":
            execute = false;
            confirm('确认清空？', function () {
                submitForm(action);
            });
            break;
        case 'saveorder':
            var cbx;
            for (var i = 0; true; i++) {
                cbx = $('#cb' + i);
                if (!cbx.length) {
                    break;
                }
                cbx.attr('checked', true);
            }
            break;
        case 'export':
            submitting = false;
            break;
    }
    if (execute) {
        submitForm(action);
    } else {
        submitting = false;
    }
}
/**
 * Submit the form
 */
function submitForm(action) {
    var form = formElement || $(":hidden[name=boxChecked]")[0].form;
    form.action.value = action;
    $(form).submit();
}
/**
 * 对checkBox选择情况记录.
 *
 * @param isItChecked
 */
function isChecked(isItChecked) {
    var $boxChecked = $(":hidden[name=boxChecked]");
    var boxChecked = parseInt($boxChecked.val());
    if (isItChecked == true) {
        $boxChecked.val(boxChecked + 1);
    } else {
        $boxChecked.val(boxChecked - 1);
    }
    return true;
}
/**
 * 处理FlexiGrid请求.
 *
 * @param action
 * @param grid
 */
function handle(action, grid) {
    if (beforeHandle(action)) {
        submitButton(action);
    }
}

function beforeHandle(action) {
    return true;
}
/**
 * 调用一个动作.
 *
 * @param id
 * @param action
 * @returns {boolean}
 */
function executeAction(id, action) {
    var cb = $('#' + id);
    if (cb.length) {
        var cbx;
        for (var i = 0; true; i++) {
            cbx = $('#cb' + i);
            if (!cbx.length) {
                break;
            }
            cbx.prop('checked', false);
        }
        if (!cb.prop('disabled')) {
            cb.prop('checked', true);
            $(":hidden[name=boxChecked]").val(1);
        }
        if (beforeHandle(action)) {
            submitButton(action);
        }
    }
    return false;
}
/**
 * URL跳转.
 *
 * @param url
 */
function gotoUrl(url) {
    window.location = url;
}
function stopRefresh() {
    return "离开则本页信息不保留";
}
function settingBeforeUnload(setting) {
    if (setting) {
        window.onbeforeunload = stopRefresh;
    } else {
        window.onbeforeunload = null;
    }
}
