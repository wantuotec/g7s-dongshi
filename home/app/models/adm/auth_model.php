<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: auth_model.php
 * @create : Apr. 5, 2012
 * @update : Apr. 5, 2012
 */

class Auth_model extends CI_Model
{
    protected $_error;

    function __construct()
    {
        parent::__construct();
    }

    /* 返回权限选项 */
    function get_priv_option()
    {
        return array(
            'access' => '执行',
            'addnew' => '新增',
            'edit'   => '修改',
            'del'    => '删除',
            'check'  => '审核',
            );

    }

    /* 根据角色返回权限 */
    function get_auths_by_group_id($id)
    {
        $this->load->dao('Sys_auths_dao');
        return $this->Sys_auths_dao->get_auths_by_group_id($id);
    }

    /* 返回指定角色所有资源列表 */
    function get_all_programs($id)
    {
        $this->load->dao('Programs_dao');
        $this->load->dao('Sys_auths_dao');

        $data = array();

        $programs = $this->Programs_dao->get_all();
        $privs = $this->Sys_auths_dao->get_auths_by_group_id($id);

        $merge = array_merge($programs, $privs);

        // 一级菜单
        foreach ($merge as $item) {
            if ($item['systemId'] == 0) {
                $data[$item['sysGroupId']] = $item;
            }
        }

        // 二级菜单
        foreach($merge as $val) {
            if($data[$val['sysGroupId']] && $val['systemId'] != 0) {
                $data[$val['sysGroupId']]['nav'][] = $val;
            }
        }
 
        unset($programs);
        unset($privs);
        return $data;
    }

    /**
     * 批量生成角色权限 
     * @param  array  $param 
     * array(
     *  'groupId' =>'1',
     *  '1-1' => array(
     *      0 =>'access',
     *      1 =>'addnew',
     *      2 =>'edit' ,
     *      3 =>'del',
     *      4 =>'check',
     *  )
     *  '2-1' => array(
     *      0 =>'access',
     *      1 =>'addnew')
     *  )
     * @return bool
     */
    function modify_sys_auths($param)
    {
        if (!isset($param['groupId']) && empty($param['groupId'])) {
            $this->_error = '角色不能为空';
            return false;
        }

        // 删除现有角色权限
        $id = $param['groupId'];
        unset($param['groupId']);
        $this->load->dao('Sys_auths_dao');
        $this->Sys_auths_dao->delete_by_group_id($id);
        $privs = $this->get_priv_option();

        // 生成新的权限
        $data = array();
        $i = 0;
        foreach ($param as $key => $selecteds) {
            $arr = explode('-', $key);
            $data[$i]['groupId'] = $id;
            $data[$i]['systemId'] = $arr[0];
            $data[$i]['sysGroupId'] = $arr[1];

            // 填充默认值
            foreach ($privs as $priv_key => $priv) {
                $data[$i][$priv_key] = 'deny';
            }
            // 覆盖提交的权限
            foreach ($selecteds as $selected) {
                $data[$i][$selected] = 'allow';
            }
            $i++;
        }
        // 插入权限
        if ($this->Sys_auths_dao->insert_auths($data)) {
            return true;
        } else {
            $this->_error = $this->Sys_auths_dao->get_error();
            return false;
        }
    }

    /* 返回错误 */
    function get_error()
    {
        return $this->_error;
    }
    
    /* 返回当前用户id */
    function get_current_user_id()
    {
        return $_SESSION['admin']['user_id'] ? $_SESSION['admin']['user_id'] : 0;
    }
        
    /* 返回当前用户名称 */
    function get_current_user_name()
    {
        return $_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'Anonymous';
    }
    
}
