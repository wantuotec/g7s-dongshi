<h1 class="top_nav">
    <span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span>
    <span class="title_wd"><?php echo $slogan;?></span>
</h1>

<div class="life-main">
    <div class="cont-left">
        <?php foreach ($article as $val) : ?>
            <div class="life-cont">
                <input type="hidden" name="article_id" value="<?php echo $val['article_id'];?>">
                <h3><span>[<?php echo $val['category_name'];?>]</span> <?php echo $val['article_title'];?></h3>
                <figure><img src="<?php echo $val['cover_photo'];?>" alt="<?php echo $val['article_title'];?>"></figure>
                <p><?php echo $val['cover_words'];?></p>
                <div class="clear_both"></div>
                <div class="life_date_view">
                    <span class="date"><?php echo $val['date'];?> </span>
                    <span class="origin"><?php echo $val['origin_type_title'];?> </span>
                    <span class="browse"><?php echo $val['read_num'];?> </span>
                    <span class="like"><?php echo $val['like_num'];?> </span>
                    <a href="<?php echo HOME_DOMAIN;?>article/article_detail?article_id=<?php echo $val['article_id'];?>" class="readmore">阅读全文>></a>
                </div>
            </div>
        <?php endforeach;?>
    </div>
    <div class="cont-right">
        <form action="" method="get">
            <ul>
                <?php foreach ($category as $val) : ?>
                    <a href="<?php echo HOME_DOMAIN;?>article?category_id=<?php echo $val['article_category_id'];?>">
                        <li <?php if ($search['category_id'] == $val['article_category_id']){echo "class='current_category'";}?>><?php echo $val['category_name'];?></li>
                    </a>
                <?php endforeach;?>
            </ul>
        </form>
    </div>
</div>
<div class="clear_both"></div>
<!-- 底部分页 -->
<?php echo $pagination; ?>