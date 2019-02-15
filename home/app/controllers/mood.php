<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 心情文字管理
 *
 * @author      madesheng
 * @date        2017-01-22
 * @category    Mood.php
 * @copyright   Copyright(c) 2017
 *
 * @version     $Id:$
 */
class Mood extends CI_Controller
{
    /**
     * 获取心情文字列表
    */
    public function index()
    {
        // 获取网站顶部slogan
        $this->load->model('Slogan_model');
        $slogan_result = $this->Slogan_model->get_single_slogan(['item_type' => 5]);

        // 获取心情列表
        $this->load->model('Mood_model');
        $mood_result = $this->Mood_model->get_mood_list();

        $this->load->library('home_pagination');

        $data = [
            'slogan'      => $slogan_result['content'],
            'moods'       => $mood_result['list'],
            'pagination'  => $this->home_pagination->get_page_bar($mood_result['total']),
        ];

        // 开启页面缓存（10分钟）
        // $this->output->cache(10);

        $this->load->view('base/header.tpl', ['current_title' => 'mood']);
        $this->load->view('mood/index.tpl', $data);
        $this->load->view('base/footer.tpl');
    }
}