<!--这里的模板样式影响到其它页面，因此不作全局引进-->
<link href="<?php echo HOME_DOMAIN;?>public/css/photos_album.css" rel="stylesheet" type="text/css">

<h1 class="top_nav">
    <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
    <span class="title_wd">镜头记录的不只是光影，也是一种生活。</span>
</h1>

<body>
    <div class="main-container">
        <ul class="album-list">
            <h2 class="album-title">相册列表</h2>
            <?php if (!empty($album_list)): 
                foreach ($album_list as $album):
            ?>
                <li>
                    <input type="hidden" name="photos_album_id" value="<?php echo $album['photos_album_id'];?>">
                    <div class="album-img">
                        <img src="<?php echo $album['cover_url'];?>" alt="">
                        <span><?php echo $album['photos_num'];?></span>
                    </div>
                    <div class="album-desc"><?php echo $album['album_name'];?></div>
                </li>
            <?php endforeach;endif;?>
            <div class="clear"></div>
        </ul>
    </div>
</body>

<script type="text/javascript">
    $(function(){
        // 点击相册，跳转到具体照片列表
        $('.album-list li').click(function(){
            var photos_album_id = $(this).find("input[name='photos_album_id']").val();
            $.HOME.redirect("<?php echo HOME_DOMAIN;?>photo/photo_list?photos_album_id=" + photos_album_id);
        });
    });
</script>