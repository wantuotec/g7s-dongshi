<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 菜单管理
 *
 * @author      liunian
 * @date        2013-04-16
 * @category    programs
 * @copyright   Copyright(c) 2012
 * @version     $Id: programs.php 778 2013-10-08 07:53:16Z 熊飞龙 $
 */
class Adm_programs extends CI_Controller
{
    /**
     * 菜单管理首页
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        $data = array();
        $this->load->model('adm/Programs_model');
        // 获取所有的菜单信息
        $data = array('list'=> $this->Programs_model->get_list());

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/programs/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 菜单信息的修改
     *
     * @access   public
     *
     * @return   void
     */
     public function edit_program()
     {
        validate_priv('edit');
        $this->load->helper('common');
        $id = intval($this->input->get('id')); // 菜单ID
        if ($id < 1) {
            show_error('非法的参数');
        }

        $data = array();
        // 返回地址
        $data['return_path'] =  HOME_DOMAIN . 'programs/';
        $this->load->model('adm/Programs_model');
        if ($this->input->post('act') == 'edit') {
            $data['filter'] = $this->input->post();
            unset($data['filter']['id']);
            unset($data['filter']['act']);

            if ($this->Programs_model->update_program_by_id($id, $data['filter'])) {
                show_msg('菜单信息修改成功', HOME_DOMAIN . "programs/index");
            } else {
                show_error($this->Programs_model->get_error());
            }
        }
        // 获取本条菜单id的数据信息
        $data['filter']   = $this->Programs_model->get_program_by_id($id);
        // 获取状态信息
        $all_status       = $this->Programs_model->get_all_status();
        $data['displays'] = $all_status['displays'];
        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/programs/edit.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
     }

     /**
      * 设置菜单是否显示跟隐藏
      *
      * @access  public
      *
      * @return  void
      */
    public function update_display()
    {
        validate_priv('edit');

        $id = intval($this->input->post('id')); //菜单ID
        if ($id < 1) {
            show_error('非法的参数');
        }
        $this->load->model('adm/Programs_model');
        $id         = intval($this->input->post('id'));
        $is_display = intval($this->input->post('is_display'));
        if ($id && $is_display >= 0) {
            $msg = $this->Programs_model->get_display_msg_by_id($id, $is_display);
            if(true === $msg) {
                die('ok');
            } else {
                die('error');
            }
        } else {
            die('param_error');
        }
    }

    /**
     * 添加二级菜单信息
     *
     * @access      public
     * @return      void
     */
    public function add_second_program()
    {
        validate_priv('addnew');
        $data       = array();
        $id         = intval($this->input->get('id'));
        $systemId   = intval($this->input->get('systemId'));
        $sysGroupId = intval($this->input->get('sysGroupId'));
        $params     = $this->input->get();
        $this->load->model('adm/Programs_model');
        if ($this->input->post('act') == 'add') {
            $data['programs'] = $this->input->post();
            unset($data['programs']['act']);
            if ($this->Programs_model->add_second_programs($data['programs'])) {
                show_msg('添加二级菜单成功', HOME_DOMAIN . "programs/index");
            } else {
                show_error($this->Programs_model->get_error());
            }
        }

        $data['programs']   = $this->Programs_model->get_program_by_id($id);
        // 获取状态信息
        $all_status         = $this->Programs_model->get_all_status();
        $data['displays']   = $all_status['displays'];
        $data['id']         = $id;
        $data['systemId']   = $systemId;
        $data['sysGroupId'] = $sysGroupId;

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/programs/add_second.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }
    /**
     * 添加一级菜单
     *
     * @access      public
     *
     * @return      void
     */
     public function add_first_programs()
     {
        validate_priv('addnew');
        $data               = array();
        $this->load->model('adm/Programs_model');
        if ($this->input->post('act') == 'add') {
            $data['programs'] = $this->input->post();
            unset($data['programs']['act']);
            if ($this->Programs_model->add_first_programs($data['programs'])) {
                show_msg('添加一级菜单成功', HOME_DOMAIN . "programs/index");
            } else {
                show_error($this->Programs_model->get_error());
            }
        }
        //获取状态信息
        $all_status         = $this->Programs_model->get_all_status();
        $data['displays']   = $all_status['displays'];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/programs/add_first.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
     }

     /**
      * 保存数据
      */
     public function do_save()
     {
         // validate_priv('edit');
         $menu = $this->input->post('menu');

         if (!is_array($menu)) {
             json_exit('服务器未收到有效的数据！', false);
         }

         $this->load->model('adm/Programs_model');
         $result = $this->Programs_model->do_save($menu);

         if($result === false) {
             json_exit($this->Adm_Programs_model->get_error(), false);
         } else {
             json_exit('操作成功！', true);
         }
     }
}