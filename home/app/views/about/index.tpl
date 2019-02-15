<article class="aboutcon">
    <h1 class="top_nav"><span class="title_img"><img class="hello_n1" src="<?php echo HOME_DOMAIN;?>public/images/snail.gif" alt=""><img class="hello_n2" src="<?php echo HOME_DOMAIN;?>public/images/hello_like.png" alt=""></span><span class="title_wd"><?php echo $slogan['content'];?></span></h1>

    <div class="about left">
        <h2>Just about me</h2>
        <ul><?php echo $website['master_describe'];?></ul>
        <h2>About website</h2>
        <p><span>网站域名：</span><a href="<?php echo HOME_DOMAIN?>"><?php echo $website['domain_name'];?></a></p>
        <p><span>创建时间：</span><?php echo $website['website_create'];?></p>
        <p><span>当前版本：</span><?php echo $website['version'];?></p>
        <p><span>后台服务：</span><?php echo $website['servicer'];?></p>
        <p><span>服务程序：</span><?php echo $website['program'];?></p>
    </div>

    <aside class="right information">  
        <div class="about_c">
            <p><span>网 名：</span><?php echo $website['nickname'];?></p>
            <p><span>姓 名：</span><?php echo $website['real_name'];?></p>
            <p><span>性 别：</span><?php echo $website['sex_title'];?></p>
            <p><span>年 龄：</span><?php echo $website['age'];?>岁</p>
            <p><span>籍 贯：</span><?php echo $website['register_address'];?></p>
            <p><span>现 居：</span><?php echo $website['live_address'];?></p>
            <p><span>职 业：</span><?php echo $website['job'];?></p>
            <p><span>联 系：</span><?php echo $website['contact'];?></p>
            <p><span>喜欢的书籍：</span><?php echo $website['like_books'];?></p>
            <p><span>喜欢的音乐：</span><?php echo $website['like_musics'];?></p>
        </div>
        <p class="wechat_title"><img src="<?php echo HOME_DOMAIN;?>public/images/wechat_50.png">欢迎关注小编的微信...</p>
        <div class="wechat_code">
            <img src="<?php echo HOME_DOMAIN;?>public/images/wechat_code.jpg">
        </div>
    </aside>
</article>