<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!--meta content="text/html; charset=gb2312" http-equiv="Content-Type"/-->
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
    <meta name="renderer" content="webkit">
    <link rel="shortcut icon" href="<?php echo HOME_DOMAIN;?>images/admin/icons/dhsp.ico" type="image/x-icon" />
    <title><?php echo TITLE; ?><?php if($title){echo ' - '.$title;} ?></title>
    <?php echo  css_tag(array('base.css'),$css) ?>
    <?php echo  script_tag(array('jquery/jquery-1.8.3.min.js', 'layer/layer.js', 'laydate/laydate.js', 'jquery/jquery.validate.1.9.min.js','jquery/jquery.metadata.js', 'My97DatePicker/WdatePicker.js', 'base.js', 'validate.js', 'jquery/plugins/jquery.form.js'), $js) ?>
    <!--[if IE 6]>
        <?php echo  script_tag(array('jquery/jquery.fixpng.min.js'),$js) ?>
        <script>DD_belatedPNG.fix('.pngfix')</script>
    <![endif]-->
</head>
<?php
  //if($_SESSION['userName'] == 'Ogawa'){ print_r($_SERVER); echo "<hr>"; print_r($_REQUEST); }
    if (preg_match("/\bindex\/\?\b/i", $_SERVER['HTTP_REFERER'])) {
        $_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
    }else{
        if (preg_match("/\bindex\b/i", $_SERVER['SCRIPT_URI']))
            $_SESSION['HTTP_REFERER'] = false;
    }

if (!in_array($_SERVER['REQUEST_URI'], array('/auth', '/admin/siderbar','/admin/info'))) {?>
<?php
    if (isset($_GET['menu_param'])) {
        $params = base64_decode($_GET['menu_param']);
        $params = json_decode($params, true);
        $_SESSION['path_tree']['url'][0] = urldecode($params['parent_name']);
        $_SESSION['path_tree']['url'][1] = urldecode($params['child_name']);
        $_SESSION['path_tree']['func']   = $params['function_name'];
    }
    
    $param_array = array($_SESSION['path_tree']['func'], $_SESSION['path_tree']['func']. '/', $_SESSION['path_tree']['func']. '/index');

    $key = key($_REQUEST);

    //if (!empty($key) && !in_array($key, $param_array)) {
    //    $_SESSION['path_tree']['url'][1] = "<a href=/" . $_SESSION['path_tree']['func'] ." >". $_SESSION['path_tree']['url'][1] ."</a>";
    //}


    //echo $_SESSION['path_tree']['url'][0] .'&nbsp;&nbsp;/&nbsp;&nbsp;'. $_SESSION['path_tree']['url'][1];
}
?>

<script>
    $(function(){
        $("#prev_page").click(function(){
            var referer = document.referrer;
            //if (referer.indexOf('menu_param') == -1) {
                history.go(-1);                
            //}
            return false;
        })
    })
</script>

<?php if(!in_array($_SERVER['REQUEST_URI'], array('/auth', '/admin/siderbar')) && $_GET['nobar'] !== '1') { ?>
<table border="0" width="100%" class="content-box-header" style="border-radius:0;margin: 0 5px;">
    <tr>
        <td width="50%">
            <h3>
            <a href="/admin/info" id="home_page">首页</a>
            <?php if($_SERVER['REQUEST_URI'] != '/admin/info') { ?>
                &nbsp;&nbsp;&gt;&nbsp;&nbsp;
                <?php echo  $_SESSION['path_tree']['url'][0] .'&nbsp;&nbsp;&gt;&nbsp;&nbsp;'. $_SESSION['path_tree']['url'][1]; ?>
                <?php if($_SERVER['HTTP_REFERER'] && false === strpos($_SERVER['HTTP_REFERER'], '/admin/siderbar')) { ?>
                &nbsp;&nbsp;&gt;&nbsp;&nbsp;<a href="<?php echo  $_SERVER['HTTP_REFERER'] ?>">返回上页</a>
                <?php }?>
            <?php } ?>
            </h3>
        </td>
        <td width="50%">
            <h3 style="float: right;margin-right: 5px;">
            <!--<a href="<?php echo  HOME_DOMAIN; ?>" target="_blank" >查看前台</a>&nbsp;|&nbsp;-->
            <a onclick="$.BKD.open('iframe', '<?php echo  HOME_DOMAIN ?>auth/reset_password?nobar=1', '400px', '340px')" href="#"><img src="<?php echo HOME_DOMAIN ?>images/admin/icons/16/editpwd.png" alt="修改密码"/>修改密码</a>&nbsp;|&nbsp;
            <a target="_parent" href="<?php echo  HOME_DOMAIN ?>auth/loginout" ><img src="<?php echo HOME_DOMAIN ?>images/admin/icons/16/loginout.png" alt="登出"/>登出</a>
            </h3>
        </td>
    </tr>
</table>
<?php } ?>