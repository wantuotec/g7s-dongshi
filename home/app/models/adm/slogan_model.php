<?php
/**
 * 网站slogan管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    slogan_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Slogan_model extends CI_Model
{
    /**
     * 获取网站基本信息
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_slogan_list($params = [])
    {
        data_filter($params);

        // 获取用户基本信息
        $service_shop = [
            'service_name'   => 'operation.slogan.get_list',
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
     * 通过ID获取单条网站slogan
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_slogan($params = [])
    {
        data_filter($params);

        $service_info = [
            'service_name'   => 'operation.slogan.get',
            'service_params' => [
                'slogan_id'  => filter_empty('slogan_id', $params),
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }

    /**
     * 编辑slogan信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_slogan($params = [])
    {
        data_filter($params);

        if (empty($params['slogan_id'])) {
            $this->set_error('记录ID为空');
            return false;
        }
        append_update_info($params);

        // 保存修改信息
        $service_shop = [
            'service_name'   => 'operation.slogan.update_by_params',
            'service_params' => [
                'slogan_id' => $params['slogan_id'],
                'set'       => $params,
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