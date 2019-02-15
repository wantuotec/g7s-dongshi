<!--此页面样式，会影响其它页面-->
<?php echo css_tag(array('normalize.css', 'jquery.slideunlock.css'),$css) ?>

<h1 class="top_nav">
    <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
    <span class="title_wd"><?php echo $slogan;?></span>
</h1>

<div class="guestbook-main">
    <div class="cont-left">

        <div class="message-input">
            <p class="message-title">支言片语</p>
            <form action="">
                <div class="cont-box">
                    <textarea class="main-text message-conts" id="main-text" placeholder="有什么想对小编说的呢 . . ."></textarea>
                </div>
                <div class="tools-box">
                    <div class="operator-box-btn">
                        <span class="main-face-icon">:-)</span>
                        <div id="slide-wrapper">
                            <input type="hidden" value="" id="lockable"/>
                            <div id="slider">
                                <span id="label">>></span>
                                <span id="lableTip">留言请拖动滑块到最右侧</span>
                            </div>
                        </div>
                    </div>
                    <div class="submit-btn"><input type="button" id="to-message-btn" value="留 言" /></div>
                </div>
            </form>
            <div class="clear"></div>
        </div>

        <p class="message-title">留言记录</p>
        <?php if (!empty($list)){
            $i = 0;
            foreach ($list as $val){
                $i++;
        ?>
            <div class="reply-one-item">
                <div class="reply-cont">
                    <input type="hidden" name="article_id" value="<?php echo $val['article_id'];?>">
                    <figure><img src="<?php echo $val['header_img'];?>" alt=""></figure>
                    <p class="visitor">匿名网友<span>[IP:<?php echo $val['address'];?>]</span></p>
                    <p class="message"><?php echo $val['message'];?></p>
                    <div class="clear_both"></div>
                    <div class="reply-box origin-reply-box">
                        <div class="reply_date_view">
                            <span class="date"><?php echo date('Y-m-d', strtotime($val['create_time']));?></span>
                        </div>
                    </div>
                    <?php if (!empty($val['admin_return'])):
                        $j = 0;
                        foreach ($val['admin_return'] as $admin):
                            $j++;
                    ?>
                        <!--以下的code属性都是为了定位使用-->
                        <div class="reply-box admin-reply-box">
                            <span class="reply-user">
                                <?php if (1 == $admin['type']){echo "网友回复：";} else {echo "小编回复：";}  ?>
                            </span>
                            <p class="reply-message"><?php echo $admin['message'];?></p>
                            <div class="reply_date_view">
                                <span class="date"><?php echo date('Y-m-d', strtotime($admin['create_time']));?></span>
                                <!--控制是否显示回复框-->
                                <?php if(2 == $admin['type']): ?>
                                    <span class="to-reply" code="<?php echo $i.'-'.$j;?>">&nbsp;</span>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="reply-admin-wrap reply-admin-wrap-<?php echo $i.'-'.$j;?> hide">
                            <div class="reply-face-icon" code="<?php echo $i.'-'.$j;?>">:-)</div>
                            <div class="input-reply">
                                <input type="text" id="reply-text-<?php echo $i.'-'.$j;?>" placeholder="" value="" maxlength="100" />
                            </div>
                            <div class="submit-reply"><input type="button" value="回复" onclick="add_reply(this)" id="<?php echo $admin['guestbook_id'];?>" code="<?php echo $i.'-'.$j;?>" /></div>
                        </div>
                    <?php endforeach;endif;?>
                </div>
            </div>
        <?php }} else { ?>
            <div class="empty-message">
                --------------- 小编很悲伤，暂无留言 ---------------
            </div>
        <?php } ?>

    </div>
    <div class="cont-right">小编思考中...</div>
</div>
<div class="clear_both"></div>

<!-- 底部分页 -->
<?php echo $pagination; ?>

<!--留言板操作-->
<script type="text/javascript">
    // 全局化锁对象
    var slider = null;

    // 显示留言板表情库
    $('.main-face-icon').SinaEmotion($('#main-text'));

    // 显示回复栏表情库(因为表情插件机制，必须分别加载每个表情按钮对应的文本区域)
    $('.reply-face-icon').each(function(){
        var code = $(this).attr('code');
        $(this).SinaEmotion($('#reply-text-'+code));
    });

    $(function () {
        //------留言按钮变灰---------
        $("#to-message-btn").css("opacity", "0.3");

        //------点击留言框，改变样式--------
        $('.message-conts').focus(function(){
            $('.cont-box').css('border', '2px solid #FFA07A');
        }).blur(function(){
            $('.cont-box').css('border', '1px solid #FFA07A');
        });

        // -----滑动验证，解锁发表按钮效果----------
        slider = new SliderUnlock("#slider", {"labelTip":"留言请拖动滑块到最右侧","successLabelTip":"已解锁,可以发表留言"}, function(){
            //滑块拖拽到了最右边，验证通过
            layer.tips('已解锁', '#to-message-btn', {tips: [2, '#99cc66'],time: 1000});
            //恢复留言按钮效果,添加点击提交事件
            $("#to-message-btn").css("opacity", "1");
            $("#to-message-btn").bind("click", function(){
                //提交留言
                add_message()
                //留言成功，重置滑块,去除点击提交事件
                reset_lock();
            });
        }, function(){});

        //滑动插件初始化
        slider.init();

        // -------初始化回复管理员区域----------
        $('.to-reply').click(function(){
            var code = $(this).attr('code');
            // 先全部隐藏所有回复区域
            $('.reply-admin-wrap').not('.reply-admin-wrap-'+code).slideUp()
            // 显示/隐藏对应下面的回复区域
            $('.reply-admin-wrap-'+code).slideToggle()
        });
    })

    // 重置留言锁
    function reset_lock () {
        slider.reset();
        $("#to-message-btn").css("opacity", "0.3");
        $("#to-message-btn").unbind("click");
    }

    // 提交留言
    function add_message () {
        var inputText = $('.message-conts').val();
        var message   = AnalyticEmotion(inputText);

        if ('' == message) {
            layer.tips('×(^o^)×顽皮...小编读不懂空气的噢！', '#to-message-btn', {tips: [2, '#99cc66'],time: 3000});
            $('.message-conts').focus();
            reset_lock();
            return false;
        }

        $.post('<?php echo HOME_DOMAIN; ?>guestbook/add_message', 'message='+message, function (response) {
            if (true === response.success) {
                layer.msg('└(^o^)┘留言成功啦！小编会尽快回复的',{time:2000},
                    function(){$.HOME.refresh();}
                );
            } else {
                layer.msg(response.message, {time: 3000});
            }
        }, 'JSON');
    }

    // 提交留言回复
    function add_reply (obj) {
        var id   = $(obj).attr('id');
        var code = $(obj).attr('code');
        var inputText = $('#reply-text-'+code).val();
        var message   = AnalyticEmotion(inputText);

        if ('' == message) {
            layer.tips('×(^o^)×内容去哪儿了...', $(obj), {tips: [2, '#99cc66'],time: 3000});
            $('#reply-text-'+code).focus();
            return false;
        }

        $.post('<?php echo HOME_DOMAIN; ?>guestbook/add_reply', 'id='+id+'&message='+message, function (response) {
            if (true === response.success) {
                layer.msg('└(^o^)┘回复成功啦！小编会仔细研读的',{time:2000},
                    function(){$.HOME.refresh();}
                );
            } else {
                layer.msg(response.message, {time: 3000});
            }
        }, 'JSON');
    }
</script>