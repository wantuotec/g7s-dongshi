<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>API平台接口测试工具</title>
    <link href="http://g.alicdn.com/sj/dpl/1.5.1/css/sui.min.css" rel="stylesheet">
    <script type="text/javascript" src="http://g.alicdn.com/sj/lib/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="http://g.alicdn.com/sj/dpl/1.5.1/js/sui.min.js"></script>
</head>

<body>
    <div class="sui-container" style="margin: 20px auto;">
    <form method="POST" class="sui-form form-horizontal" style="width: 80%;margin: 0 auto;">
        <div class="control-group">
            <label class="control-label v-top" for="">API环境：</label>
            <select name="api_uri" class="span2 input-fat">
                <?php foreach ($api_uris as $key => $val) { ?>
                <option value="<?php echo $key; ?>" <?php if($key == $api_uri) { echo 'selected'; } ?> ><?php echo $val[1]; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="control-group">
            <label class="control-label v-top" for="">API接口名称：</label>
            <input name="method" class="span2 input-xlarge input-fat" type="text" placeholder="API接口名称" value="<?php echo $method; ?>" />
        </div>

        <div class="control-group" id="params_control_div">
            <label class="control-label v-top" for="">请求参数：</label>
            <div class="row-fluid">
                <div class="input-prepend input-inner ">
                    <span class="add-on">字段：</span>
                    <input type="text" class="span2 input-xlarge input-fat" name="params_fields[]" value="<?php echo $params_fields[0] ?>">
                    <span class="add-on ">值：</span>
                    <input type="text" class="span2 input-xlarge input-fat" name="params_values[]" value="<?php echo $params_values[0] ?>">
                </div>
                <button type="button" class="sui-btn btn-primary btn-xlarge" id="add_params_input_div" style="margin: 0px 10px;">+</button>

            </div>
            <?php if (!empty($params_fields)) : ?>
            <?php foreach($params_fields as $key => $value): ?>
            <?php if ($key > 0) : ?>
            <div class="row-fluid">
                <div class="input-prepend input-inner">
                    <span class="add-on">字段：</span>
                    <input type="text" class="span2 input-xlarge input-fat" name="params_fields[]" value="<?php echo $params_fields[$key] ?>">
                    <span class="add-on">值：</span>
                    <input type="text" class="span2 input-xlarge input-fat" name="params_values[]" value="<?php echo $params_values[$key] ?>">
                </div>
                <button type="button" class="sui-btn btn-primary btn-xlarge del_params_input_div" style="margin: 0px 10px;">-</button>
            </div>
            <?php  endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>


        </div>

        <div class="control-group">
            <label class="control-label v-top" for="">请求参数json：</label>
            <div class="row-fluid">
                <textarea name="params" style="height: 190px;" class="input-xxlarge">
                    <?php if ($params_json) : ?>
                        <?php $this_params = json_encode(json_decode($params_json, true)['params']); ?>
                        <?php $this_params = str_replace('\r\n', '', $this_params)?>
                        <?php $this_params = trim(stripslashes($this_params), '"')?>
                        <?php echo $this_params ? $this_params : '';?>
                    <?php endif; ?>
                </textarea>

                请求参数以前面的字段-值为主，进行合并

            </div>
        </div>
        <div class="control-group">
            <label class="control-label v-top" for="">更多参数：</label>
            <button type="button" class="sui-btn btn-primary btn-xlarge op-more-params show-more-params ">展开 <i class="sui-icon icon-tb-unfold"></i></button>
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">app_key：</label>
            <input name="app_key"  class="span2 input-xlarge" type="text" placeholder="app_key" value="<?php echo $app_key; ?>" />
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">app_session：</label>
            <input name="app_session"  class="span2 input-xlarge" type="text" placeholder="app_session" value="<?php echo $app_session; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">app_secret：</label>
            <input name="app_secret"  class="span2 input-xlarge" type="text" placeholder="app_secret" value="<?php echo $app_secret; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">app_version：</label>
            <input name="app_version"  class="span2 input-xlarge" type="text" placeholder="app_version" value="<?php echo $app_version; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">app_type：</label>
            <input name="app_type"  class="span2 input-xlarge" type="text" placeholder="app_type" value="<?php echo $app_type; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">uuid：</label>
            <input name="uuid"  class="span2 input-xlarge" type="text" placeholder="uuid" value="<?php echo $uuid; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">响应格式：</label>
            <select name="format"  class="span2 input-xlarge">
                <option value="json" <?php if('json' == $format) { echo 'selected'; } ?> >json</option>
                <option value="xml"  <?php if('xml'  == $format) { echo 'selected'; } ?> >xml</option>
            </select>
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">接口版本：</label>
            <select name="version"  class="span2 input-xlarge">
                <option value="1.0" <?php if('1.0' == $version) { echo 'selected'; } ?> >1.0</option>
                <option value="2.0" <?php if('2.0' == $version) { echo 'selected'; } ?> >2.0</option>
            </select>
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">字符编码：</label>
            <select name="charset"  class="span2 input-xlarge">
                <option value="UTF-8"  <?php if('UTF-8'  == $charset) { echo 'selected'; } ?> >UTF-8</option>
                <option value="GB2312" <?php if('GB2312' == $charset) { echo 'selected'; } ?> >GB2312</option>
            </select>
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">加密方式：</label>
            <select name="sign_method"  class="span2 input-xlarge">
                <option value="md5"  <?php if('md5'  == $sign_method) { echo 'selected'; } ?> >md5</option>
                <option value="sha1" <?php if('sha1' == $sign_method) { echo 'selected'; } ?> >sha1</option>
            </select>
        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">时间戳：</label>
            <input name="timestamp"  class="span2 input-xlarge" type="text" placeholder="可以留空，提交时会补全" value="<?php echo $timestamp; ?>" />

        </div>

        <div class="control-group more-params">
            <label class="control-label v-top" for="">签名：</label>
            <input name="sign"  class="span2 input-xlarge" type="text" placeholder="可以留空，提交时会补全" value="<?php echo $sign; ?>" />

        </div>

        <div class="control-group">
            <label class="control-label v-top" for=""></label>
            <button type="submit" class="sui-btn btn-primary btn-xlarge">提交请求</button>
            <input type="submit" name="preview_md" class="sui-btn btn-primary btn-xlarge" style="margin-left: 10px;" value="生成简版文档md文件">
        </div>

        <div class="control-group">
            <label class="control-label v-top" for="">响应内容：</label>
            <textarea class="input-xxlarge" style="height: 600px;"><?php echo $response; ?></textarea>
        </div>
    </form>
</div>
</body>

<script>
    $(function () {
        $('#add_params_input_div').on('click', function () {
            var params_input_div = '';
            params_input_div += '<div class="row-fluid">';
            params_input_div += '<div class="input-prepend input-inner">';
            params_input_div += '<span class="add-on">字段：</span>';
            params_input_div += '<input type="text" class="span2 input-fat input-xlarge" name="params_fields[]">';
            params_input_div += '<span class="add-on">值：</span>';
            params_input_div += '<input type="text" class="span2 input-fat input-xlarge" name="params_values[]">';
            params_input_div += '</div>';
            params_input_div += '<button type="button" class="sui-btn btn-primary btn-xlarge del_params_input_div" style="margin: 0px 10px;">-</button>';
            params_input_div += '</div>';
            $('#params_control_div').append(params_input_div);
        });

        $('#params_control_div').on('click', '.del_params_input_div', function () {
            $(this).parent().remove();
        });

        var storage = window.localStorage;
        //storage.devapi_open_status  (1=展开 2=收起)
        if (storage.devapi_open_status == 1) {
            show_more_params($('.op-more-params'));
        } else {
            hide_more_params($('.op-more-params'));
        }

        $('.op-more-params').click(function () {
            var ths = $(this);
            if (ths.hasClass('show-more-params')) {
                show_more_params(ths);
                storage.devapi_open_status = 1;
            } else if(ths.hasClass('hide-more-params')) {
                hide_more_params(ths);
                storage.devapi_open_status = 2;
            }
        });

        function show_more_params(obj) {
            $('.more-params').show();
            obj.html('收起 <i class="sui-icon icon-tb-fold"></i>');
            obj.removeClass('show-more-params');
            obj.addClass('hide-more-params');
        }

        function hide_more_params(obj) {
            $('.more-params').hide();
            obj.html('展开 <i class="sui-icon icon-tb-unfold"></i>');
            obj.removeClass('hide-more-params');
            obj.addClass('show-more-params');
        }
    });
</script>
</html>