/**
* @purpose: 通用验证方法
*/
(function($) {
    $.validate = {
        isEmpty: function(data){
            return data.replace(/\s+/g, "") == '' ? true : false;
        },
        // 邮箱
        isEmail: function(data){
            var reg = new RegExp(/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i);
            return reg.test(data);
        },
        // ip地址
        isIp: function(data){
            var reg = new RegExp(/^\d+\.\d+\.\d+\.\d+$/);
            return reg.test(data);
        },
        // 身份证
        isId: function(data){
            var reg = new RegExp(/^\d{15}|\d{18}$/);
            return reg.test(data);
        },
        // 邮编
        isZip: function(data){
            var reg = new RegExp(/^\d{6}$/); // 有0开头的邮编 050043
            return reg.test(data);
        },
        // QQ
        isQQ: function(data){
            var reg = new RegExp(/^[1-9][0-9]{4,}$/);
            return reg.test(data);
        },
        // 数字
        isNaN: function(data){
            var reg = new RegExp(/^[0-9]\d*$/);
            return reg.test(data);
        },
        // 手机
        isMobile: function(data){
            var reg = new RegExp(/^1(3|4|5|6|7|8|9)[0-9]{1}\d{8}$/);
            return reg.test(data);
        },
        // 是否是密码
        isPassword : function (data) {
            return /^[\S]{6,16}$/.test(data);
        },
        // 是否拣货单号
        is_pick_sn : function (data) {
            var reg = new RegExp(/^JH\d{11}$/);
            return reg.test(data);
        },
        is_barcode : function (data) {
            var reg = new RegExp(/^[0-9A-Z]{3}\d{6}[0-9A-Z]{4}\d{4}$/);
            return reg.test(data);
        }
    }
})(jQuery);