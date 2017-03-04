/**
 * App相关验证工具
 */
yii.validation = $.extend(yii.validation, {
    mobile: function (value, messages, options) {
        if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
            return;
        }
        var regexp = /^13[\d]{9}$|^14[57]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0678]{1}\d{8}$|^18[\d]{9}$/,
            matches = regexp.exec(value),
            valid = true;

        if (matches === null) {
            valid = false
        }
        if (!valid) {
            yii.validation.addMessage(messages, options.message, value);
        }
    }
});
