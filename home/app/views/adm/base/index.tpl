<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <link rel="shortcut icon" href="<?php echo HOME_DOMAIN;?>public/images/horse.ico" type="image/x-icon" />
    <title><?php echo ADM_TITLE; ?><?php if($title) ' - '.$title ?></title>
    <?php echo css_tag(array('admin/base.css'),$css) ?>
    <?php echo script_tag(array('jquery/jquery-1.8.3.min.js'), $js) ?>
    <script>
        var keepLogin = function () {
            $.get('/adm_auth/get_current_user_id');
        }
        setInterval(keepLogin, 600000);
    </script>
</head>
    <frameset id="frameset" name="main" cols="235px,*" frameborder="0" >
        <frame id="siderbar" name="siderbar" src="<?php echo HOME_DOMAIN; ?>admin/siderbar" noresize="noresize" scrolling="auto"></frame>
        <frame id="content" name="content" src="<?php echo HOME_DOMAIN; ?>admin/info" noresize="noresize" scrolling="auto"></frame>
     </frameset>
</html>
