<!doctype html>

<html lang="zh-cn">

<head>

<title><?php echo TITLE;?></title>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>

<meta name="keywords" content="<?php echo KEYWORDS;?>" />

<meta name="description" content="<?php echo DESCRIPTION;?>" />

<meta name="viewport" content="width=device-width, initial-scale=1"/>

<link rel="shortcut icon" href="<?php echo HOME_DOMAIN;?>public/images/horse.ico" type="image/x-icon" />

<?php echo css_tag(array('base.css', 'index.css', 'date.css', 'about.css', 'mood.css', 'life.css', 'magnific-popup.css', 'guestbook.css', 'article_detail.css', 'sinaFaceAndEffec.css'),$css) ?>

<!--[if lt IE 9]>
<script src="js/modernizr.js"></script>
<![endif]-->

<?php echo script_tag(array('jquery.js', 'My97DatePicker/WdatePicker.js', 'date.js', 'base.js', 'jquery-1.11.1.min.js', 'jquery.easing.1.3.js', 'modernizr.2.5.3.min.js', 'jquery.magnific-popup.min.js', 'templatemo_script.js', 'guestbook_input.js', 'sinaFaceAndEffec.js','layer/layer.js', 'slideUnlock/jquery.slideunlock.js'), $js) ?>

</head>

<body>

 <header>
    <a name="top" id="top"></a>
    <div id="logo"><a href="/"></a></div>
    <nav class="topnav" id="topnav">
        <a <?php if($current_title && $current_title == 'home'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>home/index"><span>首页</span><span class="en">Home</span></a>
        <a <?php if($current_title && $current_title == 'about'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>about/index"><span>关于这里</span><span class="en">About</span></a>
        <a <?php if($current_title && $current_title == 'article'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>article/index"><span>生活随笔</span><span class="en">Article</span></a>
        <a <?php if($current_title && $current_title == 'mood'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>mood/index"><span>心情杂记</span><span class="en">Mood</span></a>
        <a <?php if($current_title && $current_title == 'photo'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>photo/index"><span>光影流年</span><span class="en">Photo</span></a>
        <a <?php if($current_title && $current_title == 'guestbook'){ echo "class='topnav_current'"; }?> href="<?php echo HOME_DOMAIN;?>guestbook/index"><span>留言版</span><span class="en">Guestbook</span></a>
        <!-- <a <?php if($current_title && $current_title == 'login'){ echo "class='topnav_current'"; }?> href="javascript:void(0)" onclick="$.HOME.open('iframe','<?php echo HOME_DOMAIN;?>member/auth?nobar=1','800px', '550px', 'true', '登录|注册')" style="text-align:left;">
            <img src="<?php echo HOME_DOMAIN;?>public/images/centre.png" alt="" class="member-header-img"><span class="en">Hi！请登录</span>
        </a> -->
    </nav>
</header>
<!--页面流动散点线条效果-->
<?php if (1 == $_SESSION['flowing_line']): ?>
    <script type="text/javascript" color="0,0,255" opacity='0.5' zIndex="-2" count="99" src="<?php echo HOME_DOMAIN;?>public/js/canvas-nest.min.js"></script>
<?php endif; ?>