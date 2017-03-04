/**
 * Created by ChenBin on 16/1/28.
 */
(function ($) {
    $.weui = function () {
        var $loadingToast;
        var $toast;

        var confirmDialogTemplate = '<div class="weui_dialog_confirm">'
            + '<div class="weui_mask"></div>'
            + '<div class="weui_dialog">'
            + '<div class="weui_dialog_hd"><strong class="weui_dialog_title">{title}</strong></div>'
            + '<div class="weui_dialog_bd">{content}</div>'
            + '<div class="weui_dialog_ft">'
            + '<a href="javascript:void(0);" class="weui_btn_dialog default">{cancel}</a>'
            + '<a href="javascript:void(0);" class="weui_btn_dialog primary">{ok}</a>'
            + '</div>'
            + '</div>'
            + '</div>';
        var alertDialogTemplate = '<div class="weui_dialog_alert">'
            + '<div class="weui_mask"></div>'
            + '<div class="weui_dialog">'
            + '<div class="weui_dialog_hd"><strong class="weui_dialog_title">{title}</strong></div>'
            + '<div class="weui_dialog_bd">{content}</div>'
            + '<div class="weui_dialog_ft">'
            + '<a href="javascript:void(0);" class="weui_btn_dialog primary">{ok}</a>'
            + '</div>'
            + '</div>'
            + '</div>';
        var loadingToastTemplate = '<div id="loadingToast" class="weui_loading_toast" style="display:none;">'
            + '<div class="weui_mask_transparent"></div>'
            + '<div class="weui_toast">'
            + '<div class="weui_loading">'
            + '<div class="weui_loading_leaf weui_loading_leaf_0"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_1"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_2"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_3"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_4"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_5"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_6"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_7"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_8"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_9"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_10"></div>'
            + '<div class="weui_loading_leaf weui_loading_leaf_11"></div>'
            + '</div>'
            + '<p class="weui_toast_content">{text}</p>'
            + '</div>'
            + '</div>';
        var toastTemplate = '<div id="toast" style="display: none;">'
            + '<div class="weui_mask_transparent"></div>'
            + '<div class="weui_toast">'
            + '<i class="weui_icon_toast"></i>'
            + '<p class="weui_toast_content">{text}</p>'
            + '</div>'
            + '</div>';
        var msgPageTemplate = '<div class="weui_msg">'
            + '<div class="weui_icon_area"><i class="weui_icon_success weui_icon_msg"></i></div>'
            + '<div class="weui_text_area">'
            + '<h2 class="weui_msg_title">{title}</h2>'
            + '<p class="weui_msg_desc">{content}</p>'
            + '</div>'
            + '<div class="weui_opr_area">'
            + '<p class="weui_btn_area">'
            + '<a href="javascript:void(0)" class="weui_btn weui_btn_primary">{ok}</a>'
            + '<a href="javascript:void(0)" class="weui_btn weui_btn_default">{cancel}</a>'
            + '</p>'
            + '</div>'
            + '<div class="weui_extra_area">'
            + '<a href="javascript:void(0)">{detail}</a>'
            + '</div>'
            + '</div>';
        var actionSheetTemplate = '<div id="actionSheet_wrap">'
            + '<div class="weui_mask_transition" id="mask"></div>'
            + '<div class="weui_actionsheet" id="weui_actionsheet">'
            + '<div class="weui_actionsheet_menu">{menu_list}</div>'
            + '<div class="weui_actionsheet_action">'
            + '<div class="weui_actionsheet_cell" id="actionsheet_cancel">{cancel}</div>'
            + '</div>'
            + '</div>'
            + '</div>';

        /**
         * 显示确认信息对话框.
         *
         * @param {string} content
         * @param {callback} okHandler
         * @param {string} title
         * @param {string} okText
         * @param {callback} cancelHandler
         * @param {string} cancelText
         */
        function confirm(content, okHandler, title, okText, cancelHandler, cancelText) {
            title = title || '确认';
            okText = okText || '确定';
            cancelText = cancelText || '取消';

            var confirmDialog = replace(confirmDialogTemplate, {
                'title': title,
                'cancel': cancelText,
                'ok': okText,
                'content': content
            });

            $confirmDialog = $(confirmDialog);
            $confirmDialog.find('.weui_dialog_ft > .default').one('click', function () {
                if ($.isFunction(cancelHandler)) {
                    cancelHandler();
                }
                $confirmDialog.remove();
            });
            $confirmDialog.find('.weui_dialog_ft > .primary').one('click', function () {
                if ($.isFunction(okHandler)) {
                    okHandler();
                }
                $confirmDialog.remove();
            });
            $(document.body).append($confirmDialog);
        }

        /**
         * 显示警告信息框.
         *
         * @param {string} content
         * @param {string} title
         * @param {callback} okHandler
         * @param {string} okText
         */
        function alert(content, title, okHandler, okText) {
            if ($.isFunction(title)) {
                okHandler = title;
                title = null;
            }
            title = title || '错误';
            okText = okText || '确定';

            var alertDialog = replace(alertDialogTemplate, {
                'title': title,
                'ok': okText,
                'content': content
            });

            $alertDialog = $(alertDialog);
            $alertDialog.find('.weui_dialog_ft > .primary').one('click', function () {
                if ($.isFunction(okHandler)) {
                    okHandler();
                }
                $alertDialog.remove();
            });
            $(document.body).append($alertDialog);
        }

        /**
         * 显示Loadding Toast.
         *
         * @param {string} text
         */
        function toggleLoadingToast(text) {
            $loadingToast = $loadingToast || $('#loadingToast');

            text = text || '数据加载中';

            if ($loadingToast.length > 0) {
                if ($loadingToast.css('display') == 'none') {
                    $loadingToast.find('p.weui_toast_content').html(text);
                    $loadingToast.show();
                } else {
                    $loadingToast.hide();
                }
            } else {
                var loadingToast = replace(loadingToastTemplate, {
                    'text': text
                });
                $(document.body).append(loadingToast);
                $loadingToast = $('#loadingToast');
                $loadingToast.show();
            }
        }

        /**
         * 显示Toast.
         *
         * @param {string} text
         */
        function showToast(text) {
            $toast = $toast || $('#toast');
            text = text || '已完成';
            if ($toast.length > 0) {
                $toast.find('p.weui_toast_content').html(text);
            } else {
                var toast = replace(toastTemplate, {
                    'text': text
                });
                $(document.body).append(toast);
                $toast = $('#toast');
            }
            $toast.show();
            setTimeout(function () {
                $toast.hide();
            }, 2000);
        }

        /**
         * 显示Msg Page.
         *
         * @param {string} content
         * @param {callback} okHandler
         * @param {callback} cancelHandler
         * @param {callback} detailHandler
         * @param {object} options
         */
        function showMsgPage(content, okHandler, cancelHandler, detailHandler, options) {
            options = options || {};
            var title = options.title || '操作成功';
            var okText = options.okText || '确定';
            var cancelText = options.cancelText || '取消';
            var detailText = options.detailText || '查看详情';

            var msgPage = replace(msgPageTemplate, {
                'title': title,
                'cancel': cancelText,
                'ok': okText,
                'detail': detailText,
                'content': content
            });

            $msgPage = $(msgPage);
            $msgPage.find('.weui_btn_area > .weui_btn_default').one('click', function () {
                if ($.isFunction(cancelHandler)) {
                    cancelHandler();
                }
                $msgPage.remove();
            });
            $msgPage.find('.weui_btn_area > .weui_btn_primary').one('click', function () {
                if ($.isFunction(okHandler)) {
                    okHandler();
                }
                $msgPage.remove();
            });
            $msgPage.find('.weui_extra_area a').click(function () {
                if ($.isFunction(detailHandler)) {
                    detailHandler();
                }
            });
            $(document.body).append($msgPage);

        }

        /**
         * 显示Action Sheet.
         *
         * @param {object} menus
         * @param {callback} cancelHandler
         * @param {string} cancelText
         * @param {callback} callback
         */
        function showActionSheet(menus, cancelHandler, cancelText, callback) {
            var menuList = '';
            for (var key in menus) {
                menuList += '<div class="weui_actionsheet_cell" data-menu-id="' + key + '">' + menu.text + '</div>';
            }
            cancelText = options.cancelText || '取消';
            var actionSheet = replace(actionSheetTemplate, {
                'menu_list': menuList,
                'cancel': cancelText
            });
            $actionSheet = $(actionSheet);
            $actionSheet.find('#actionsheet_cancel').one('click', function () {
                if ($.isFunction(cancelHandler)) {
                    cancelHandler();
                }
                $actionSheet.remove();
            });

            if ($.isFunction(callback)) {
                $actionSheet.find('.weui_actionsheet_cell').click(function () {
                    callback($(this).data('menu-id'));
                    $actionSheet.remove();
                });
            }
            $(document.body).append($actionSheet);
        }

        /**
         * 字符串替换.
         *
         * @param {string} str
         * @param {object} replacePairs
         * @returns {*}
         */
        function replace(str, replacePairs) {
            for (var key in replacePairs) {
                str = str.replace('{' + key + '}', replacePairs[key]);
            }
            return str;
        }

        return {
            confirm: confirm,
            alert: alert,
            toggleLoadingToast: toggleLoadingToast,
            showToast: showToast,
            showMsgPage: showMsgPage,
            showActionSheet: showActionSheet
        }
    }();
})(jQuery);