<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: role.php
 * @create : Apr. 1, 2012
 * @update : Apr. 1, 2012
 */
class Adm_role extends CI_Controller
{
    /* 首页 */
    function index()
    {
        validate_priv('access');

        $data = array();

        $this->load->library('pagination');
        $this->load->model('adm/User_model');
        $this->load->model('adm/Role_model');

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $size = isset($_GET['size']) ? intval($_GET['size']) : 20;

        $data['filter'] = $this->input->get();
        if (empty($data['filter']) || count($data['filter']) < 1) {
            $data['filter'] = $this->Role_model->get_search_defaults();
        }

        $data['search_option'] = $this->Role_model->get_search_option();

        list($data['list'], $data['count']) = $this->Role_model->browse($data['filter'],
            $offset, $size);

        $this->User_model->warp_user_count_by_group($data['list']);

        $data['pageination'] = $this->pagination->getPageBar($data['count'], $size);

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/role/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 新增角色 */
    function addRole()
    {
        validate_priv('addnew');

        $data = array();

        $this->load->model('adm/Role_model');

        // 未指定act，显示页面
        if ($this->input->post('act') == 'add') {
            $data['filter'] = $this->input->post();
            unset($data['filter']['act']);

            $this->load->library('form_validation');
            $this->form_validation->set_rules('groupName', '角色名称', 'required');
            $this->form_validation->set_rules('status', '状态', 'required');
            if ($this->form_validation->run() == false)
                show_error(validation_errors());

            if ($this->Role_model->create_role($data['filter'])) {
                show_msg('新建角色成功', HOME_DOMAIN . 'adm_role/');
            } else {
                show_error($this->get_error());
            }
        }

        $data['search_option'] = $this->Role_model->get_search_option();

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/role/add_role.tpl', $data);
        $this->load->view('adm/base/foot.tpl');

    }

    /* 编辑角色 */
    function editRole()
    {
        validate_priv('edit');

        $this->load->model('adm/Role_model');

        // 未指定act，显示页面
        if ($this->input->post('act') == 'edit') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('id', '角色id', 'is_natural_no_zero|required');
            $this->form_validation->set_rules('groupName', '角色名称', 'required');
            $this->form_validation->set_rules('status', '角色状态', 'required');
            if ($this->form_validation->run() == false)
                show_error(validation_errors());

            $id = $this->input->post('id');
            $filter = array();
            $filter['groupName'] = $this->input->post('groupName');
            $filter['status'] = $this->input->post('status');

            if ($this->Role_model->modify_role($filter, $id)) {
                show_msg('编辑角色成功', HOME_DOMAIN . 'adm_role/');
            } else {
                show_error($this->get_error());
            }
        }

        $id = intval($this->input->get('id'));
        if ($id < 1) {
            show_error('非法参数');
        }
        $data = array();

        $data['search_option'] = $this->Role_model->get_search_option();
        $data['filter'] = $this->Role_model->get_by_role_id($id);

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/role/edit_role.tpl', $data);
        $this->load->view('adm/base/foot.tpl');

    }

    /* 成员设置 */
    function manageUser()
    {
        validate_priv('edit');

        $id = intval($this->input->get('id'));
        if ($id < 1) {
            show_error('非法参数');
        }
        $this->load->model('adm/User_model');
        $this->load->model('adm/Role_model');
        $data = array();
        $data['list'] = $this->User_model->get_by_group_id($id);
        $data['auth_groups'] = $this->Role_model->get_all_auth_groups();

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/role/manage_user.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 权限设置 */
    function priv()
    {
        validate_priv('edit');

        $id = intval($this->input->get('id'));
        if ($id < 1) {
            show_error('非法参数');
        }

        $this->load->model('adm/Auth_model');
        $data = array();
        $data['groupId'] = $id;
        $data['list'] = $this->Auth_model->get_all_programs($id);
        $data['priv_option'] = $this->Auth_model->get_priv_option();

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/settings/role/priv.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    function priv_act()
    {
        validate_priv('edit');
        $param = $this->input->post();
        $this->load->model('adm/Auth_model');
        if($this->Auth_model->modify_sys_auths($param)){
            show_msg('编辑权限已成功', HOME_DOMAIN . 'adm_role');
        }else{
            show_error($this->Auth_model->get_error());
        }
    }

    /* 成员移动 */
    function moveUser()
    {
        validate_priv('edit');

        $this->load->library('form_validation');
        $this->load->model('adm/User_model');

        $this->form_validation->set_rules('ids', '用户id', 'required');
        $this->form_validation->set_rules('groupId', '用户角色', 'required');
        if ($this->form_validation->run() == false)
            show_error(validation_errors());

        $ids = $this->input->post('ids');

        $filter = array();
        $filter['groupId'] = $this->input->post('groupId');

        if ($this->User_model->modify_users($filter, $ids)) {
            show_msg('成员移动成功', HOME_DOMAIN . 'adm_role/');
        } else {
            show_error($this->get_error());
        }
    }
}