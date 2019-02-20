<?php
 /**
 * 验证码
 *
 * @author      杨海波
 * @date        2015-06-17
 * @category    Captcha
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class Captcha_model extends CI_Model
{
    public $expire_time = 10; // 验证码过期时间 单位 分钟
    public $max_sms_num = 5;  // 同一个号码，一天最多可以收几条短信

    /**
     * 初始化查询参数
     *
     * @param   array   $params     查询参数
     *
     * @return  array
     */
    private function __init_params(array $params = array())
    {
        data_filter($params);

        $where = array(
            'captcha_key'    => isset($params['captcha_key'])    ? $params['captcha_key']    : null,
            'phone_number'   => isset($params['phone_number'])   ? $params['phone_number']   : null,
            'is_used'        => isset($params['is_used'])        ? $params['is_used']        : null,
            'create_time >=' => isset($params['ge_create_time']) ? $params['ge_create_time'] : null,
            'create_time <=' => isset($params['le_create_time']) ? $params['le_create_time'] : null,
            'is_deleted'     => isset($params['is_deleted'])     ? $params['is_deleted']     : 2,
        );

        invalid_data_filter($where);

        return $where;
    }

    /**
     * 生成图片验证码
     *
     * @param   array    $params    请求参数
     *
     * @return  array|bool
     */
    public function generate_image_captcha($params = [])
    {
        data_filter($params);

        if (empty($params['project_id'])) {
            $this->set_error(20033);
            return false;
        }

        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.captcha.generate_image_captcha',
            'service_params' => $params,
        ];

        $result = $this->requester->request($service_info);
        if (false == $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return $result['data'];
    }

    /**
     * 生成短信验证码
     *
     * @param   array    $params    请求参数
     *
     * @return  array|bool
     */
    public function generate_sms_captcha(array $params = array())
    {
        data_filter($params);

        if (empty($params['project_id'])) {
            $this->set_error(20033);
            return false;
        }

        if (!is_phone($params['phone_number'])) {
            $this->set_error(47002);
            return false;
        }

        // 限制每天每个手机发送验证码的条数
        $result = $this->is_valid_send_number($params);

        // bug fix by mark 之前代码的很容易漏下去
        if (true !== $result) {
            $this->set_error($this->get_error());
            if (!empty($result) && is_array($result)) {
                return $result;
            }

            return false;
        }

        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.captcha.generate_sms_captcha',
            'service_params' => $params,
        ];

        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return ['captcha_key' => $result['data']['captcha_key']];
    }

    /**
     * 限制每天每个手机发送验证码的条数
     *
     * @param   array    $params    验证的参数
     *
     * @return  bool
     */
    public function is_valid_send_number($params = [])
    {
        data_filter($params);

        if (!is_phone($params['phone_number'])) {
            $this->set_error(47002);
            return false;
        }

        // 每隔多少秒才能发一次验证码
        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.configure.get_template_by_configure_name',
            'service_params' => [
                'configure_name' => 'sms_captcha_send_timeout',
            ],
        ];

        $configure_result    = $this->requester->request($service_info);
        $sms_captcha_send_timeout = intval($configure_result['data']);
        if (false === $configure_result['success']) {
            $this->set_error($configure_result['errcode']);
            return false;
        }

        // 每隔多少秒才能发一次验证码
        $lock_key = 'sms_captcha_send_timeout_' . $params['phone_number'];
        $this->load->library('redis');
        $result = $this->redis->lock($lock_key, time(), $sms_captcha_send_timeout); // lock会自动加前lock:前缀
        // 如果加锁失败，表示还没有过重新发送的时间
        if (false === $result) {
            $time_begin = $this->redis->get_lock($lock_key);
            $time_remaining = $sms_captcha_send_timeout - (time() - $time_begin);
            $this->set_error(47015);
            return ['time_remaining' => $time_remaining];
        }

        // 卡住每个手机每天只能发送 n 条短信(后台设置的数量)
        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.configure.get_template_by_configure_name',
            'service_params' => [
                'configure_name' => 'day_people_max_send_captcha_number',
            ],
        ];

        $configure_result    = $this->requester->request($service_info);
        $day_max_send_number = intval($configure_result['data']);
        if (false === $configure_result['success']) {
            $this->set_error($configure_result['errcode']);
            return false;
        }

        // 获取今天这个手机发了多少条
        $search_params = [
            'is_pages'       => false,
            'phone_number'   => $params['phone_number'],
            'ge_create_time' => date('Y-m-d 00:00:00'),
            'le_create_time' => date('Y-m-d 23:59:59'),
            'fields'         =>'captcha_id, phone_number, project_id',
        ];

        $service_info = [
            'service_name'   => 'base.captcha.get_total',
            'service_params' => $search_params,
        ];
        $captcha_result = $this->requester->request($service_info);

        if ($captcha_result['data'] >= $day_max_send_number) {
            $this->set_error(47011);
            return ['day_max_send_number' => $day_max_send_number];
        }

        return true;
    }

    /**
     * 校验验证码是否正确
     *
     * @param   array    $params    请求参数
     * @param   string   $type      类型 sms image
     *
     * @return  bool
     */
    public function is_valid_captcha(array $params = array(), $type)
    {
        data_filter($params);

        if (empty($params['captcha_key'])) {
            $this->set_error(47001);
            return false;
        }

        if ('sms' === $type) {
            if (!is_phone($params['phone_number'])) {
                $this->set_error(47002);
                return false;
            }
        }

        if (empty($params['captcha_content'])) {
            $this->set_error(47003);
            return false;
        }

        $this->load->library('requester');
        $service_info = [
            'service_name'   => 'base.captcha.is_valid_captcha',
            'service_params' => $params,
        ];

        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return true;
    }
}