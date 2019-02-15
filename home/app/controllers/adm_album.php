<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-27
 * @category    adm_album
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_album extends CI_Controller
{
    /**
     * 相册列表
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access');

        $this->load->model('adm/Photos_album_model');
        $result = $this->Photos_album_model->get_photos_album_list();

        $data = [
            'list' => $result['list'],
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/photos_album/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 新增相册
     *
     * @access  public
     *
     * @return  void
     */
    public function add_album()
    {
        validate_priv('addnew');

        $this->load->model('adm/Photos_album_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Photos_album_model->add_album($params);
            if (false === $result) {
                json_exit($this->Photos_album_model->get_error());
            } else {
                json_exit('添加相册成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $data = [
                'search' => $params,
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/photos_album/add_album.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 编辑相册信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_album()
    {
        validate_priv('edit');

        $this->load->model('adm/Photos_album_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $result = $this->Photos_album_model->edit_album($params);
            if (false === $result) {
                json_exit($this->Photos_album_model->get_error());
            } else {
                json_exit('相册信息编辑成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $result = $this->Photos_album_model->get_single_album($params);
            $data = ['search' => $result];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/photos_album/edit_album.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 设置相册有效性
     *
     * @access  public
     *
     * @return  void
     */
    public function set_enabled()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Photos_album_model');
        $result = $this->Photos_album_model->edit_album($params);

        if(false == $result){
            json_exit($this->Photos_album_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}