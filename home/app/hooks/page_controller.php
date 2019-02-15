<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 页面效果控制钩子
 *
 * @author     madesheng
 * @date       2017-11-27
 * @category   Page_controller
 * @copyright  Copyright (c)  2016
 *
 * @version    $Id$
 */
class Page_controller
{
    /**
     * 控制页面的流动线条效果
     *
     * @param   array   $params 参数
     */
    function flowing_line($params)
    {
        $CI = & get_instance();

        $service_info = [
            'service_name'   => 'operation.module_manage.get_list',
            'service_params' => ['fields' => 'module_mark,is_open', 'key_name' => 'module_mark'],
        ];
        $CI->load->library('requester');
        $result = $CI->requester->request($service_info);

        // 将散点线条控制标识放在session中供页面读取
        $_SESSION['flowing_line'] = $result['data']['list'][3]['is_open'];
    }
}