<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <link rel="shortcut icon" href="<?php echo HOME_DOMAIN;?>public/images/horse.ico" type="image/x-icon" />
    <title><?php echo  TITLE ?></title>
    <meta name="description" content=""/>
    <?php echo css_tag(array('admin/base.css'),$css) ?>
    <?php echo script_tag(array('jquery/jquery-1.8.3.min.js','admin/base.js'),$js) ?>
    <!--[if IE 6]>
        <?php echo script_tag(array('jquery/jquery.fixpng.min.js'),$js) ?>    
        <script>DD_belatedPNG.fix('.pngfix')</script>
    <![endif]-->
</head>