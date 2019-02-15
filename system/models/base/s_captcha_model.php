<?php
 /**
 * 验证码管理
 *
 * @author      liunian
 * @date        2015-01-04
 * @category    Captcha
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class S_captcha_model extends CI_Model
{
    // 初始化
    public $id  = 'captcha_id';
    public $dao = 'base/s_captcha_dao';

    public $expire_time = 10; // 验证码过期时间 单位 分钟

    /**
     * 获取验证码列表
     *
     * @access  public
     *
     * @param   array   $params     查询参数
     *
     * @return  bool|array
     */
    public function get_captcha_list($params = [])
    {
        data_filter($params);

        $input  = ['captcha_content','order_by','is_pages','phone_number','project_id','page_size','is_used'];
        $output = ['list','total','pages'];

        $this->filter($input, $params);

        $result = $this->get_list($params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } 

        $this->filter($output, $result);

        return $result;
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

        $config = array();
        if (!empty($params['width']) && $params['width'] > 0) {
            $config['width'] = intval($params['width']);
        }

        if (!empty($params['height']) && $params['height'] > 0) {
            $config['height'] = intval($params['height']);
        }

        // 生成图片验证码
        $this->load->library('captcha');
        $captcha = $this->captcha->create_base64($config);

        $captcha_data = array(
            'captcha_key'     => get_unique_key(),
            'captcha_content' => $captcha['word'],
            'phone_number'    => '',
            'project_id'      => intval($params['project_id']),
            'create_time'     => date('Y-m-d H:i:s'),
        );

        $result = $this->add($captcha_data, false, true);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return [
            'captcha_key'    => $captcha_data['captcha_key'],
            'captcha_base64' => $captcha['base64'],
            // 'captcha_html'   => '<img src="data:image/jpg/png/gif;base64,' . $captcha['base64'] .'" >',
        ];
    }

    /**
     * 校验验证码是否正确
     *
     * @param   array    $params    请求参数type, captcha_key, phone_number, captcha_content
     *
     * @return  bool
     */
    public function is_valid_captcha($params = [])
    {
        data_filter($params);
        $type = isset($params['type']) ? $params['type'] : null;

        if (empty($params['captcha_key'])) {
            $this->set_error(47001);
            return false;
        }

        if (empty($params['captcha_content'])) {
            $this->set_error(47003);
            return false;
        }

        // 获取验证码的数据
        $captcha = $this->get_by_captcha_key($params['captcha_key']);
        if (empty($captcha)) {
            $this->set_error(47004);
            return false;
        }

        // 图形验证码和短信验证码同时存在的情况，需要先验证是否有发送过验证码
        if ('sms' === $type) {
            if (!is_phone($params['phone_number'])) {
                $this->set_error(47002);
                return false;
            }
            if (empty($captcha['phone_number'])) { // 需要手机验证的情况下，用户没有点“发送手机验证码”按钮
                $this->set_error(47020);
                return false;
            }
            if ($captcha['phone_number'] != $params['phone_number']) {
                $this->set_error(47008);
                return false;
            }
        }

        // 验证码错误
        if (strtolower($captcha['captcha_content']) != strtolower($params['captcha_content'])) {
            if ('sms' === $type) {
                $this->set_error(47022);
            } else {
                $this->set_error(47005);
            }
            return false;
        }

        // 已使用
        if (1 == $captcha['is_used']) {
            $this->set_error(47006);
            return false;
        }

        // 10分钟内效
        if ((strtotime($captcha['create_time']) + $this->expire_time * 60) < time()) {
            $this->set_error(47007);
            return false;
        }

        // 把验证码标记为已使用
        $result = $this->update_by_captcha_key($params['captcha_key'], ['is_used' => 1]);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return true;
    }

}