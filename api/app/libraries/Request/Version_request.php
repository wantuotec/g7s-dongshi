<?php
/**
 * Version Request Factory
 *
 * @author     杨海波
 * @date       2015-05-05
 * @category   Version
 * @copyright  Copyright (c)  2015
 *
 * @version    $Id$
 */
class MY_Version_request extends MY_Request
{
    /**
     * 获取最新版本
     *
     * @param   array   $params   输入参数
     *
     * @return  bool|array
     */
    public function get($params = array())
    {
        // 权限验证
        $this->access(array('public', 'shop', 'member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'app_type'    => empty($params['app_type'])    ? null : intval($params['app_type']),
                'app_version' => empty($params['app_version']) ? null : $params['app_version'],
                'group'       => $userinfo['group'],
            ),
            'shop' => array(
                'shop_id'     => intval($userinfo['shop_id']),
                'app_type'    => empty($params['app_type'])    ? null : intval($params['app_type']),
                'app_version' => empty($params['app_version']) ? null : $params['app_version'],
                'group'       => $userinfo['group'],
            ),
            'member' => array(
                'member_id'   => intval($userinfo['member_id']),
                'app_type'    => empty($params['app_type'])    ? null : intval($params['app_type']),
                'app_version' => empty($params['app_version']) ? null : $params['app_version'],
                'group'       => $userinfo['group'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'version_id',
                'app_version',
                'app_type',
                'file',
                'description',
                'is_reload',
            ),
            'shop' => array(
                'version_id',
                'app_version',
                'app_type',
                'file',
                'description',
                'is_reload',
            ),
            'member' => array(
                'version_id',
                'app_version',
                'app_type',
                'file',
                'description',
                'is_reload',
            ),
        );

        $this->load->model('Version_model');
        $result = $this->Version_model->get_version($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Version_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 获取是否要访问测试环境
     *
     * @param   array   $params   输入参数
     *
     * @return  bool|array
     */
    public function get_env($params = array())
    {
        // 权限验证
        $this->access(array('public', 'shop', 'member'));

        $app_info = $this->get_app_info();

        $app_type = $app_info['app_type'];

        $app_version = $app_info['app_version'];

        $key = $app_type . '_' . $app_version;

        $this->load->library('requester');

        $instance = & get_instance();
        $config = get_custom_config($instance, 'is_access_test_environment');
        if (false === $config) {
            $this->set_error($instance->get_error());
            return false;
        }

        $config = json_decode($config, true);

        $is_access_test_environment = isset($config[$key]) ? $config[$key] : 2;
        return compact('is_access_test_environment');
    }
}
