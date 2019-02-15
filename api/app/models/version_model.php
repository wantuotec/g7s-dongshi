<?php
/**
 * APP 版本
 *
 * @author       杨海波
 * @date         2015-05-05
 * @category     Order_model
 * @copyright    Copyright(c) 2015
 *
 * @version      $Id$
 */
class Version_model extends CI_Model
{
    /**
     * 获得最新版本信息
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function get_version($params = [])
    {
        data_filter($params);

        if (empty($params['app_type'])) {
            $this->set_error(47351);
            return false;
        }

        if(empty($params['app_version'])) {
            $this->set_error(47352);
            return false;
        }

        // 鲜米用户端 2.0.0 版本不能升级
        if (33 == $params['app_type'] && in_array($params['app_version'], ['2.0.0'])) {
            return [];
        }

        $service_info = [
            'service_name'   => 'base.version.get_version',
            'service_params' => $params,
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return $result['data'] ? $result['data'] : [];
    }

    /**
     * 获得版本列表
     *
     * @param   array   $params     查询参数
     *
     * @return  array|bool
     */
    public function get_version_list($params = [])
    {
        data_filter($params);

        $service_info = [
            'service_name'   => 'base.version.get_list',
            'service_params' => $params,
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return $result['data'] ? $result['data'] : [];
    }
}
