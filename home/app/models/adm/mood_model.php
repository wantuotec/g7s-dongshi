<?php
/**
 * 心情杂记管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    mood_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Mood_model extends CI_Model
{
    /**
     * 获取心情杂记列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_mood_list($params = [])
    {
        data_filter($params);

        $service_params = [
            'service_name'   => 'mood.mood.get_list',
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

        // 处理图片地址
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$mood) {
                !empty($mood['image_url']) && $mood['image_url'] = $cdn_domain . $mood['image_url'];
            }
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
    public function get_single_mood($params = [])
    {
        data_filter($params);

        if (empty($params['mood_id'])) {
            $this->set_error('心情ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'mood.mood.get',
            'service_params' => [
                'mood_id'  => filter_empty('mood_id', $params),
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
     * 添加心情信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_mood($params = [])
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
            'service_name'   => 'mood.mood.add_mood',
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
     * 编辑心情信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_mood($params = [])
    {
        if (empty($params['mood_id'])) {
            $this->set_error('记录ID为空');
            return false;
        }
        $params['no_filter'] = true;
        append_update_info($params);

        // 保存修改信息
        $service_params = [
            'service_name'   => 'mood.mood.update_by_params',
            'service_params' => [
                'mood_id' => $params['mood_id'],
                'set'     => $params,
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