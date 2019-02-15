<?php
/**
 * 网站slogan管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    module_manage_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Module_manage_model extends CI_Model
{
    /**
     * 获取网站基本信息
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_module_list($params = [])
    {
        data_filter($params);

        // 获取用户基本信息
        $service_shop = [
            'service_name'   => 'operation.module_manage.get_list',
            'service_params' => [],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_shop);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return $result['data'];
    }

    /**
     * 编辑slogan信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_module($params = [])
    {
        data_filter($params);

        if (empty($params['module_manage_id'])) {
            $this->set_error('模块记录ID为空');
            return false;
        }
        append_update_info($params);

        // 保存修改信息
        $service_shop = [
            'service_name'   => 'operation.module_manage.update_by_params',
            'service_params' => [
                'module_manage_id' => $params['module_manage_id'],
                'set'              => $params,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_shop);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }
}