/**
 * 后台通用 js
 */
(function($){
    // 顶级域名
    window.DOMAIN = window.location.href.indexOf('dreamma.cn') > -1 ? 'dreamma.cn' : 'dreamma.dev';
    // 自动选择域名前缀
    window.PREFIX = '';
    if (/http(s?):\/\/dev/.test(window.location.href)) {
        PREFIX = 'dev';
    }

    $.DOMAIN = {
        WWW  : 'http://' + PREFIX + 'www.'  + DOMAIN + '/',
        MAIN : DOMAIN
    };

    $.BKD = {
        /**
         * 滑动菜单
         */
        slide: function(id){
            $("#main-nav a.nav-top-item").hover(
                function () {
                    $(this).stop().animate({ paddingLeft: "25px" }, 200);
                },
                function () {
                    $(this).stop().animate({ paddingLeft: "15px" });
                }
            );
        },

        /**
         * 滑动菜单
         */
        siderbar: function(id){
            var $obj = $('#'+id).find('li:has(ul)');
            if($obj.length>0){
                // 切换选中菜单
                $obj.find('>ul>li>a').click(function(){
                    $obj.find('.current').removeClass('current');
                    $(this).addClass('current');
                    $(this).parent().parent().prev('.nav-top-item').addClass('current');
                });

                // 打开关闭二级菜单
                $obj.find('>.nav-top-item').click(function(){
                    // 先关闭所有打开的菜单栏目
                    $obj.find('>ul').slideUp('fast');

                    var $targetObj = $(this).next('ul');
                    if($targetObj.css('display')=='none'){
                        $targetObj.slideDown('fast');
                        $.BKD.syncHeight();
                        return false;
                    }else{
                        $targetObj.slideUp('fast');
                        $.BKD.syncHeight();
                        return false;
                    }
                    }
               );
            }
        },

        /**
         * 关闭提示信息
         */
        cloesNotice: function(){
            var $obj = $('.notification a.close');
            $obj.click(function(){
                $(this).parent().hide();
                return false;
            })
        },

        /**
         * 一起滚
         */
        scroll: function(){
            $(window).scroll(function(){
                var scroll_height = $(document).scrollTop();
                $(window.parent.frames["siderbar"]).scrollTop(scroll_height);
            });
        },

        /**
         * 一样高
         */
        syncHeight: function(){
            if(parent.frames["siderbar"] && parent.frames["content"]){
                var side    = $('#sidebar',parent.frames["siderbar"].document);
                var content = $('body',parent.frames["content"].document);
                var side_height    = parseInt(side.height());
                var content_height = parseInt(content.height());

                sync_height = side_height > content_height ? side_height : content_height ;
                content.height(sync_height);
            }
        },

        /**
         * 水平折叠指定框架
         */
        foldFrame: function(id,frameId,cols){
            var $fold = $('#'+id).find('.fold');
            var defaultCols = parent.document.getElementById(frameId).cols;
            if($fold.length>0){
                $fold.click(function(){
                    $fold.hide();
                    $open.show();
                    parent.document.getElementById('siderbar').scrolling="no";
                    $('#sidebar-wrapper').hide();
                    $('#sidebar').width('10px');
                    parent.document.getElementById(frameId).cols = cols;
                    return false;
                })
            };
            $open = $('#'+id).find('.open');
            if($open.length>0){
                $open.click(function(){
                    $open.hide();
                    $fold.show();
                    parent.document.getElementById('siderbar').scrolling="auto";
                    $('#sidebar-wrapper').show();
                    $('#sidebar').width('225px');
                    parent.document.getElementById(frameId).cols = defaultCols;
                    return false;
                })
            }
        },

        /**
         * 表格样式
         */
         tableClass: function(){
            $('.content-box-content table.list tbody tr:even').addClass("alt-row");
            $(".content-box-content table.list tbody tr").hover(
                function () {
                    $(this).addClass('over');
                },
                function () {
                    $(this).removeClass('over');
                }
            );
            $(".content-box-content table.mouseover tbody tr").hover(
                function () {
                    $(this).addClass('over');
                },
                function () {
                    $(this).removeClass('over');
                }
            );

            $('.search-content_1 table tbody tr').each(function(){


               if($(this).find("td").length == 6) {
                   $('.search-content table tbody tr td').css({"width":"20%"});
                   $('.search-content table tbody tr td:even').css({"text-align":"right", "width":"7%"});
               } else if($(this).find("td").length == 4) {
                   $('.search-content table tbody tr td').css({"width":"20%"});
                   $('.search-content table tbody tr td:even').css({"text-align":"right", "width":"10%"});
               }
            });
         },

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
         * 获取按键代码
         *
         * @params  object  event   事件对像
         *
         * @return  int
         */
        get_key_code : function (event) {
            return event.keyCode || event.which;
        },

        /**
         * 是否是 Tab 或 Enter 按键
         *
         * @params  object  event   事件对像
         *
         * @return  boolean
         */
        is_tab_enter : function (event) {
            return (9 === $.BKD.get_key_code(event) || 13 === $.BKD.get_key_code(event)) ? true : false;
        },

        /**
         * 四舍五入
         *
         * @params  mixed   number      数字
         * @params  int     precision   精度
         *
         * @return  int 弹窗的索引
         */
        round : function (number, precision) {
            var precision = precision || 0;
            return (new Number(number)).toFixed(precision);
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

            $.BKD.layer_index = layer.open(config);
            return $.BKD.layer_index;
        },

        /**
         * 关闭使用 $.BKD.open 打开的窗口
         *
         * @params    index    调用 $.BKD.open 时的返回值
         */
        close : function (index) {
            layer.close(index);
        },

        /**
         * 关闭使用 $.BKD.open 打开的窗口 - 从父窗关闭子窗口
         *
         * @params    index    调用 $.BKD.open 时的返回值
         */
        close_parent : function (index) {
            parent.$.BKD.close_current();
        },

        /**
         * 关闭使用 $.BKD.open 打开的当前窗口(如果有打开了多个窗口需要使用close方法来关闭)
         *
         * @params    index    调用 $.BKD.open 时的返回值
         */
        close_current : function () {
            if ($.BKD.layer_index) {
                layer.close($.BKD.layer_index);
            }
        },

        /**
         * Tips 提示
         *
         * @params  object  obj      目标对象
         * @params  string  msg      提示内容
         * @params  string  time     自动关闭时间,单位：秒  , 如果为 true 则需要点击按钮才能关闭
         * @params  string  position 位置，在哪个位置展示 [top、right、bottom、left]
         * @params  int     maxWidth Tips 长度 (单位：px)
         * @params  string  color    内容字体颜色 (颜色码 例：red、#ccc)
         * @params  string  backgroundColor 背景颜色 (颜色码 例：red、#ccc)
         *
         * @return  void|bool
         */
        tips : function(obj, msg, time, position, maxWidth, color, backgroundColor) {
            if (typeof obj != 'object') {
                $.BKD.msg("第一个参数类型必须为对象！");
                return false;
            }

            if (!msg) msg = '嗨，我是 Tips';
            if (!time) time = 2;
            if (!maxWidth) maxWidth = 'auto';
            if (!color) color = '#fff';
            if (!backgroundColor) backgroundColor = '#78BA32';

            switch (position) {
                case 'top' :
                    position = 0;
                    break;

                case 'left' :
                    position = 3;
                    break;

                case 'right' :
                    position = 1;
                    break;

                case 'bottom' :
                    position = 2;
                    break;

                default:
                    position = 0;
                    break;
            }

            if (time === true) {
                layer.tips(msg, obj, {style: ['background-color:'+ backgroundColor +'; color:' + color, backgroundColor],guide: position, maxWidth: maxWidth, closeBtn:[0, true]});
            } else {
                layer.tips(msg, obj, {style: ['background-color:'+ backgroundColor +'; color:' + color, backgroundColor],guide: position, time: time, maxWidth: maxWidth});
            }
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
         * 取元素 value 值
         *
         * @params  string  name    名称
         * @params  string  type    元素类型
         * @params  string  extra   额外的选择器
         *
         * @return  bool
         */
        value : function (name, type, extra) {
            var type  = type  || 'input';
            var extra = extra || '';
            return $(type + '[name=' + name + ']' + extra).val();
        },

        /**
         * 全选/反选 checkbox
         *
         * @params    object    obj         全选对象[不提供时，被点击的全选对象 class 中应包含 check_all ]
         * @params    object    selector    被选对象[不提供时，被选对象类型 type为checkbox name 为 check_all[]]
         *
         * @return    void
         */
        check_all : function (obj, selector) {

            if (!obj) obj = $(".check_all");
            if (!selector) selector = $("input[name='check_all[]'][type='checkbox']");

            $(obj).click(function(){

                var selector_length = selector.length;
                for (var i = 0; i < selector_length; i++) {
                    if (selector[i].checked == true) {
                        $(selector[i]).attr("checked", false);
                    } else {
                        $(selector[i]).attr("checked", true);
                    }
                }
            });
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

        /**
         * 还可以输入多少字
         *
         * @params    object    obj_field   form对象
         * @params    int       limit       允许输入的字数
         *
         * @return    void
         */
        text_counter : function (obj_field, limit) {
            /**
             * demo
             * <textarea name="memo" style="width:100%;height:100px;"
             *  onKeyDown="$.BKD.text_counter(this.form.memo, 255);"
             *  onkeyup="$.BKD.text_counter(this.form.memo, 255);" >
             * </textarea>
             * 给需要控制输入的表单上加上 onKeyDown 与 onkeyup 事件及可。
             */
            if (!limit) limit = 0;

            var add_input = "<p>* 您还可以输入 <strong class='text_counter_rem_len'>"+limit+"</strong> 字</p>";
            var obj_field_value = obj_field.value;

            if ($(".text_counter_rem_len").val() == undefined) {
                $(obj_field).parent().append(add_input);
            }

            if (obj_field_value.length > limit) {
                obj_field.value = obj_field_value.substring(0, limit);
            } else {
                $(".text_counter_rem_len").html(limit - obj_field_value.length);
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
                                $.BKD.set_radiobox(names,jsons[names]);
                            } else if (input_type == 'checkbox') {
                                $.BKD.set_checkbox(names,jsons[names]);
                            }
                        }

                        if ($("input[name='"+names+"[]']").attr("type") === 'checkbox') {
                            $.BKD.set_checkbox(names+'[]',jsons[names]);
                        }

                        if ($("select[name='"+names+"']").length >= 1) {
                            $.BKD.set_select(names,jsons[names]);
                        }

                        if ($("textarea[name='"+names+"']").length >= 1) {
                            $.BKD.set_textarea(names,jsons[names]);
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

        /**
         * 判断给定函数是否存在
         *
         * @params    string    func_name    函数名称
         *
         * @return    void
         */
        function_exists : function (func_name){
            try{
                if(typeof(eval(func_name)) == 'function'){
                    return 1;
                }
            }catch(e){
                alert("函数 "+func_name+" 不存在!");
                return false;
            }
        },

        /**
         * 对象深复制
         *
         * @params    object    source  需要深复制的对象
         */
        deepCopy : function(source) { 
            var result={};

            for (var key in source) {
              result[key] = typeof source[key]==='object' ? deepCoyp(source[key]): source[key];
            }

            return result;
        },

        /**
         * Json 版权限验证
         *
         * @params    object    obj    需要进行权限验证的JSON对象
         */
        validate_priv : function(obj){
            if (typeof obj == 'object') {
                if (obj.success != true) {
                    $.BKD.msg(obj.message);
                    return false;
                }
                return true;
            }

            return false;
        },

        /**
         * JS 清除左右两边的空格
         *
         * @params    string    str    字符串
         *
         */
        trim : function(str) {
            return str.replace(/(^\s*)|(\s*$)/g, "");
        },

        /**
         * 调用浏览器桌面通知（火狐不可以同一时刻弹出多个桌面通知，Chrome可以，但一次最多显示3个）
         *
         * @param   string    title             通知标题
         * @param   string    body              通知内容
         * @param   string    icon              图标URL
         * @param   string    tag               通知窗口ID 不同窗口不同ID，相同ID通知会覆盖
         * @param   int       autoCloseTime     自动关闭的时间，单位秒 （浏览器默认20秒完毕，这个时间要小于20S）
         *
         */
        notification : function (title, body, icon, tag, autoCloseTime) {
            var icon  = icon  || $.DOMAIN.BKD + 'image/logo128.png';

            if (!("Notification" in window)) {
                alert("你现在使用的浏览器不支持桌面通知，请换成Chrome、Firefox或者极速模式");
                return;
            }

            var createNotification = function (title, body, icon, tag, autoCloseTime) {
                var title = title || null;
                var body  = body  || null;
                var icon  = icon  || null;
                var tag   = tag   || null;
                var autoCloseTime = autoCloseTime || null;

                var notification = new Notification(title, {
                    body : body,
                    icon : icon,
                    tag  : tag,
                });

                if (autoCloseTime) {
                    notification.onshow = function () {
                        setTimeout(function () {
                            notification.close();
                        }, autoCloseTime * 1000);
                    }
                }
                return notification;
            }

            // Notification.permission granted default denied
            if (Notification.permission ==="granted") {
                var notification = new createNotification(title, body, icon, tag, autoCloseTime);
            } else if (Notification.permission === 'default') {
                Notification.requestPermission(function (permission) {
                    if (permission ==="granted") {
                        var notification = new createNotification(title, body, icon, tag, autoCloseTime);
                    } else {
                        alert('请把浏览器设置为允许通知');
                    }
                });
            } else {
                alert('请把浏览器设置为允许通知');
            }
        },

        /**
         * 省市区三级联动
         *
         * @param   int       province_code       父级城市id
         * @param   object    operation 城市的下拉框对象
         */
        get_city : function(province_code, operation) {
            if (province_code) {
                $.post($.DOMAIN.BKD + 'area/get_city_list', {"province_code" : province_code}, function (response) {
                    if (response.success === false) {
                        $.BKD.msg(response.message);
                    } else {
                        options = "";
                        options += "<option value=\"\">请选择</option>";
                        var code = operation.attr('code');

                        for(key in response.data) {
                            options += "<option "+((code === response.data[key]['area_code']) ? 'selected' : '')+" value="+response.data[key]['area_code']+">"+response.data[key]['area_name']+"</option>";
                        }
                        operation.html(options);
                    }
                } , 'json');
            }else{
                if(operation.attr("name") == 'city_code'){
                    operation.html("<option value=\"\">请选择</option>");
                    $("#district_code").html("<option value=\"\">请选择</option>");
                }else{
                    operation.html("<option value=\"\">请选择</option>");
                }

            }
        },

    };
})(jQuery);

$(document).ready(function () {
    // 绑定日期插件
    $(".Wdate").live('click', function () {
        WdatePicker();
    });

    $.BKD.check_all();
});