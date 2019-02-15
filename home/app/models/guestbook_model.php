<?php
/**
 * 用户留言管理
 *
* @author     madesheng
* @date       2017-06-01
* @category   Guestbook_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Guestbook_model extends CI_Model
{
    /**
     * 获取用户留言列表
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_guestbook_list()
    {
        // 获取当前IP
        $current_ip = $this->input->ip_address();

        // 拉取所有已审核通过的留言 & 基于当前用户IP的留言(level一级用户留言)
        $service_info = [
            'service_name'   => 'member.guestbook.get_list',
            'service_params' => [
                'is_pages'   => true,
                'page_size'  => 10,
                'level'      => 1,
                'type'       => 1,
                'key_name'   => 'guestbook_id',
                'order_by'   => 'create_time DESC',
                'or_where'   => [
                    'audit_status' => 2,
                    "(`ip`='{$current_ip}' and `audit_status`=1)",
                ],
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        // 获取子级留言
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as $key => &$val) {
                $service_info = [
                    'service_name'   => 'member.guestbook.find_sub_guestbook',
                    'service_params' => [
                        'logic'        => 1,  //1.前端 2.后台
                        'guestbook_id' => $key,
                        'ip'           => $current_ip,
                    ],
                ];

                $sub_result = $this->requester->request($service_info);
                if (true != $sub_result['success']) {
                    $this->set_error($sub_result['message']);
                    return false;
                }

                $val['admin_return'] = $sub_result['data'];

                // 随机系统头像
                $num = rand(1, 10);
                $val['header_img'] = HOME_DOMAIN . 'public/images/header/header_'. $num .'.jpg';
            }
        }

        return $result['data'];
    }

    /**
     * 添加用户留言-包含回复
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_message($params = [])
    {
        $this->load->library('requester');

        // notice：包含表情等特殊字符，不要使用过滤
        if (empty($params['message'])) {
            $this->set_error('内容为空啊，小编是读不懂空气的哟！');
            return false;
        }

        // 如果有传上级ID，则获取相关信息
        if (!empty($params['id'])) {
            $service_params = [
                'service_name'   => 'member.guestbook.get_by_params',
                'service_params' => ['fields' => 'guestbook_id,level','guestbook_id' => $params['id']],
            ];
            $parent_result = $this->requester->request($service_params);
            if (true != $parent_result['success']) {
                $this->set_error($parent_result['message']);
                return false;
            }

            $params['parent_guestbook_id'] = $parent_result['data']['guestbook_id'];
            $params['level']               = $parent_result['data']['level'] + 1;
        }

        // 获取IP地址归属地
        $addr_temp = getTaobaoAddress($params['ip']);
        $addr_data = $addr_temp['data'];
        if ('内网IP' != $addr_data['country']) {
            $address = $addr_data['country'] . $addr_data['area'] . $addr_data['region'] . $addr_data['city'] . '-' . $addr_data['isp'];
        } else {
            $address = $params['ip'];
        }

        $add_params = [
            'message'             => filter_empty('message', $params),
            'parent_guestbook_id' => filter_empty('parent_guestbook_id', $params),
            'level'               => filter_empty('level', $params),
            'ip'                  => filter_empty('ip', $params),
            'address'             => $address,
            'type'                => 1,
            'no_filter'           => true,  //不要使用过滤
        ];
        append_create_info($add_params);
        invalid_data_filter($add_params);

        // 保存新增信息
        $service_params = [
            'service_name'   => 'member.guestbook.add_guestbook',
            'service_params' => [
                'list'         => $add_params,
                'is_batch'     => false,
                'is_insert_id' => true,
            ],
        ];

        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }
}
