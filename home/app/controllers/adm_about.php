<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-26
 * @category    adm_about
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_about extends CI_Controller
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

        $this->load->model('adm/About_model');
        $result = $this->About_model->get_website_info();

        $data = [
            'list' => $result,
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/about/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 编辑网站信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_website_info()
    {
        validate_priv('edit');

        $this->load->model('adm/About_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->About_model->edit_website_info($params);

            if (false === $result) {
                json_exit($this->About_model->get_error());
            } else {
                json_exit('网站信息编辑成功', true);
            }
        } else {
            $result = $this->About_model->get_website_info();
            $data = ['search' => $result];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/about/edit.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }
}