<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $baidu_ak; ?>"></script>
<style type="text/css">
.input-group {
  margin: 30px 0 36px;
  position: relative;
}
.input-group .title {
  width: 127px;
  margin-right: 12px;
  display: inline-block;
  text-align: right;
  font-size: 15px;
  color: #1A1A1A;
  letter-spacing: 5px;
}
.input-group .input {
  border: 1px solid #DDD;
  /* CSS 调工样式时使用 */
  height: 36px;
  line-height: 36px;
  width: 655px;
  padding-left: 12px;
  font-size: 15px;
}
.input-group .input.input-wrong {
  border: 1px solid #E8380C;
}
.input-group .address-tips {
  position: absolute;
  top: 40px;
  left: 145px;
  font-size: 14px;
  color: red;
}
.input-group .address-suggest {
  z-index: 10;
  position: absolute;
  left: 144px;
  width: 642px;
  max-height: 424px;
  border: 1px solid #1A1A1A;
  padding: 0 12px;
  overflow-y: auto;
  background-color: #FFF;
}
.input-group .address-suggest .address-suggest-title {
  font-size: 15px;
  line-height: 34px;
  color: #00A3A5;
  letter-spacing: 5px;
}
.input-group .address-suggest .address-suggest-line {
  padding: 10px 0 10px;
  font-size: 15px;
  color: #666;
  border-top: 1px solid #ddd;
  cursor: pointer;
}
.input-group .address-suggest .address-suggest-line .address-main {
  color: #131315;
}
.input-group .address-suggest .address-suggest-line .address-main .address-extra {
  color: #666;
}
.input-group .address-suggest .address-suggest-line .address-detail {
  margin-top: 5px;
}
.input-group .address-suggest .address-suggest-line .address-detail .address-detail-left {
  float: left;
}
.input-group .address-suggest .address-suggest-line .address-detail .address-detail-right {
  float: right;
  margin-right: 50px;
}
.input-group .address-suggest .address-suggest-line-no-result {
  font-size: 14px;
  text-align: center;
  padding: 10px 0 10px;
  font-size: 15px;
  color: #333;
}


.address-left-content {
    width: 50%;
    float: left;
}
.address-right-content {
    width: 50%;
    height: 800px;
    float: left;
}
#map {
    box-sizing: border-box;
    border: 1px solid #dedede;
}
#map .anchorBL {
    display: none;
}

</style>
<div id="main-content clearfix"> <!-- Main Content Section with everything -->
    <div class="address-left-content border">
        <div class="input-group J-bindAddressSuggest">
            <label class="title">取件地标：</label>
            <input type="text" class="input" value="<?php echo $address1; ?>" bind="J-addressSuggest" name="sender_addr"/>
            <div class="address-tips hide">请在地图中选择符合的坐标</div>
            <input class="J-addr-latitude" type="hidden" name="sender_addr_latitude"  value="<?php echo isset($order['sender_addr_latitude']) ? $order['sender_addr_latitude'] : '';?>"/>
            <input class="J-addr-longitude" type="hidden" name="sender_addr_longitude" value="<?php echo isset($order['sender_addr_longitude']) ? $order['sender_addr_longitude']: '';?>"/>
        </div>
    </div>

    <div class="address-right-content border" id="map">
    </div>
</div><!-- End Main Content -->
<script type="text/javascript">
<!--
    // 百度地图API功能
    var map = new BMap.Map('map', {enableMapClick:false}); // 地图不可点

    // todo 上海市需要要的需要修改成当前所在城市
    map.centerAndZoom('上海市', 12);
    map.enableScrollWheelZoom();

    var updateGps = function (point, label) {
        if ('设置成功' != label.content) {
            console.log(point.lng)
            console.log(point.lat)
            // 此处用 ajax 请求更新GPS坐标
            $('input[bind=J-addressSuggest').removeClass('input-wrong');
            $('.address-tips').hide();
            label.setContent('设置成功');
        }
    }

    var selectPoint =  function(point) {
        // 清楚所有覆盖物
        map.clearOverlays();

        var marker = new BMap.Marker(point);  // 创建标注
        overlays.push(marker);
        map.addOverlay(marker);               // 将标注添加到地图中
        marker.setAnimation(BMAP_ANIMATION_BOUNCE);
        map.setZoom(15); // 图片级别调大
        map.panTo(point);

        var labelStyle = {
            color: "red",
            fontSize: "12px",
            height: "20px",
            lineHeight: "20px",
            fontFamily: "微软雅黑",
            border: "1px solid red"
        };

        var labelOptions = {
          position : point,              // 指定文本标注所在的地理位置
          offset   : new BMap.Size(0, 0) // 设置文本偏移量
        }
        var label = new BMap.Label('点击此处选择此坐标', labelOptions); // 创建文本标注对象
        label.setStyle(labelStyle);
        map.addOverlay(label);
        label.addEventListener('click', function () {
            updateGps(point, label);
        });
    }

    // 右键菜单
    var menu = new BMap.ContextMenu();
    var txtMenuItem = [
        {
            text:'选择此处',
            callback: selectPoint
        }
    ];
    for(var i=0; i < txtMenuItem.length; i++){
        menu.addItem(new BMap.MenuItem(txtMenuItem[i].text,txtMenuItem[i].callback,100));
    }
    map.addContextMenu(menu);

    var overlays = [];

    /**
     * 获取元素的参数
     *
     * @params  string  selector    选择器
     * @params  string  params_name 元素
     *
     * @return  json object
     */
    if (!C) {
        var C = {
            getParams: function (selector, params_name) {
                params_name = ('undefined' != typeof(params_name) && '' != params_name && null != params_name) ? params_name : 'params';
                var selector = (selector instanceof jQuery ? selector : $(selector)).attr(params_name);
                if ('undefined' == typeof selector || '' == selector || !selector) {
                    return null;
                } else {
                    try {
                        return $.parseJSON(selector);
                    } catch(e) {
                        //C.log('不是一个有效的json字符串：' + selector)
                    }
                }
            }
        }
    }

    /**
     * 常用地址和建议地址
     * @params  e   object  数据对象
     * @return  boolean
     */
    var bindAddressSuggest = function () {


        // 点击输入框弹出建议地址
        $('.J-bindAddressSuggest').delegate('[bind="J-addressSuggest"]', 'click', function () {
            var params = C.getParams($(this));
            // 地址栏中没有地址执行获取自己的常用地址
            if ('' == $(this).val()) {
            } else {
                // 下面有数据就展示
                var suggestion = $(this).parent().find('.address-suggest');
                if (suggestion.size() > 0) {
                    suggestion.removeClass('hide');
                } else {
                }
            }
        });

        $('.J-bindAddressSuggest').delegate('[bind="J-addressSuggest"]', 'blur', function(e) {
            var obj = $(this);
            setTimeout(function () {
                var exist = obj.parent().find('.J-addr-latitude').val();
                if (!(exist && typeof exist != 'undefined' && '' != exist)) {
                    $(obj).addClass('input-wrong');
                    $(obj).parent().find('.address-tips').show();
                }
            }, 200);
        });

        // 输入地址百度地图搜索的
        $('.J-bindAddressSuggest').on('keyup', '[bind="J-addressSuggest"]', function (e) {
            // 屏蔽上下左右 home end ctrl 按键
            if ((e.keyCode >= 35 && e.keyCode <= 40) || 17 == e.keyCode) {
                return false;
            }

            var params = C.getParams($(this));
            var obj    = $(this);
            var query  = $.trim($(this).val());

            // var type  = $(this).attr('data');
            // $(this).val(query);
            var last = $(this).data('last');

            if (query.length == 0 && query != last) {
                $(this).data('last', query);
                // tododelete F.getAddressList(params.type, $(this));
            }

            // 如果内容变化，把已选择的地址的信息清除
            if (query.length > 0 && query != last) {
                $(this).parent().find('.J-addr-latitude').val('');
                $(this).parent().find('.J-addr-longitude').val('');
            }

            // 和上次不一样时才处理
            var ajax = null;
            if (query.length > 0 && query != last) {
                $(this).data('last', query);
                var geoInfo = function(lng, lat, callback) {
                    var myGeo = new BMap.Geocoder();
                    myGeo.getLocation(new BMap.Point(lng, lat), function(res){
                        callback && callback(res['addressComponents']);
                    });
                };

                var getPosition = function (position) {
                    var title       = position.title;
                    var address     = position.address || "";
                    var lat         = position.point.lat;
                    var lng         = position.point.lng;
                    var province    = position.province;
                    var city        = position.city;

                    // 经度纬度不存在
                    if (!position.point && !position.point.lat && !position.point.lng) {
                        return;
                    }

                    // 没有意义的地址去除
                    var tags = position.tags || [];
                    for(var i=0;i<tags.length;i++){
                        if(tags[i] == '行政地标' || tags[i] == '道路' || tags[i] == '乡路' || tags[i] == '名称标注类'){
                            return;
                        }
                    }

                    // 组合地址信息
                    content  = '<div class="address-suggest-line">';
                    content +=     '<div class="address-main name">'  + title + '</div>';
                    content +=     '<div class="address-detail clearfix">';
                    content +=         '<div class="address-detail-left address">正在加载中……</div>';
                    content +=     '</div>';
                    content +=     '<input type="hidden" name="latitude" value="'  + lat + '"/>';
                    content +=     '<input type="hidden" name="longitude" value="' + lng + '"/>';
                    content += '</div>';

                    var target = $(content);
                    var targetTitle = target.find('.address-main.name');
                    var targetAddress = target.find('.address-detail-left.address');

                    // BMAP_POI_TYPE_NORMAL     一般位置点
                    // BMAP_POI_TYPE_BUSSTOP    公交车站位置点
                    // BMAP_POI_TYPE_SUBSTOP    地铁车站位置点
                    switch(position.type) {
                        case BMAP_POI_TYPE_NORMAL : // 一般位置点
                            if (!address) {
                                geoInfo(lng, lat, function(res) {
                                    targetAddress.text(res['city'] + res['district'] + res['street'] + res['streetNumber']);
                                });
                            }else if (new RegExp("^(.*市)(.*[区县])$").test(address)){
                                geoInfo(lng, lat, function(res) {
                                    targetAddress.text(res['city'] + res['district'] + res['street'] + res['streetNumber']);
                                });
                            }else if (new RegExp("^(.*市)(.*[区县]).+").test(address)) {
                                targetAddress.text(address);
                            } else if (new RegExp(".*(?!市).*[区县]").test(address)) {
                                geoInfo(lng, lat, function(res) {
                                    targetAddress.text(res['city'] + address);
                                });
                            } else if (!(new RegExp("^(.*市).*[区县]").test(address))) {
                                geoInfo(lng, lat, function(res){
                                    targetAddress.text(res['city'] + res['district'] + address);
                                });
                            } else {
                                targetAddress.text(address);
                            }
                            break;
                        case BMAP_POI_TYPE_BUSSTOP : // 公交车站位置点
                            targetTitle.text(title + '-公交站');
                            geoInfo(lng, lat, function(res){
                                targetAddress.text(res['city'] + res['district'] + res['street'] + res['streetNumber']);
                            });
                            break;
                        case BMAP_POI_TYPE_SUBSTOP : // 地铁车站位置点
                            targetTitle.text(title + '-地铁站');
                            geoInfo(lng, lat, function(res){
                                targetAddress.text(res['city'] + res['district'] + address + title + '地铁站');
                            });
                            break;
                    }

                    return target;
                };

                // 百度地图处理
                // http://developer.baidu.com/map/reference/index.php?title=Class:%E6%9C%8D%E5%8A%A1%E7%B1%BB/LocalSearch
                var cityName = $('.city-name-show').html() || '上海市';
                var options = {
                    pageCapacity: 100, // 每页结果数
                    onSearchComplete: function(results){
                        // 判断状态是否正确
                        if (local.getStatus() == BMAP_STATUS_SUCCESS){

                            var notExist = (obj.parent().find('.address-suggest').size() < 1);

                            if (notExist) {
                                var container = $('<div class="address-suggest J-auto-hide-target"></div>');
                            } else {
                                var container = obj.parent().find('.address-suggest').removeClass('hide').html('');
                            }

                            for (var i = 0; i < results.getCurrentNumPois(); i ++){
                                position = getPosition(results.getPoi(i));
                                //if (position) {
                                    container.append(position);
                                //}
                            }

                            if (notExist) {
                                obj.after(container);
                            }
                        } else {
                            var notExist = (obj.parent().find('.address-suggest').size() < 1);

                            if (notExist) {
                                var container = $('<div class="address-suggest J-auto-hide-target"><div class="address-suggest-line-no-result">没有找到合适的地址，建议先输入办公楼、小区等关键字。</div></div>');
                            } else {
                                var container = obj.parent().find('.address-suggest').removeClass('hide').html('<div class="address-suggest-line-no-result">没有找到合适的地址，建议先输入办公楼、小区等关键字。</div>');
                            }

                            // 如果是其它情况，没有结果等
                            obj.after(container);
                        }
                    }
                };
                var local = new BMap.LocalSearch(cityName, options);
                local.search(query, {forceLocal: true}); // 限制在当前城市
            }
        });

        // 选中地址的事件
        $('.J-bindAddressSuggest').parent().delegate('.address-suggest .address-suggest-line', 'click', function () {
            // 添加业务代码
            var address_data = {};
            var phone        = $(this).find('.phone').html();
            var obj          = $(this).parent().parent().parent();
            var data_type    = obj.find('.data_address').attr('datatype'); // 地址类型

            // 百度搜索过来的数据(百度过来的数据是没有手机号码的)
            if ('undefined' == typeof phone) {
                address_data.address = $(this).find('.address').html() + $(this).find('.name').html();
                address_data.name    = '';

            // 自己的常用地址
            } else {
                address_data.address = $(this).find('.address').html() || '';
                address_data.name    = $(this).find('.name').html() || '';
            }

            address_data.phone       = $(this).find('.phone').html() || '';
            address_data.addr_number = $(this).find('.addr_number').html() || '';
            address_data.latitude    = $(this).find('input[name=latitude]').val();
            address_data.longitude   = $(this).find('input[name=longitude]').val();

            var point  = new BMap.Point(address_data.longitude, address_data.latitude);
            selectPoint(point);

            // 如果正确的选择了地址，把错误信息及样式移除
            $(this).parent().parent().find('.address-tips').hide();
            $(this).parent().parent().find('[bind="J-addressSuggest"]').removeClass('input-wrong');

            $(this).parent().addClass('hide');
        });
    };

    bindAddressSuggest();
-->
</script>