<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 用户管理
 *
 * @author      madesheng
 * @date        2017-01-22
 * @category    Member.php
 * @copyright   Copyright(c) 2017
 *
 * @version     $Id:$
 */
class Member extends CI_Controller
{
    /**
     * 用户登录
    */
    public function auth()
    {
        $member_info = get_user_info();
        // 已登录
        if (!empty($member_info)) {
            die('跳去个人中心页面');
        // 未登录，转去登录页
        } else {
            $this->login();
        }
    }

    /**
     * 用户登录
    */
    public function login()
    {
        $this->load->model('Member_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Member_model->add_notice($params);
            if (false === $result) {
                json_exit($this->Member_model->get_error());
            } else {
                json_exit('好哒，这就成为空间一员了哦！~(^_^)∠※', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $data = ['search' => $params];

            $this->load->view('member/login.tpl');
        }
    }
}