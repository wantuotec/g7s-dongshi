<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 网站信息管理 
 *
 * @author      madesheng
 * @date        2017-01-22
 * @category    Home.php
 * @copyright   Copyright(c) 2017
 *
 * @version     $Id:$
 */
class About extends CI_Controller
{
    /**
     * 获取网站基本信息
    */
    public function index()
    {
        // 获取网站顶部slogan
        $this->load->model('Slogan_model');
        $slogan_result = $this->Slogan_model->get_single_slogan(['item_type' => 2]);

        // 获取网站基本信息
        $this->load->model('About_model');
        $website_result = $this->About_model->get_website_info();

        $data = [
            'slogan'  => $slogan_result,
            'website' => $website_result,
        ];

        // 开启页面缓存（120分钟）
        // $this->output->cache(120);

        $this->load->view('base/header.tpl', ['current_title' => 'about']);
        $this->load->view('about/index.tpl', $data);
        $this->load->view('base/footer.tpl');
    }
}