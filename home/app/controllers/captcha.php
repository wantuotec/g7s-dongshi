<?php
/**
 * 信息跟验证码
 *
 * @author       liunian
 * @date         2016-01-26
 * @category     captcha
 * @copyright    Copyright(c) 2016
 * @version      $Id$
 */
class Captcha extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取图片验证码
     *
     * @access  public
     *
     * @return  void
     */
    public function image()
    {
        if ($this->input->is_post_request()) {
            $params = $this->input->post();
        } else {
            $params = $this->input->get();
        }

        data_filter($params);

        $params['project_id'] = PROJECT_ID;

        $this->load->model('Captcha_model');
        $result = $this->Captcha_model->image($params);

        if (false === $result) {
            json_exit($this->Captcha_model->get_error());
        } else {
            json_exit('ok', true, $result);
        }
    }

    // 发送短信验证码
    public function sms()
    {
        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $params['project_id'] = PROJECT_ID;
            //是否需要先验证图形码
            $params['is_check']   = $params['is_check'] == 1 ? true : false;

            $this->load->model('Captcha_model');
            $result = $this->Captcha_model->sms($params);

            if (false === $result) {
                json_exit($this->Captcha_model->get_error());
            } else {
                json_exit('发送成功！', true, $result);
            }
        }
    }
}