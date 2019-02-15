<?php
/**
 * 网站留言管理
 *
 * @@author     madesheng
 * @date        2017-06-22
 * @category    guestbook_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Guestbook_model extends CI_Model
{
    /**
     * 获取所有留言列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_guestbook_list($params = [])
    {
        data_filter($params);

        // 获取用户基本信息
        $service_shop = [
            'service_name'   => 'member.guestbook.get_list',
            'service_params' => [
                'is_pages'       => true,
                'order_by'       => 'create_time DESC',
                'type'           => filter_empty('type', $params),
                'audit_status'   => filter_empty('audit_status', $params),
                'ge_create_time' => filter_empty('ge_create_time', $params),
                'le_create_time' => filter_empty('le_create_time', $params),
            ],
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
     * 获取待审核留言列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_audit_list($params = [])
    {
        data_filter($params);

        // 获取用户基本信息
        $service_shop = [
            'service_name'   => 'member.guestbook.get_list',
            'service_params' => [
                'is_pages'       => true,
                'order_by'       => 'create_time DESC',
                'ge_create_time' => filter_empty('ge_create_time', $params),
                'le_create_time' => filter_empty('le_create_time', $params),
                'type'           => 1,
                'audit_status'   => 1,
            ],
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
     * 通过ID获取单条留言
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_message($params = [])
    {
        data_filter($params);

        if (empty($params['guestbook_id'])) {
            $this->set_error('留言ID为空');
            return false;
        }

        // 根据ID查询留言信息
        $service_info = [
            'service_name'   => 'member.guestbook.get',
            'service_params' => [
                'guestbook_id' => filter_empty('guestbook_id', $params),
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        // 获取当前留言的回复信息
        $service_info = [
            'service_name'   => 'member.guestbook.get_list',
            'service_params' => [
                'parent_guestbook_id' => filter_empty('guestbook_id', $params),
                'type'                => 2,
                'audit_status'        => 2,
            ],
        ];
        $this->load->library('requester');
        $reply_result = $this->requester->request($service_info);
        if (true != $reply_result['success']) {
            $this->set_error($reply_result['message']);
            return false;
        }

        if (!empty($reply_result['data']['list'])) {
            foreach ($reply_result['data']['list'] as $reply) {
                $result['data']['reply_list'][] = $reply;
            }
        }

        return $result['data'];
    }

    /**
     * 编辑留言
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_guestbook($params = [])
    {
        data_filter($params);

        if (empty($params['guestbook_id'])) {
            $this->set_error('留言ID为空');
            return false;
        }

        // 保存网站信息
        $service_params = [
            'service_name'   => 'member.guestbook.update_by_params',
            'service_params' => [
                'guestbook_id' => $params['guestbook_id'],
                'set'          => $params,
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
     * 管理员回复留言
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function admin_reply($params = [])
    {
        if (empty($params['guestbook_id'])) {
            $this->set_error('用户留言ID为空');
            return false;
        }
        if (empty($params['content'])) {
            $this->set_error('回复内容为空');
            return false;
        }

        // 获取当前IP
        $current_ip = $this->input->ip_address();

        // -----------添加留言回复------------------
        $add_params = [
            'ip'                  => $current_ip,
            'parent_guestbook_id' => $params['guestbook_id'],
            'message'             => $params['content'],
            'type'                => 2,
            'audit_status'        => 2,
            'is_reply'            => 1,  // 目前管理员回复不能再被用户回复，默认已回复
        ];
        append_create_info($add_params);

        $service_shop = [
            'service_name'   => 'member.guestbook.add_guestbook',
            'service_params' => [
                'list'         => $add_params,
                'is_batch'     => false,
                'is_insert_id' => true,
            ],
        ];

        $this->load->library('requester');
        $add_result = $this->requester->request($service_shop);
        if (true != $add_result['success']) {
            $this->set_error($add_result['message']);
            return false;
        }

        // 更改用户留言是否被回复状态
        $service_params = [
            'service_name'   => 'member.guestbook.update_by_params',
            'service_params' => [
                'guestbook_id' => $params['guestbook_id'],
                'set'          => ['is_reply' => 1],
            ],
        ];

        $this->load->library('requester');
        $update_result = $this->requester->request($service_params);
        if (true != $update_result['success']) {
            $this->set_error($update_result['message']);
            return false;
        }

        return true;
    }
}