<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * APP 文件上传 入口
 * 
 *
 * @author       杨海波
 * @date         2016-12-20
 * @category     Rest
 * @copyright    Copyright(c) 2016
 * @version      $Id$
 */

class Upload extends CI_Controller
{
    /**
     * 接口默认入口地址
     */
    public function index()
    {
        url_404();
    }

    /**
     * 上传图片
     */
    public function image()
    {
        // 必须是 POST 方式
        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            //dump($params, $_FILES);exit('------------');

            $this->load->model('Upload_model');
            $request = $this->Upload_model->upload_image_by_browser($this->input->post());
            dump($request);
        } else {


            $this->load->view('test/add.tpl', $data);
        }
    }
}