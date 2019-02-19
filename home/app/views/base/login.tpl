<div id="login-wrapper">
    <div id="login-content">
        <div id="login-top" class="pngfix">
            <img class="pngfix" src="<?php echo HOME_DOMAIN ?>images/admin/logo_title_bg.png" alt="<?php echo TITLE; ?>" />
        </div>
        <form onsubmit="return false;">
            <?php if (1 == $mode) { ?>
            <p>
                <label>用户名</label>
                <input name="username" class="text-input" type="text" value="<?php echo isset($username) ? $username : ''; ?>" onkeyup="next(this, event, 'password')"/>
            </p>
            <div class="clear">
            </div>
            <p>
                <label>密&nbsp;&nbsp;&nbsp;&nbsp;码</label>
                <input name="password" class="text-input" type="password" onkeyup="next(this, event, 'captcha')"/>
            </p>
            <div class="clear">
            </div>
            <p class="captcha">
                <label>验证码</label>
                <input name="captcha" class="text-input" type="text" onkeyup="to_submit(event)" maxlength="4" />&nbsp;
                <a href="javascript:void(0)"><img style="vertical-align:middle;" src="<?php echo HOME_DOMAIN ?>auth/captcha/?useage=backend&<?php echo microtime(true) ?>" title="点击换一张验证码"/></a>
            </p>

            <div class="clear">
            </div>
            <!-- <div class="notification information png_bg  pngfix">
                <div>
                    点击验证码图片可以换验证码
                </div>
            </div> -->
            <p>
                <input class="submit" value="登 录" type="button" onclick="do_submit()" />
            </p>
            <?php } else if (2 == $mode) { ?>
            <p>
                <label>手机号</label>
                <input name="phone_number" class="text-input" type="text" value="<?php echo $phone_number ?>" onkeyup="next(this, event, 'captcha')"/>
            </p>
            <div class="clear"></div>
            <p>
                <label>验证码</label>
                <input type="hidden" name="captcha_key" />
                <input class="submit fl get-captcha" style="width:110px;float:right;padding: 0 10px;height: 30px;" value="获取验证码" type="button" onclick="get_sms_captcha()" />
                <input name="captcha" class="text-input" style="width:70px;float:right;margin-right: 20px;" type="text" onkeyup="next(this, event, 'captcha')"/>
            </p>
            <div class="clear"></div>
            <p>
                <input class="submit login-btn" value="登 录" type="button" onclick="do_submit_sms()" />
            </p>
            <?php } ?>
            <div class="clear">
            </div>
            <span id="tip" class="c-red"></span>
        </form>
    </div>
    <!-- End #login-content -->
    <div style="text-align:center;margin-top:20px;font-size:10px;">
        
    </div>
</div>
<!-- End #login-wrapper -->
<script type="text/javascript">

$(document).ready(function(){
    // 更换验证码
    $('.captcha img').click(function () {
        $(this).attr('src', '<?php echo HOME_DOMAIN ?>auth/captcha/?useage=backend&' + Math.random());
        return false;
    }); 
})

if ($('input[name=username]').val()) {
    $('input[name=password]').focus();
} else {
    // 第一个文本框默认有焦点
    $('input[name=username]').focus();
}

function next(obj, event, name) {
    if ($.BKD.is_tab_enter(event)) {
        <?php if (1 == $mode) { ?>
        if ($('input[name=username]').val() && $('input[name=password]').val() && $('input[name=captcha]').val()) {
            do_submit();
        } else if (!$(obj).val()) {
            $(obj).focus();
        } else {
            $('input[name=' + name + ']').focus();
        }
        <?php } else if (2 == $mode) { ?>
        if ($('input[name=phone_number]').val() && $('input[name=captcha]').val() && $('input[name=captcha_key]').val()) {
            do_submit();
        } else if (!$(obj).val()) {
            $(obj).focus();
        } else {
            $('input[name=' + name + ']').focus();
        }
        <?php } ?>
    }
}

function to_submit(event) {
    if ($.BKD.is_tab_enter(event)) {
        do_submit();
    }
}

function do_submit() {
    if (!$('input[name=username]').val()) {
        $('input[name=username]').focus();
        return false;
    }

    if (!$('input[name=password]').val()) {
        $('input[name=password]').focus();
        return false;
    }

    if (!$('input[name=captcha]').val()) {
        $('input[name=captcha]').focus();
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>auth/login', $('form').serialize(), function (response) {
        if (true === response.success) {
            $.BKD.redirect(response.data);
        } else {
            if (response.data == 'captcha') {
                $('.captcha img').click();
            }

            if (response.data) {
                $('input[name=' + response.data + ']').val('');
                $('input[name=' + response.data + ']').focus();
            }

            $('#tip').html(response.message);
        }
    } ,'JSON');

    return false;
}

var counter = 60;
function timeCounter(obj)
{
    if (counter == 0) {
        $(obj).attr('disabled', false);
        $(obj).val('获取验证码');
        return;
    } else {
        $(obj).attr('disabled', true);
        $(obj).val('重新发送(' + counter + ')');
        counter--;

        setTimeout(function() {
            timeCounter(obj);
        }, 1000);
    }
}

function get_sms_captcha()
{
    var phone_number = $('input[name=phone_number]').val()
    if (!phone_number) {
        $('input[name=phone_number]').focus();
        $('#tip').html('请输入手机号');
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>auth/get_sms_captcha', 'phone_number=' + phone_number, function (response) {
        if (true === response.success) {
            $('input[name=captcha_key]').val(response.data.captcha_key);
            timeCounter($('.get-captcha'));
        } else {
            if (response.data) {
                $('input[name=phone_number]').focus();
            }

            $('#tip').html(response.message);
        }
    } ,'JSON');
}

function do_submit_sms() {
    if (!$('input[name=phone_number]').val()) {
        $('input[name=phone_number]').focus();
        $('#tip').html('请输入手机号');
        return false;
    }

    if (!$('input[name=captcha]').val()) {
        $('input[name=captcha]').focus();
        $('#tip').html('请输入验证码');
        return false;
    }

    if (!$('input[name=captcha_key]').val()) {
        $('input[name=captcha_key]').focus();
        $('#tip').html('请获取验证码');
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>auth/sms_login', $('form').serialize(), function (response) {
        if (true === response.success) {
            $.BKD.redirect(response.data);
        } else {
            if (response.data) {
                $('input[name=' + response.data + ']').val('');
                $('input[name=' + response.data + ']').focus();
            }

            $('#tip').html(response.message);
        }
    } ,'JSON');

    return false;
}
</script>