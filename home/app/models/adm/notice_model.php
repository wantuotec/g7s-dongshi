<?php
/**
 * 网站公告管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    notice_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Notice_model extends CI_Model
{
    /**
     * 获取网站公告列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_notice_list($params = [])
    {
        data_filter($params);

        $service_params = [
            'service_name'   => 'operation.website_notice.get_list',
            'service_params' => [
                'is_pages'       => true,
                'is_enabled'     => filter_empty('is_enabled', $params),
                'ge_create_time' => filter_empty('ge_create_time', $params),
                'le_create_time' => filter_empty('le_create_time', $params),
                'order_by'       => 'create_time DESC',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单条网站公告
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_notice($params = [])
    {
        data_filter($params);

        if (empty($params['website_notice_id'])) {
            $this->set_error('公告ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'operation.website_notice.get',
            'service_params' => [
                'website_notice_id'  => filter_empty('website_notice_id', $params),
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
     * 添加网站公告
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_notice($params = [])
    {
        // notice：包含表情等特殊字符，不要使用过滤
        if (empty($params['content'])) {
            $this->set_error('心情内容为空');
            return false;
        }
        $params['no_filter'] = true;
        append_create_update($params);

        // 保存新增信息
        $service_params = [
            'service_name'   => 'operation.website_notice.add_notice',
            'service_params' => [
                'list'         => $params,
                'is_batch'     => false,
                'is_insert_id' => true,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }

    /**
     * 编辑公告信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_notice($params = [])
    {
        if (empty($params['website_notice_id'])) {
            $this->set_error('公告ID为空');
            return false;
        }
        $params['no_filter'] = true;
        append_update_info($params);

        // 保存修改信息
        $service_params = [
            'service_name'   => 'operation.website_notice.update_by_params',
            'service_params' => [
                'website_notice_id' => $params['website_notice_id'],
                'set'               => $params,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }
}