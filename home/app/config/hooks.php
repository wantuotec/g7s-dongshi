<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
if (php_sapi_name() != 'cli') { // �����������ģʽ�������ô˹���
 
}

// 控制页面的散点线条效果
$hook['post_controller_constructor'][] = array(
    'class'    => 'Page_controller',
    'function' => 'flowing_line',
    'filename' => 'page_controller.php',
    'filepath' => 'hooks',
    'params'   => array()
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
