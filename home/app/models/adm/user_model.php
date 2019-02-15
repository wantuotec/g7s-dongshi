<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: user_model.php
 * @create : Mar. 30, 2012
 * @update : Mar. 30, 2012
 */

class User_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /* 分页查询 */
    function browse($params, $offset, $size)
    {
        if (count($params) < 1) {
            $this->set_error('分页查询参数不能为空');
        }
        $this->load->model('adm/Search_model');

        return $this->Search_model->browse('User_dao', $params, $offset, $size, 0);
    }

    /* 获取所有用户 */
     function get_all_username()
    {
        $this->load->dao('User_dao');
      return  $this->User_dao->get_all_username();
    }

    /* 返回搜索选项 */
    function get_search_option()
    {
        return array(
                /* 状态 */
                'status' => array(
                    'allow' => '有效',
                    'deny'  => '作废',
                )
            );
    }

    /* 返回搜索默认值 */
    function get_search_defaults()
    {
        return array(
            'groupId'  => '',
            'user'     => '',
            'userName' => '',
            'status'   => '',
            );
    }

    /* 返回所有authGroup */
    function get_all_auth_groups()
    {
        $this->load->dao('Auth_groups_dao');
        return $this->Auth_groups_dao->get_all_auth_groups();
    }

    /* 新增用户 */
    function create_user($data)
    {
        if (!$this->_validate_insert($data)) {
            return false;
        }

        $this->load->dao('User_dao');
        if ($this->User_dao->insert_user($data)) {
            return true;
        } else {
            $this->set_error($this->User_dao->get_error());
            return false;
        }

    }

    /* 修改用户 */
    function modify_user($data, $id)
    {
        if (!isset($id) || $id < 1) {
            return false;
        }
        if (!$this->_validate_update($data)) {
            return false;
        }

        $this->load->dao('User_dao');

        if ($this->User_dao->update_user($data, $id)) {
            return true;
        } else {
            $this->set_error($this->User_dao->get_error());
            return false;
        }

    }

    /* 修改多个用户信息 */
    function modify_users($data, $ids)
    {
        if (!$this->_validate_update($data)) {
            return false;
        }
        if (count($ids) < 1) {
            $this->set_error('传入的id列表有误');
            return false;
        }

        $this->load->dao('User_dao');

        if ($this->User_dao->update_users($data, $ids)) {
            return true;
        } else {
            $this->set_error($this->User_dao->get_error());
            return false;
        }
    }

    /* 通过user_id获取用户信息 */
    function get_by_user_id($id)
    {
        $this->load->dao('User_dao');
        return $this->User_dao->get_by_user_id($id);
    }

    /* 通过groupId获取用户信息 */
    function get_by_group_id($id)
    {
        $this->load->dao('User_dao');
        return $this->User_dao->get_by_group_id($id);
    }

    /* 加密密码-后台 */
    function pass_encode($str)
    {
        $str .= 'm_!@#$%^&*DREAMMAXIAOMAADMIN_$^%$#@&^%&';
        return md5(md5($str));
    }


    /**
     * 根据传输数组的键值，绑定一个['user_count']
     * @param  array &$data 传入数组的key必须是group的id
     * @return bool
     */
    function warp_user_count_by_group(&$data)
    {
        if (count($data) < 1) {
            $this->_error = '传入的数组为空';
            return false;
        }
        $ids = array();
        $ids = array_keys($data);
        if (count($ids) < 1) {
            $this->set_error('必须以group_id作为键盘名');
            return false;
        }
        $this->load->dao('User_dao');
        $count_list = $this->User_dao->get_count_by_group_ids($ids);

        foreach ($data as $key => &$value) {
            if (isset($count_list[$key]))
                $value['user_count'] = $count_list[$key]['count'];
            else
                $value['user_count'] = 0;
        }

        unset($value);
        unset($data);
        return true;
    }

    /* 后台用户登录 */
    function loginin($user, $pass)
    {
        $this->load->dao('User_dao');
        $user = $this->User_dao->get_by_account($user, $this->pass_encode($pass));

        // 有用户数据
        if (count($user) > 0) {
            // 更新登录信息
            $data = [];
            $data['lastLoginIp'] = $this->input->ip_address();
            $data['lastLogin']   = date("Y-m-d H:i:s");
            $this->User_dao->update_user($data, $user['id']);

            // 保存登录信息到session中
            $_SESSION['admin']['user_id']    = $user['id'];
            $_SESSION['admin']['login_name'] = $user['user'];
            $_SESSION['admin']['userName']   = $user['userName'];
            $_SESSION['admin']['groupId']    = $user['groupId'];

            // 附加角色名
            $this->load->dao('Auth_groups_dao');
            $group = $this->Auth_groups_dao->get_by_role_id($user['groupId']);
            $_SESSION['admin']['groupName'] = isset($group['groupName']) ? $group['groupName'] : '';

            $this->load->model('adm/Auth_model');
            $_SESSION['admin']['privilege'] = $this->Auth_model->get_auths_by_group_id($user['groupId']);
            return true;
        } else {
            $this->set_error('您输入的用户名或者密码有误');
            return false;
        }
    }

    /* 用户登出 */
    function loginout($url)
    {
        if (!empty($_SESSION['admin'])) {
            unset($_SESSION['admin']);
        }

        header($url);
    }

    /* 获得用户自定义菜单 */
    function get_user_menu()
    {
        $data = array();
        $menu = $_SESSION['admin']['privilege'];
        if (count($menu) < 1) {
            return $data;
        }

        // 获取所有一级菜单
        $this->load->dao('Programs_dao');
        $programs = $this->Programs_dao->get_all_by_system_id(0);

        foreach ($menu as $item) {
            if (!isset($data[$item['sysGroupId']]))
                $data[$item['sysGroupId']] = $programs[$item['sysGroupId']];

            $data[$item['sysGroupId']]['nav'][] = $item;
        }
        unset($menu);

        return $data;
    }

    /* 更新认证 */
    protected function _validate_update(&$data)
    {
        $this->load->dao('Auth_groups_dao');
        if (isset($data['groupId']) && $this->Auth_groups_dao->exist_auth_id($data['groupId']) <
            1) {
            $this->set_error('指定的角色不存在！');
            return false;
        }
        return true;
    }

    /* 插入认证 */
    protected function _validate_insert(&$data)
    {
        $this->load->dao('User_dao');
        if (!isset($data['user']) || $this->User_dao->exist_user_by_name($data['user']) >0) {
            $this->set_error('用户帐号已经存在');
            return false;
        }

        $this->load->dao('Auth_groups_dao');
        if (!isset($data['groupId']) || $this->Auth_groups_dao->exist_auth_id($data['groupId']) <
            1) {
            $this->set_error('指定的角色不存在！');
            return false;
        }
        return true;
    }

    /**
     * 根据传输数组的键值，绑定一个['user_name']
     * @param  array &$data      传入数组
     * @param  array $source     user_id的键名
     * @return bool
     */
    function warp_user_name(&$data,$source = 'user_id')
    {
        $ids = array();
        $ids = array_keys($data,$source);
        foreach($data as $item)
        {
            if($item[$source])
                $ids[] = $item[$source];
        }
        unset($item);

        if (count($ids) > 0) {
            $this->load->dao('User_dao');
            $list = $this->User_dao->get_name_by_ids($ids);

            foreach ($data as &$item) {
                if (isset($list[$item[$source]]))
                    $item['user_name'] = $list[$item[$source]]['userName'];
                else
                    $item['user_name'] = '';
            }

            unset($item);
            unset($data);
            return true;
        }
        return false;
    }

    /**
     * 修改密码
     *
     * @param    string   $password       原密码
     * @param    string   $new_password   新密码
     *
     * @return   string|bool
     */
    function change_pass($password,$new_password)
    {
        if(empty($new_password)){
            $this->set_error('新密码不能为空');
            return false;
        }
        $this->load->model('adm/Auth_model');
        $user_id = $this->Auth_model->get_current_user_id();

        if($user_id<1){
            $this->set_error('必须登录后再继续操作');
            return false;
        }
        $user = $this->get_by_user_id($user_id);
        if(!isset($user['id'])){
            $this->set_error('无法找到用户');
            return false;
        }
        if($user['status'] !== 'allow'){
            $this->set_error('当前用户已经被注销');
            return false;
        }

        $password = $this->pass_encode($password);
        if ($user['pass'] != $password) {
            $this->set_error('原密码错误！');
            return false;
        }

        $new_password = $this->pass_encode($new_password);
        if(!empty($user['pass']) && $user['pass'] == $new_password){
            $this->set_error('原始密码和新密码一致');
            return false;
        }

        $data = array(
            'pass'=>$new_password,
        );
        $this->load->dao('User_dao');
        if(!$this->User_dao->update_user($data,$user['id'])){
            $this->set_error($this->User_dao->get_error());
            return false;
        }
        return true;
    }

    /**
     * 获取系统用户信息
     *
     * @return  array
     */
    public function get_system_user()
    {
        return $this->get_by_user_id('90');
    }

    /**
     * 检测密码是否是弱密码
     *
     * @access  public
     *
     * @param   string  $password   密码
     *
     * @return  bool
     */
    public function is_weak_password($password = null)
    {
        if (empty($password) || strlen($password) < 8 || 1 === preg_match('/^\d+$/', $password)) {
            return true;
        }

        return false;
    }

    /**
     * 生成用户随机密码
     *
     * @access  public
     *
     * @return  string
     */
    function gen_pass()
    {
        $length = 8;
        $char   = '0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f|g|h|i|j|k|l|m|n|o|p|q|r|s|t|u|v|w|x|y|z';
        $arr    = explode('|', $char);

        for ($i = 0; $i < $length; $i++) {
            $num = rand(0, 35);
            $str .= $arr[$num];
        }
        return $str;
    }

    /**
     * 修改密码信息
     *
     * @access  public
     *
     * @params  int     $id         人员ID
     * @params  array   $params     修改信息
     *
     * @return  boolen
     */
    function update_password($id, $params)
    {
        data_filter($params);

        if (empty($id)) {
            $this->set_error('无法找到用户');
            return false;
        }

        if (empty($params['password'])) {
            $this->set_error('新密码为空');
            return false;
        }

        if (empty($params['last_password'])) {
            $this->set_error('再次输入新密码为空');
            return false;
        }

        if ($params['password'] != $params['last_password']) {
            $this->set_error('两次密码输入不一致');
            return false;
        }

        if ($this->is_weak_password($params['password'])) {
            $this->set_error('您的密码过于简单，密码应该为8位以上的英文数字组合');
            return false;
        }

        $new_password = $this->pass_encode($params['password']);

        $data = array(
            'pass'=> $new_password,
        );

        $this->load->dao('User_dao');
        if (!$this->User_dao->update_user($data, $id)) {
            $this->set_error($this->User_dao->get_error());
            return false;
        }

        return true;
    }

    /**
     * 手机号是否存在
     *
     * @access  public
     *
     * @param   array   $params  短信登录参数
     *
     * @return  boolen
     */
    public function is_exist_phone_number($params = [])
    {
        $this->load->dao('User_dao');
        $user = $this->User_dao->get_account_by_phone($params['phone_number']);

        // 没有用户数据
        if (0 == count($user)) {
            return false;
        }

        if ($params['id'] == $user['id']) {
            return false;
        } else {
            return true;
        }
    }

}
