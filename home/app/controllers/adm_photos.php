<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-17
 * @category    adm_photos
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_photos extends CI_Controller
{
    /**
     * 文章列表
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access');

        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Photos_model');
        $result = $this->Photos_model->get_photos_list($params);

        // 获取相册列表
        $this->load->model('adm/Photos_album_model');
        $photos_album = $this->Photos_album_model->get_photos_album_list(['fields' => 'photos_album_id, album_name', 'is_enabled' => 1]);

        $this->load->library('pagination');
        $data = [
            'search'       => $params,
            'photos_album' => $photos_album['list'],
            'list'         => $result['list'],
            'pagination'   => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/photos/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 新增照片-(选择相册)
     *
     * @access    public
     *
     * @return    void
     */
    public function add_photos()
    {
        validate_priv('addnew');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $this->load->model('adm/Photos_model');
            $result = $this->Photos_model->add_photos($params);

            if (false === $result) {
                json_exit($this->Photos_model->get_error());
            } else {
                json_exit('添加成功', true, $result);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            // 获取相册列表
            $this->load->model('adm/Photos_album_model');
            $photos_album = $this->Photos_album_model->get_photos_album_list(['fields' => 'photos_album_id, album_name', 'is_enabled' => 1]);

            $data = [
                'search'       => $params,
                'photos_album' => $photos_album['list'],
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/photos/add_photos.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 编辑心情信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_photos()
    {
        validate_priv('edit');

        $this->load->model('adm/Photos_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Photos_model->edit_photos($params);
            if (false === $result) {
                json_exit($this->Photos_model->get_error());
            } else {
                json_exit('照片编辑成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            // 获取照片信息
            $result = $this->Photos_model->get_single_photos($params);

            // 获取相册列表
            $this->load->model('adm/Photos_album_model');
            $photos_album = $this->Photos_album_model->get_photos_album_list(['fields' => 'photos_album_id, album_name', 'is_enabled' => 1]);

            $data = [
                'search'       => $result,
                'photos_album' => $photos_album['list'],
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/photos/edit_photos.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 设置照片为相册封面
     *
     * @access  public
     *
     * @return  void
     */
    public function set_cover()
    {
        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Photos_model');
        $result = $this->Photos_model->set_cover($params);

        if(false == $result){
            json_exit($this->Photos_model->get_error());
        } else {
            json_exit('设置封面成功', true);
        }
    }

    /**
     * 设置有效性
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

        $this->load->model('adm/Photos_model');
        $result = $this->Photos_model->set_enabled($params);

        if(false == $result){
            json_exit($this->Photos_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}