<?php
/**
* Captcha Request Factory
*
* @author     liunian
* @date       2016-01-01
* @category   Captcha
* @copyright  Copyright (c)  2015
*
* @version    $Id$
*/
class MY_Captcha_request extends MY_Request
{
    /**
     * 获取图片验证码
     *
     * @param   array   $params   输入参数
     *
     * @return  bool|array
     */
    public function image($params = array())
    {
        // 权限验证
        $this->access(array('public'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'project_id' => PROJECT_ID,
                'width'      => empty($params['width'])      ? null : intval($params['width']),
                'height'     => empty($params['height'])     ? null : intval($params['height']),
                'group'      => $userinfo['group'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'captcha_key',
                'captcha_base64',
                // 'captcha_html',
            ),
        );

        $this->load->model('Captcha_model');
        $result = $this->Captcha_model->generate_image_captcha($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Captcha_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 获取短信验证码
     *
     * @param   array   $params   输入参数
     *
     * @return  bool|array
     */
    public function sms($params = array())
    {
        // 权限验证
        $this->access(array('public'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'project_id'      => PROJECT_ID,
                'phone_number'    => empty($params['phone_number'])    ? null  : $params['phone_number'],
                'captcha_key'     => empty($params['captcha_key'])     ? null  : $params['captcha_key'],
                'captcha_content' => empty($params['captcha_content']) ? null  : $params['captcha_content'],
                'is_voice_sms'    => !isset($params['is_voice_sms'])   ? false : true,
                'is_check'        => (bool)$params['is_check'],
                'group'           => $userinfo['group'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'captcha_key',
            ),
        );

        $this->load->model('Captcha_model');
        $result = $this->Captcha_model->generate_sms_captcha($params[$userinfo['group']]);

        // bug fix by mark
        if (false === $result) {
            $this->set_error($this->Captcha_model->get_error());
            return false;
        } else {
            if (!empty($result) && is_array($result)) {
                if (isset($result['captcha_key'])) {
                    return $this->group_params($output_format, $result, 1);
                } else {
                    $this->set_replace(array_values($result));
                    $this->set_error($this->Captcha_model->get_error());

                    return false;
                }
            }
        }
    }

    /**
     * 验证验证码是否正确
     *
     * @param   array   $params   输入参数
     *
     * @return  bool
     */
    public function is_valid_captcha($params = array())
    {
        // 权限验证
        $this->access(array('public'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'phone_number'    => empty($params['phone_number'])    ? null : $params['phone_number'],
                'captcha_key'     => empty($params['captcha_key'])     ? null : $params['captcha_key'],
                'captcha_content' => empty($params['captcha_content']) ? null : $params['captcha_content'],
                'type'            => empty($params['type'])            ? null : $params['type'],
                'group'           => $userinfo['group'],
            ),
        );

        // 输出参数
        $output_format = array();

        $this->load->model('Captcha_model');
        $result = $this->Captcha_model->is_valid_captcha($params[$userinfo['group']], $params['type']);

        if (false === $result) {
            $this->set_error($this->Captcha_model->get_error());
            return false;
        } else {
            return $result;
        }
    }
}
