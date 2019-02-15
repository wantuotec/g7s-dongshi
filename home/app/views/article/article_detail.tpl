<style>
    /*改变畅言发送按钮样式*/
    #SOHUCS #SOHU_MAIN .module-cmt-box .post-wrap-w .wrap-action-w .action-issue-w .issue-btn-w a button.btn-fw{
        width:50px;
        height:30px;
        background-image: url("<?php echo HOME_DOMAIN;?>public/images/send_.png");
        background-position: 5px 0px;
        background-size: 40px 30px;
        opacity:0.5;
    }
    #SOHUCS #SOHU_MAIN .module-cmt-box .post-wrap-w .wrap-action-w .action-issue-w .issue-btn-w a button.btn-fw:hover{
        background-image: url("<?php echo HOME_DOMAIN;?>public/images/send_.png");
        opacity:1;
    }
    /*隐藏畅言底部版权信息*/
    #SOHUCS #SOHU_MAIN .module-cmt-footer .section-service-w div.service-wrap-w a {
        display:none!important;
    }
</style>
<h1 class="top_nav">
    <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
    <span class="title_wd"><?php echo $slogan;?></span>
</h1>

<div class="article-detail-box">
    <div class="detail-left">
        <div class="detail-cont">
            <input type="hidden" name="article_id" value="<?php echo $list['article_id'];?>">
            <h2><?php echo $list['article_title'];?></h2>
            <div class="detail_date_view">
                <span class="date"><?php echo $list['date'];?></span>
                <span class="origin"><?php echo $list['origin_type_title'];?></span>
                <span class="browse"><?php echo $list['read_num'];?></span>
                <span class="like" id="like_num"><?php echo $list['like_num'];?></span>
            </div>
            <div class="detail-word">
                <?php echo $list['content'];?>
            </div>
        </div>

        <?php if (1 == $module_open[1]['is_open']) { ?>
            <!--畅言评论系统-高速版-START-->
            <div id="SOHUCS" sid="<?php echo $list['article_id'];?>"></div>
            <script charset="utf-8" type="text/javascript" src="http://changyan.sohu.com/upload/changyan.js" ></script>
            <script type="text/javascript">
            window.changyan.api.config({
            appid: 'cyt1TCjIO',
            conf: 'prod_06332fec7808df9d0c852e93cea3843d'
            });
            </script>
            <!--畅言评论系统-高速版-END-->
        <?php } ?>
    </div>

    <div class="detail-right">
        <div class="user-remark">
            <ul class="market-like-box">
                <li class="remark-title"> 喜欢这篇文章吗？ </li>
                <li <?php if(2 == $is_market_like){echo "class='not-like'";}else{echo "class='is-like'";}?> onclick="market_like(<?php echo $list['article_id'];?>)" id="market_like"></li>
            </ul>
        </div>

        <div class="article-category">
            <form action="">
                <ul>
                    <?php foreach ($category as $val) : ?>
                        <a href="<?php echo HOME_DOMAIN;?>article?category_id=<?php echo $val['article_category_id'];?>">
                            <li <?php if ($list['article_category_id'] == $val['article_category_id']){echo "class='current_category'";}?>><?php echo $val['category_name'];?></li>
                        </a>
                    <?php endforeach;?>
                </ul>
            </form>
        </div>

    </div>
</div>
<div class="clear_both"></div>

<!--评论操作-->
<script type="text/javascript">
    var interval_time = 15000;  //5秒提示一次点赞操作

    // 定时检测当前用户是否已点赞，提示点赞操作
    var like_notice = setInterval("notice_like()", interval_time);


    // 提示文章点赞操作
    function notice_like () {
        var current_like_stat = $("#market_like").attr("class");
        // 已点赞，清除定时提示
        if ('is-like' == current_like_stat) {
            clearInterval(like_notice);
        // 未点赞，定时提示
        } else if ('not-like' == current_like_stat) {
            layer.tips('如果喜欢这篇文章，给个赞吧', '#market_like', {tips: [2, '#87CEFA'],time: 3000});
        }
    }

    // 点赞操作
    function market_like (article_id) {
        // 获取当前点赞按钮的样式值
        var market_btn_class = $("#market_like").attr('class');

        $.post('<?php echo HOME_DOMAIN; ?>article/market_like', 'article_id='+article_id, function (response) {
            if (true === response.success) {
                // 更改文章点赞数显示
                var like_num = parseInt($("#like_num").html())+1;
                $('#like_num').html(like_num)
                // 更改点赞状态显示
                if ('not-like' == market_btn_class) {
                    $("#market_like").attr('class','is-like');
                }
                // 点赞成功提示
                layer.tips('小编会继续努力的(^o^)', '#market_like', {tips: [2, '#87CEFA'],time: 3000});
            } else {
                layer.tips(response.message, '#market_like', {tips: [2, '#87CEFA'],time: 3000});
            }
        }, 'JSON');
    }
</script>