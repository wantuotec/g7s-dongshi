<?php
/**
 * APP 版本
 *
 * @author       许成
 * @date         2016-08-23
 * @category     Common_model
 * @copyright    Copyright(c) 2016
 *
 * @version      $Id$
 */
class Common_model extends CI_Model
{
    /**
     * 验证版本
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function check_version_is_upgrade($params)
    {
        // 获取指定时间不活跃用户配置
        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.configure.get_template_by_configure_name',
            'service_params' => [
                'configure_name' => 'check_version_is_upgrade',
            ],
        ];
        $configure_result = $this->requester->request($service_info);
        if (false === $configure_result['success']) {
            $this->set_error($configure_result['errcode']);
            return false;
        }

        $data = json_decode($configure_result['data'], true);
        if (!empty($data) && is_array($data)) {
            $app_type = isset($data['app_types'][$params['app_type']]) ? $data['app_types'][$params['app_type']] : [];
            if (!empty($app_type)) {
                if (strcmp($params['app_version'], $app_type['min_upgrade_version']) <= 0) {
                    $this->set_error(20038);
                    return false;
                }
            }
        }

        return true;
        /*
        $this->load->library('requester');

        $service_info = [
            'service_name'   => 'fdm_order.order_base.get_config',
            'service_params' => $params,
        ];

        $result = $this->requester->request($service_info);

        if ($result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['errcode']);
            return false;
        }
        */
    }
}