<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <?php if($url): ?>
        <meta http-equiv="refresh" content="3;url=<?php echo $url ?>">
    <?php endif; ?>
    <title><?php echo $heading ?>，页面自动跳转中...</title>
    <?php echo css_tag(array('mobile.css'),$css) ?>
    <?php echo script_tag(array('jquery/jquery-1.8.3.min.js'),$js) ?>
    <style>
        h2{padding:0}
        p{padding:5px 0 10px}
    </style>
</head>
<body>
<div id="main-content">
    <div id="header">
		<h2><?php echo $heading; ?> | <a href="http://bkd.myxiequ.com/mobile">回到首页</a></h2>
	</div>	
	<div class="content-box">
		<h2><?php echo $message; ?></h2>
        <?php if($url): ?><a href="<?php echo $url ?>">>>继续操作</a><?php endif; ?>
	</div>
 </div>
</body>
</html>