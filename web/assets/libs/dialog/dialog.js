;
(function ($) {
    $.dialog = function () {
        var _state = 'state_icon info_icon';
        var _defaultMessageTitle = '信息';
        /**
         * 设置状态值.
         *
         * @param {string} state
         */
        function setState(state) {
            var states = ['warn', 'error', 'info', 'ask', 'ok', 'danger', 'remove', 'smile', 'cry', 'none'];
            if ($.inArray(state, states) != -1) {
                if (state == 'warn') {
                    _defaultMessageTitle = '错误';
                } else {
                    _defaultMessageTitle = '信息';
                }
            } else {
                state = 'info';
            }
            _state = 'dialog-icon-' + state;

            return $.dialog;
        }

        /**
         * 添加对话框图标代码
         */
        function addIcon() {
            var $dialogBody = $('.ui-dialog td.ui-dialog-body');
            if ($dialogBody.children().first()[0].tagName.toLowerCase() !== 'span') {
                $dialogBody.prepend('<span class="dialog-icon"></span>');
            }
            $dialogBody.children().first().removeClass().addClass('dialog-icon ' + _state);
        }

        /**
         * 移除对话框图标代码
         */
        function removeIcon() {
            var $dialogBody = $('.ui-dialog td.ui-dialog-body');
            if ($dialogBody.children().first()[0].tagName.toLowerCase() === 'span') {
                $dialogBody.children().first().remove();
            }
        }
        /**
         * 显示MessageBox 仅有关闭按钮.
         *
         * @param {string} message
         * @param {string} title
         * @param {boolean} quickClose
         * @param {callback} callback
         * @param width
         */
        function messageBox(message, title, quickClose, callback, width) {
            title = title || _defaultMessageTitle;
            width = width || 300;
            var options = {
                title: title,
                content: message,
                cancel: false,
                width: width,
                zIndex: 9999,
                okValue: '确定'
            };
            if(quickClose){
                options['quickClose'] = true;
            }
            options['ok'] = function(){
                if ($.isFunction(callback)) {
                    callback();
                }
            };

            var d = dialog(options);
            addIcon();
            d.showModal();
        }

        /**
         * 创建确认对话框.
         *
         * @param {string} message
         * @param {callback} callback
         * @param {string} title
         * @param {int} width
         */
        function confirm(message, callback, title, width) {
            title = title || "确认对话框";
            width = width || 350;
            var options = {
                title: title,
                content: message,
                width: width,
                zIndex: 9999,
                cancelValue: '取消',
                okValue: '确定',
                cancel: function () {}
            };
            options['ok'] = function(){
                if ($.isFunction(callback)) {
                    callback();
                }
            };
            var d = dialog(options);
            addIcon();
            d.showModal();
        }
        /**
         * 创建确认对话框无图标.
         *
         * @param {string} content
         * @param {callback} callback
         * @param {string} title
         * @param {int} width
         */
        function confirm2(content, callback, title, width) {
            title = title || "确认对话框";
            width = width || 350;
            var options = {
                title: title,
                content: content,
                width: width,
                zIndex: 9999,
                cancelValue: '取消',
                okValue: '确定',
                cancel: function () {}
            };
            options['ok'] = function(){
                if ($.isFunction(callback)) {
                    callback();
                }
            };
            var d = dialog(options);
            removeIcon();
            d.showModal();
        }
        /**
         * 创建信息弹出框.
         *
         * @param {string} content
         * @param {string} title
         * @param {callback} callback
         * @param {int} width
         */
        function popup(content, title, callback, width) {
            title = title || _defaultMessageTitle;
            width = width || 300;
            var options = {
                title: title,
                content: content,
                width: width,
                cancelValue: '关闭',
                okValue: '确定',
                zIndex: 9999,
                cancel: function () {}
            };
            options['ok'] = function(){
                if ($.isFunction(callback)) {
                    callback();
                }
            };
            var d = dialog(options);
            removeIcon();
            d.showModal();
        }

        return {
            setState: setState,
            messageBox: messageBox,
            confirm: confirm,
            confirm2: confirm2,
            popup: popup
        }
    }();
})(jQuery);
