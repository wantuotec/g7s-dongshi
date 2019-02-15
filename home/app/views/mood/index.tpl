<div class="moodlist">
    <h1 class="top_nav">
        <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
        <span class="title_wd"><?php echo $slogan;?></span>
    </h1>

    <div class="bloglist-wrap">
        <div class="bloglist">
            <?php if (is_array($moods) && !empty($moods)) { foreach ($moods as $val) { ?>
            <ul class="arrow-box">
                <img class="pin" src="<?php echo HOME_DOMAIN;?>public/images/pin.png">
                <div class="content-box">
                    <p>
                        <?php echo $val['content'];?>
                    </p>
                </div>
                <span class="mood_date_view"><?php echo $val['date'];?></span>
            </ul>
            <?php } } ?>
        </div>
    </div>

    <!-- 底部分页 -->
    <?php echo $pagination; ?>
</div>
<script>
    // 富文本编辑器可能给图片包裹多个父标签，因此给每个img图片增加一个指定的父标签，用来打开图片
    $(function(){
        var img_box = "<span class='img-box'></span>";
        $(".content-box p").each(function(){
            $(this).find("img").wrap(img_box)
        });

        //弹出图片相册
        layer.photos({
          photos: '.img-box',
          anim: 4, //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
        });
    });
</script>