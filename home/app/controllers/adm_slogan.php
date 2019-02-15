<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-26
 * @category    adm_slogan
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_slogan extends CI_Controller
{
    /**
     * 网站信息展示
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access');

        $this->load->model('adm/Slogan_model');
        $result = $this->Slogan_model->get_slogan_list();

        $data = [
            'list' => $result['list'],
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/slogan/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 编辑网站信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_slogan()
    {
        validate_priv('edit');

        $this->load->model('adm/Slogan_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $result = $this->Slogan_model->edit_slogan($params);
            if (false === $result) {
                json_exit($this->Slogan_model->get_error());
            } else {
                json_exit('网站信息编辑成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $result = $this->Slogan_model->get_single_slogan($params);
            $data = ['search' => $result];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/slogan/edit_slogan.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 设置slogan有效性
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

        $this->load->model('adm/Slogan_model');
        $result = $this->Slogan_model->edit_slogan($params);

        if(false == $result){
            json_exit($this->Slogan_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}