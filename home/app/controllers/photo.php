<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-06
 * @category    Photo.php
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Photo extends CI_Controller
{
    /*
    * 相册列表
    */
    public function index()
    {
        $params = $this->input->get();
        data_filter($params);

        $params['is_enabled'] = 1;
        $this->load->model('Photos_model');
        $result = $this->Photos_model->get_album_list($params);

        $data = [
            'current_title' => 'photo',
            'album_list'    => $result['list'],
        ];

        $this->load->view('base/header.tpl', ['current_title' => 'photo']);
        $this->load->view('photo/index.tpl', $data);
        $this->load->view('base/footer.tpl');
    }

    /*
    * 照片列表
    */
    public function photo_list()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('Photos_model');
        // 相册列表
        $album = $this->Photos_model->get_album_list(['fields' => 'photos_album_id, album_name', 'is_enabled' => 1]);

        // 照片列表
        $result = $this->Photos_model->get_photos_list($params);

        $this->load->library('home_pagination');
        $data = [
            'search'      => $params,
            'album_list'  => $album['list'],
            'photos_list' => $result['list'],
            'pagination'  => $this->home_pagination->get_page_bar($result['total']),
        ];

        $this->load->view('base/header.tpl', ['current_title' => 'photo']);
        $this->load->view('photo/photo_list.tpl', $data);
        $this->load->view('base/footer.tpl');
    }
}