<!--这里的模板样式影响到其它页面，因此不作全局引进-->
<link href="<?php echo HOME_DOMAIN;?>public/css/photo.css" rel="stylesheet" type="text/css">

<h1 class="top_nav">
    <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
    <span class="title_wd">每一张照片，都是时光的标本。</span>
</h1>

<body>
    <div class="main-container">
        <nav class="main-nav">
            <form action="">
                <ul class="nav right center-text">
                    <?php if (!empty($album_list)): 
                        foreach ($album_list as $album):
                    ?>
                        <li>
                            <a href="<?php echo HOME_DOMAIN;?>photo/photo_list?photos_album_id=<?php echo $album['photos_album_id'];?>"><input type="button"  value="<?php echo $album['album_name'];?>" <?php if ($search['photos_album_id'] == $album['photos_album_id']){echo "class='current_album'";}?>></a>
                        </li>
                    <?php endforeach;endif;?>
                </ul>
            </form>
        </nav>

        <div class="content-container">
            <header>
                <h1 class="center-text"> 光影集 </h1>
            </header>
            <div id="portfolio-content" class="center-text">
                <div class="portfolio-page" id="page-1">
                    <?php if (!empty($photos_list)): 
                        foreach ($photos_list as $photo):
                    ?>
                        <div class="portfolio-group">
                            <a class="portfolio-item" href="<?php echo $photo['photo_url'];?>">
                                <img src="<?php echo $photo['photo_url'];?>" alt="image 1">
                                <div class="detail">
                                    <h3>【<?php echo $photo['photo_title'];?>】</h3>
                                    <p><?php echo $photo['photo_describe'];?></p>
                                   <span class="up-view">浏览大图</span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach;endif;?>

            <!-- 底部分页 -->
            <?php echo $pagination; ?>
            </div>
        </div>
    </div>
</body>

<!--单击切换图片-->
<script type="text/javascript">
    $(function () {
        $('.pagination li').click(changePage);
        $('.portfolio-item').magnificPopup({ 
            type: 'image',
            gallery:{
                enabled:true
            }
        });
    });
</script>