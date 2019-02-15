<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: user.php
 * @purpose: 用户管理
 * @create : Mar. 31, 2012
 * @update : Mar. 31, 2012
 */

class Adm_user extends CI_Controller
{
    /* 首页 */
    function index()
    {
        validate_priv('access');
        
        $data = array();

        $this->load->library('pagination');
        $this->load->model('adm/User_model');

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 20;

        $data['filter'] = $this->input->get();

        if (empty($data['filter']) || count($data['filter']) < 1) {
            $data['filter'] = $this->User_model->get_search_defaults();
        }

        $data['search_option'] = $this->User_model->get_search_option();

        $this->load->dao('Auth_groups_dao');
        $data['group'] = $this->Auth_groups_dao->get_all_auth_groups();

        list($data['list'], $data['count']) = $this->User_model->browse($data['filter'],
            $offset, $size);

        $data['pageination'] = $this->pagination->getPageBar($data['count'], $size);

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/user/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 新增用户 */
    function addUser()
    {
        validate_priv('addnew');
        
        $this->load->model('adm/User_model');

        $this->load->library('email');
        if ($this->input->post('act') == 'add') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user', '用户帐号', 'required');
            $this->form_validation->set_rules('userName', '用户姓名', 'required');
            $this->form_validation->set_rules('pass', '用户密码', 'required');
            $this->form_validation->set_rules('status', '状态', 'required');

            $data = $this->input->post();
            unset($data['act']);

            // 密码加密
            $data['pass'] = $this->User_model->pass_encode($data['pass']);

            if ($this->form_validation->run() == false)
                show_error(validation_errors());
            if ($this->User_model->create_user($data)) {
                show_msg('新增用户成功', HOME_DOMAIN . 'adm_user/');
            } else {
                show_error($this->User_model->get_error(), 'javascript:history.back();');
            }

        }
        $data = array();

        $data['filter'] = $this->User_model->get_search_defaults();

        $data['search_option'] = $this->User_model->get_search_option();

        $data['search_option']['auth'] = $this->User_model->get_all_auth_groups();

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/user/add_user.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 编辑用户 */
    function editUser()
    {
        validate_priv('edit');

        $user_id = $this->input->is_post_request() ? intval($this->input->post('id')) : intval($this->input->get('id'));
        if ($user_id < 1) {
            show_error('非法的参数');
        }

        $data = array();

        $this->load->model('adm/User_model');

        if ($this->input->post('act') == 'edit') {

            $data['filter'] = $this->input->post();

            unset($data['filter']['id']);
            unset($data['filter']['act']);
            unset($data['filter']['province_code']);
            unset($data['filter']['city_code']);
            unset($data['filter']['district_code']);

            $this->load->library('form_validation');
            $this->form_validation->set_rules('id', '用户', 'is_natural_no_zero|required');
            $this->form_validation->set_rules('userName', '用户姓名', 'required');
            $this->form_validation->set_rules('status', '状态', 'required');
            $this->form_validation->set_rules('email', 'Email', 'valid_email|required');

            if ($this->form_validation->run() == false)
                show_error(validation_errors(), "javascript:history.back();");

            // 校验一下手机号
            $this->load->helper('validate');
            if (!empty($data['filter']['phone_number']) && !is_mobile($data['filter']['phone_number'])) {
                show_msg('请输入正确的手机号', "javascript:history.back();");
            }

            // 看手机号是不是存在
            if (!empty($data['filter']['phone_number']) && is_mobile($data['filter']['phone_number'])) {
                $is_exist = $this->User_model->is_exist_phone_number([
                    'phone_number' => $data['filter']['phone_number'],
                    'id'           => $user_id,
                ]);
                if ($is_exist) {
                    show_msg('手机号不能重复', "javascript:history.back();");
                }
            }

            if ($this->User_model->modify_user($data['filter'], $user_id)) {
                show_msg('用户修改已成功', "javascript:history.back();");
            } else {
                show_error($this->User_model->get_error(), 'javascript:history.back();');
            }
        }

        $data['filter'] = $this->User_model->get_by_user_id($user_id);

        $data['search_option'] = $this->User_model->get_search_option();

        $data['search_option']['auth'] = $this->User_model->get_all_auth_groups();

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/user/edit_user.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 修改密码 */
    function editPass()
    {
        validate_priv('edit');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $this->load->model('adm/User_model');
            $result = $this->User_model->update_password($params['id'], $params);

            if (false === $result) {
                json_exit($this->User_model->get_error());
            } else {
                json_exit('修改密码成功', true);
            }
        } else {
            $params = $this->input->get();

            $this->load->model('adm/User_model');
            $data['filter'] = $this->User_model->get_by_user_id($params['id']);

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/settings/user/edit_pass.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /* 查看权限 */
    function priv()
    {
        validate_priv('access');
        
        $id = intval($this->input->get('id'));
        if ($id < 1) {
            show_error('非法参数', "javascript:history.back();");
        }
        
        $this->load->model('adm/Auth_model');
        $this->load->model('adm/User_model');
        $user = $this->User_model->get_by_user_id($id);

        $data = array();
        $data['groupId'] = $id;
        $data['list'] = $this->Auth_model->get_all_programs($user['groupId']);
        $data['priv_option'] = $this->Auth_model->get_priv_option();
        
        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/user/priv.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }
    
    /* 修改用户密码 */
    function ajaxResetPassword()
    {
        validate_priv('edit');

        $output['status'] = 0;
        $id = intval($this->input->get('id'));
        if ($id > 0) {
            $this->load->model('adm/User_model');

            // 生成初始密码
            $pass = $this->User_model->gen_pass();
            $encode_pass = $this->User_model->pass_encode($pass);
            if ($this->User_model->modify_user(array('pass' => $encode_pass), $id)) {
                $data = $this->User_model->get_by_user_id($id);
                $msg = sprintf('新爱的%s：<br/>您的用户名为：%s<br />您的新密码为：%s<br />请尽快登录 <a href="%s" target="_blank">%s</a> 并修改您的密码。<br/>', $data['userName'], $data['user'], $pass, HOME_DOMAIN, HOME_DOMAIN);
                $this->load->library('mail');
                if($this->mail->send($data['email'], '密码重置邮件', $msg)){
                    $output['status'] = 1;
                }
            }
        }
        echo json_encode($output);
        exit();
    }

    /**
     * 按 group_id 获取功能列表
     *
     * @access public
     *
     * @return void
     */
    public function get_programs_by_name()
    {
        validate_priv('access');
        $this->load->helper('common');

        $name = $this->input->get('name');
        data_filter($name);
        if (!empty($name)) {
            $this->load->dao('Programs_dao');
            $program = $this->Programs_dao->get_program_by_name($name);
            is_array($program) && $data = $this->Programs_dao->get_programs_by_group_id($program['sysGroupId']);
            (empty($data) || !is_array($data)) && $data = array();
            json_exit('成功', true, $data);
        } else {
            json_exit('系统参数缺失');
        }
    }

    /**
     * 字符串转数组
     *
     * @access public
     *
     * @return void
     */
    public function string_to_array($string = null)
    {
        if(!empty($string)){
            $branch_id = trim($string, ",");
            $branch_id = explode(",", $branch_id);
            return $branch_id;
        }
    }
}