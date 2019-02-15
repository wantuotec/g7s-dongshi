<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-26
 * @category    adm_module_manage
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_module_manage extends CI_Controller
{
    /**
     * 功能模块列表
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access');

        $this->load->model('adm/Module_manage_model');
        $result = $this->Module_manage_model->get_module_list();

        $data = [
            'list' => $result['list'],
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/module_manage/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 设置模块是否开启
     *
     * @access  public
     *
     * @return  void
     */
    public function set_open()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Module_manage_model');
        $result = $this->Module_manage_model->edit_module($params);

        if(false == $result){
            json_exit($this->Slogan_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}