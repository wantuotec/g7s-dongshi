$(function(){
    /* =============通用JS========== */
    // 顶级域名
    window.DOMAIN = window.location.href.indexOf('dreamma.cn') > -1 ? 'dreamma.cn' : 'dreamma.dev';
    // 自动选择域名前缀
    window.PREFIX = '';
    if (/http(s?):\/\/dev/.test(window.location.href)) {
        PREFIX = 'dev';
    }

    $.DOMAIN = {
        HOME_DOMIN : 'http://' + PREFIX + 'www.'  + DOMAIN + '/',
        ADMIN      : 'http://' + PREFIX + 'www.'  + DOMAIN + '/admin',
        MAIN       : DOMAIN
    };

    $.HOME = {
        /**
         * 全选/反选checkbox
         * bind_target  绑定事件的目标     eg.'#select'
         * target       全选/反选的目标    eg.'input:checkbox'
         */
         selectCheckBox: function(bind_target,target){
            var bind_target = $(bind_target);
            var target = $(target);
            if(bind_target.length>0 && target.length>0){
                bind_target.click(function(){
                    if($(this).is(':checked')){
                        target.attr('checked','checked');
                    }
                     else {
                        target.removeAttr('checked');
                    }
               })
            }
         },

         /**
          * 得到被选中的 checkbox
          *
          * @params    string    input_name    需要得到的 name 名 [默认为 "check_all[]"]
          * @params    string    splits        分隔符 [默认为 ","]
          *
          * @return    string
          */
         get_check_all : function (input_name, splits) {
             if (!splits) splits = ',';
             if (!input_name) input_name = 'check_all[]';

             var checkbox_values = '';
             $("input[name='"+input_name+"'][type='checkbox']:checked").each(function (){
                 checkbox_values += $(this).val()+splits;
             })

             if (checkbox_values.length > 1) {
                 checkbox_values = checkbox_values.substring(0,checkbox_values.length-1);
             }

             return checkbox_values;
         },

         /*
          * 页面刷新
          */
         refresh : function () {
             window.location.reload();
         },

         /*
          * 父页面刷新
          */
         refresh_parent : function () {
             parent.window.location.reload();
         },

         /*
          * 打开新页面
          */
         redirect : function (url) {
             window.location.href = url;
         },

         /**
          * 弹出消息
          *
          * @params  string  message 提示信息
          *
          * @return  bool
          */
         msg: function(message){
             return alert(message);
         },

         /**
          * 弹出确认
          *
          * @params  string  message 确认信息
          *
          * @return  bool
          */
         confirm : function (message) {
             return confirm(message);
         },

         /**
          * 获取按键代码
          *
          * @params  object  event   事件对像
          *
          * @return  int
          */
         get_key_code : function (event) {
             return event.keyCode || event.which;
         },

         // 用于记录 layer 打开的窗口ID号
        layer_index : null,

         /**
          * 通用弹窗函数
          *
          * @params  string  type        弹窗类型 iframe 或 div
          * @params  string  target      iframe时为链接地址 div时为目标的ID 如#abc
          * @params  string  width       宽
          * @params  string  height      高
          * @params  boolean is_modal    是否模态窗口
          *
          * @return  int 弹窗的索引
          */
         open : function (type, target, width, height, is_modal, title) {
             var type     = type     || 'div';
             var width    = width    || 'auto';
             var height   = height   || 'auto';
             var is_modal = typeof(is_modal) != 'undefined' && false === is_modal ? false : true;
             var title    = title    || '';

             // ------------------------ 弹出窗自适应屏幕 START ------------------------
             var clientWidth = 0;
             var clientHeight = 0;
             var center_width = '50%';
             var center_height = 200;
             if (typeof window.innerHeight == 'undefined') {
                 clientWidth  = document.body.clientWidth;
                 if (document.documentElement.clientHeight > document.body.clientHeight){
                     clientHeight = document.documentElement.clientHeight;
                 } else {
                     clientHeight = document.body.clientHeight;
                 }
             } else {
                 clientWidth  = document.documentElement.clientWidth;
                 clientHeight = document.documentElement.clientHeight;
             }
             if(height != 'auto') {
                 height = height.replace('px', '');
             }
             if(width != 'auto') {
                 width = width.replace('px', '');
             }

             if (width != 'auto' && Number(width) > clientWidth) {
                 width = clientWidth * 0.9;
             }
             if (height != 'auto' && Number(height) > clientHeight) {
                 height = clientHeight * 0.9;
             }

             if(height != 'auto') {
                 center_height = (clientHeight - height) / 2;
             }
             if(width != 'auto') {
                 center_width = (clientWidth - width) / 2;
             }
             // ------------------------ 弹出窗自适应屏幕  END ------------------------

             var config = {
                 title  : [title           , false],
                 area   : [width+"px"      , height+"px"],
                 move   : ['.xubox_border' , true],
                 shade  : [0.2 , '#000000' , is_modal],
                 offset : [center_height+"px", center_width+"px"]
             };

             if ('iframe' == type) {
                 config.type   = 2;
                 config.content = target;
             } else {
                 config.type   = 1;
                 config.content = target;
             }

             $.HOME.layer_index = layer.open(config);
             return $.HOME.layer_index;
         },

         /**
          * 关闭使用 $.HOME.open 打开的窗口
          *
          * @params    index    调用 $.HOME.open 时的返回值
          */
         close : function (index) {
             layer.close(index);
         },

         /**
          * 关闭使用 $.HOME.open 打开的窗口 - 从父窗关闭子窗口
          *
          * @params    index    调用 $.HOME.open 时的返回值
          */
         close_parent : function (index) {
             parent.$.HOME.close_current();
         },

         /**
          * 关闭使用 $.HOME.open 打开的当前窗口(如果有打开了多个窗口需要使用close方法来关闭)
          *
          * @params    index    调用 $.HOME.open 时的返回值
          */
         close_current : function () {
             if ($.HOME.layer_index) {
                 layer.close($.HOME.layer_index);
             }
         },

         /**
          * 表单自动赋值
          * @params  object  jsons   json对象
          *
          * @return  void
          */
         form_init : function (jsons) {
             if (typeof jsons == 'object' && null != jsons) {
                 var input_type    = null;
                 var input_name    = null;
                 var select_name   = null;
                 var textarea_name = null;

                 // 遍历JSON
                 for (names in jsons) {
                     if (names) {
                         if ($("input[name='"+names+"']").attr("type") != 'undefined') {
                             input_type = $("input[name='"+names+"']").attr("type");
                             if ('text' == input_type || 'hidden' == input_type) {
                                 $("input[name='"+names+"']").val(jsons[names]);
                             } else if (input_type == 'radio') {
                                 $.HOME.set_radiobox(names,jsons[names]);
                             } else if (input_type == 'checkbox') {
                                 $.HOME.set_checkbox(names,jsons[names]);
                             }
                         }

                         if ($("input[name='"+names+"[]']").attr("type") === 'checkbox') {
                             $.HOME.set_checkbox(names+'[]',jsons[names]);
                         }

                         if ($("select[name='"+names+"']").length >= 1) {
                             $.HOME.set_select(names,jsons[names]);
                         }

                         if ($("textarea[name='"+names+"']").length >= 1) {
                             $.HOME.set_textarea(names,jsons[names]);
                         }
                     }
                 }
             }
         },

         /**
          * radio 自动选中
          *
          * @params  string  radio_name    需要选中的名称
          * @params  string  radio_value   需要选中的值
          *
          * @return  void
          */
         set_radiobox : function (radio_name, radio_value) {
             $("input[name='"+radio_name+"'][type='radio']").each(function() {
                 if ($(this).val() == radio_value) {
                     $(this).attr("checked","checked");
                 }
             });
         },

         /**
          * checkbox 自动选中
          *
          * @params  string  checkbox_name    需要选中的名称
          * @params  string  checkbox_value   需要选中的值
          * @params  string  splits           多个值使用的分隔符,默认为"|"
          *
          * @return  void
          */
         set_checkbox : function (checkbox_name, checkbox_value, splits) {
             if (checkbox_value) {
                 if (!splits) splits = ',';
                 checkbox_value += splits;
                 var values = checkbox_value.split(splits);

                 for (var i=0; i < values.length; i++) {
                     if (values[i]) {
                         $("input[name='"+checkbox_name+"'][type='checkbox']").each(function() {
                             if ($(this).val() == values[i]) {
                                 $(this).attr("checked", "checked");
                             }
                         });
                     }
                 }
             }
         },

         /**
          * selecte 自动选中
          *
          * @params  string  select_name    需要选中的名称
          * @params  string  select_value   需要选中的值
          *
          * @return  void
          */
         set_select : function (select_name, select_value) {
             var obj = $("select[name='"+select_name+"']");
             var select_count = $("select[name='"+select_name+"']").length;

             for (var i=0; i < select_count; i++) {
                 var option_count = $("option",obj[i]).length;

                 for(var j=0; j < option_count; j++)
                 {
                     if ($("option",obj[i]).get(j).value == select_value) {
                         $("option",obj[i]).get(j).selected = true;
                     }
                 }
             }
         },

         /**
          * textarea 自动赋值
          *
          * @params  string  text_name   需要赋值名称
          * @params  string  text_value   需要赋值的内容
          *
          * @return  void
          */
         set_textarea : function (text_name, text_value) {
             $("textarea[name='"+text_name+"']").val(text_value);
         },


    }

    /* =============返回顶部按钮效果-开始========== */
    $(window).scroll(function(){
        if ($(window).scrollTop() > 500) {
            $("#back_top").fadeIn(1000); //一秒渐入动画
        } else {
            $("#back_top").fadeOut(1000);//一秒渐隐动画
        }
    });

    $("#back_top").click(function() {
        $('body,html').animate({scrollTop:0},1000);
    });
    /* ===========返回顶部按钮效果-结束=========== */

    /* ==========留言板、文章评论：回复-开始======== */
    $("span.to-reply").click(function(){
        $('.to-reply-box').slideUp('fast');//初始化关闭所有回复框

        var btn_statu = $(this).html().trim();

        if (btn_statu === "评论") {
            $('.to-reply').html("评论");  //所有评论框恢复“评论”按钮
            $(this).html("收起");         //点击的评论框变成“收起”按钮
            $(this).parent().parent().find(".to-reply-box").slideDown('fast');
        } else if (btn_statu === "收起") {
            $('.to-reply').html("评论");  //所有评论框恢复“评论”按钮
            $(this).parent().parent().find(".to-reply-box").slideUp('fast');
        }

    });
    /* ==========留言板、文章评论：评论-结束========== */
});