<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, user-scalable=0, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/>
        <meta content="yes" name="apple-mobile-web-app-capable"/>
        <meta content="yes" name="apple-touch-fullscreen"/>
        <meta content="telephone=no" name="format-detection"/>
        <meta content="black" name="apple-mobile-web-app-status-bar-style">
        <link rel="dns-prefetch" href="//m.<?php echo DOMAIN ?>">
        <link rel="dns-prefetch" href="//cdn.<?php echo DOMAIN ?>">
        <!--<link rel="shortcut icon" href="<?php echo img_url('css/v1.0/image/logo_48.ico'); ?>" />-->
        <!--<link rel="apple-touch-icon-precomposed" href="<?php echo img_url('css/v1.0/image/logo_touch.png?v=1'); ?>">-->
        <title><?php if($title){ echo $title; } else { echo TITLE;} ?></title>
        <meta name="keywords" content="<?php if($keywords){echo $keywords;} else { echo KEYWORDS;} ?>" />
        <meta name="description" content="<?php if($description){echo $description, '-', TITLE;} else { echo DESCRIPTION;} ?>" />
        <meta name="author" content="<?php echo $_SERVER['HTTP_HOST']; ?>" />
        <meta name="copyright"  content="Copyright &copy;<?php echo $_SERVER['HTTP_HOST']; ?> 版权所有" />
        <?php echo css_tag(array('m/v1/m.css'), $css); ?>

        <?php if (empty($error) && !empty($jsapi)) { ?>
        <script type="text/javascript">
            //调用微信JS api 支付
            function jsApiCall()
            {
                WeixinJSBridge.invoke(
                    'getBrandWCPayRequest',<?php echo $jsapi; ?>,
                    function(res){
                        // WeixinJSBridge.log(res.err_msg + '<==>' + '11');
                        // alert(res.err_code+'<===>' +res.err_desc + '<==>' + res.err_msg);
                        // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                        if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                            window.location.href = "<?php echo $back_url; ?>";
                        } else {
                            // res.err_msg == "get_brand_wcpay_request:cancel"
                            // res.err_msg == "get_brand_wcpay_request:fail"
                            window.location.href = "<?php echo $error_url; ?>";
                        }
                    }
                );
            }

            function callpay()
            {
                if (typeof WeixinJSBridge == "undefined"){
                    if( document.addEventListener ){
                        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                    }else if (document.attachEvent){
                        document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                    }
                }else{
                    jsApiCall();
                }
            }
        </script>
        <?php } ?>
    </head>

    <body>
        <div class="wrapper"><!-- wrapper 开始 -->
            <div class="header"><!-- header 开始 -->
                <div class="header-left" callback="J-click-redirect" params='{"url":"<?php echo $back_url; ?>"}' >
                    <div>
                        <span class="header-back"></span>
                    </div>
                </div>
                <div class="header-center">
                    微信安全支付
                </div>
                <div class="header-right">
                    <div>
                    </div>
                </div>
            </div><!-- header 结束 -->

            <div class="content"><!-- content 开始 -->
                <div class="wechat-pay-content">
                <?php if (!empty($error)) { ?>
                    <div class="wechat-icon-error"></div>
                    <div class="wechat-msg-error">
                        <?php echo $error; ?>
                    </div>
                    <input type="button" class="button-blue-560-80" value="返回" callback="J-click-redirect" params='{"url":"<?php echo $error_url; ?>"}' />
                <?php } else { ?>
                    <div class="wechat-icon-pay"></div>
                    <div class="wechat-msg-amount">
                        <?php echo $amount; ?>
                    </div>
                    <input type="button" class="button-blue-560-80" value="确认支付" onclick="callpay();"  />
                <?php } ?>
                </div>
            </div><!-- content 结束 -->

            <div class="footer"><!-- footer 开始 -->

            </div><!-- footer 结束 -->
        </div><!-- wrapper 结束 -->

        <script src="http://lib.sinaapp.com/js/jquery/2.0.3/jquery-2.0.3.min.js" type="text/javascript"></script>

        <script type="text/javascript">
            if (typeof jQuery == 'undefined') {
                document.write(unescape("%3Cscript src='<?php echo CDN_DOMAIN; ?>js/jquery/jquery-2.0.3.min.js' type='text/javascript'%3E%3C/script%3E"));
            }
        </script>
        <?php echo script_tag(array('m.min.js'), $js); ?>



        <?php if (ENVIRONMENT == 'production') { ?>
        <!-- 添加统计代码 -->
        <?php } ?>
    </body>
</html>