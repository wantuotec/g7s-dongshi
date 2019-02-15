<?php
/**
 * 网站信息管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    about_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class About_model extends CI_Model
{
    /**
     * 获取网站基本信息
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_website_info($params = [])
    {
        // 获取用户基本信息
        $service_shop = [
            'service_name'   => 'operation.about.get',
            'service_params' => [],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_shop);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        //处理数据
        $result['data']['sex_title'] = $result['data']['sex'] == 1 ? '男' : '女';
        $result['data']['age']       = get_age($result['data']['birthday'])['age'];

        return $result['data'];
    }

    /**
         * 编辑网站信息
         * 
         * @param    $params    array    保存参数
         *
         * @return   array | bool
         */
        public function edit_website_info($params = [])
        {
            // 验证数据
            $check_result = $this->_check_params($params);
            if (false === $check_result) {
                $this->set_error($this->get_error());
                return false;
            }
            $params['no_filter'] = true;
            append_update_info($params);

            // 保存网站信息
            $service_shop = [
                'service_name'   => 'operation.about.update_by_params',
                'service_params' => [
                    'about_id' => $params['about_id'],
                    'set'      => $params,
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

        /**
         * 编辑网站信息-验证数据
         * 
         * @param    $params    array    保存参数
         *
         * @return   array | bool
         */
        public function _check_params($params = [])
        {
            if (!preg_match('/^(\d){4}-(\d){2}-(\d){2}$/', $params['birthday'])) {
                $this->set_error('生日格式需为 0000-00-00 格式');
                return false;
            }

            return true;
        }
}