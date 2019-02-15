<?php
/**
 * 用户管理
 *
* @author     madesheng
* @date       2017-06-01
* @category   Member_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Member_model extends CI_Model
{
    /**
     * 获取心情文字列表
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_mood_list($params = [])
    {
        data_filter($params);

        $service_info = [
            'service_name'   => 'mood.mood.get_list',
            'service_params' => [
                'is_pages'   => true,
                'is_enabled' => 1,
                'order_by'   => 'create_time DESC',
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        // 处理部分字段：日期
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as $key => &$val) {
                $val['date'] = $val['create_time'];
            }
        }

        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }
}
