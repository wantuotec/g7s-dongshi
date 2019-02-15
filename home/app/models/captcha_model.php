<?php
/**
 * 验证码
 *
 * @author       madesheng
 * @date         2016-11-16
 * @category     Captcha_model
 * @copyright    Copyright(c) 2016
 * @version      $Id$
 */
class Captcha_model extends CI_Model
{
    /**
     * 获取图片验证码
     *
     * @access  public
     *
     * @return  void
     */
    public function image($params = [])
    {
        data_filter($params);

        $service_info = [
            'service_name'   => 'base.captcha.generate_image_captcha',
            'service_params' => $params,
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['message']);
            return false;
        } else {
            // 如果是 IE6/7 无法解析 data:image
            if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6.0') || strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0')) {
                $result['data']['captcha_html'] = '<img src="' . site_url('captcha/show?img=') . urlencode($result['captcha_base64']) .'" >';
            } else {
                $result['data']['captcha_html'] = '<img src="data:image/png;base64,' . $result['data']['captcha_base64'] .'" >';
            }

            return $result['data'];
        }
    }

    // 发送短信验证码
    public function sms($params = [])
    {
        data_filter($params);

        if (empty($params['phone_number'])) {
            $this->set_error('请输入手机号');
            return false;
        }

        if ($params['is_check'] == true) {
            if (empty($params['captcha_key']) || empty($params['captcha_content'])) {
                $this->set_error('请输入图片验证码');
                return false;
            }
        }

        $service_info = [
            'service_name'   => 'base.captcha.generate_sms_captcha',
            'service_params' => $params,
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return $result['data'];
    }

    /**
     * 把 BASE64 转成图片验证码（主要是为了兼容IE6/7）
     *
     * @access  public
     *
     * @return  void
     */
    public function show()
    {
        if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6.0') || strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0'))
        {
            $img = $this->input->get('img');
            if (!empty($img)) {
                header("Content-Type: image/png");
                echo base64_decode($img);
            }
        }
        exit;
    }
}