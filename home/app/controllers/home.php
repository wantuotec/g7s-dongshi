<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-01-22
 * @category    Home.php
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Home extends CI_Controller
{
    /**
     * 网站首页
    */
    public function index()
    {
        // 获取首页数据
        $this->load->model('Home_model');
        $result = $this->Home_model->get_home_data();

        $result['user_info'] = [];

        $data = [
            'list'  => $result,
        ];

        $this->load->view('base/header.tpl', ['current_title' => 'home']);
        $this->load->view('home/index.tpl', $data);
        $this->load->view('base/footer.tpl');
    }
}