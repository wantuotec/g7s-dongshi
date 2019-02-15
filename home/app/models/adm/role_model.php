<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: role.php
 * @create : Apr. 30, 2012
 * @update : Apr. 30, 2012
 */

class Role_model extends CI_Model
{
    protected $_error;

    function __construct()
    {
        parent::__construct();
    }

    /* 返回搜索选项 */
    function get_search_option()
    {
        return array('status' => array(
                'allow' => '有效',
                'deny' => '作废',
                ));
        ;
    }

    /* 返回搜索默认值 */
    public function get_search_defaults()
    {
        return array(
            'id' => '',
            'groupName' => '',
            'status' => '',
            );
    }

    /* 分页查询 */
    function browse($params, $offset, $size)
    {
        if (count($params) < 1) {
            $this->_error = '分页查询参数不能为空';
        }
        $this->load->model('adm/Search_model');

        return $this->Search_model->browse('Auth_groups_dao', $params, $offset, $size, 0);
    }

    /* 新增角色 */
    function create_role($data)
    {
        $this->load->dao('Auth_groups_dao');

        if ($this->Auth_groups_dao->insert_role($data)) {
            return true;
        } else {
            $this->_error = $this->Auth_groups_dao->get_error();
            return false;
        }

    }

    /* 修改角色 */
    function modify_role($data, $id)
    {
        if (!isset($id) || $id < 1) {
            $this->_error = '修改角色失败，缺少必要参数';
            return false;
        }

        $this->load->dao('Auth_groups_dao');

        if ($this->Auth_groups_dao->update_role($data, $id)) {
            return true;
        } else {
            $this->_error = $this->Auth_groups_dao->get_error();
            return false;
        }

    }

    /* get_all_auth */
    function get_all_auth_groups()
    {
        $this->load->dao('Auth_groups_dao');
        return $this->Auth_groups_dao->get_all_auth_groups();
    }

    /* get_by_id */
    function get_by_role_id($id)
    {
        $this->load->dao('Auth_groups_dao');
        return $this->Auth_groups_dao->get_by_role_id($id);
    }


    public function update(array $params)
    {
        data_filter($params);

        $set = array(
            'status'         => !isset($params['status'])    ? null : $params['status'],
            'groupName'      => !isset($params['groupName']) ? null : $params['groupName'],
            'udateTime'      => !isset($params['udateTime']) ? null : $params['udateTime'],
            'bind_warehouse' => !isset($params['bind_warehouse']) ? null : $params['bind_warehouse'],
        );

        $where = array(
            'id' => empty($params['id']) ? null : $params['id'],
        );

        invalid_data_filter($set, array(null));
        invalid_data_filter($where);

        if (empty($where) || empty($set)) {
            $this->set_error('没有需要更新的数据！');
            return false;
        }

        $this->load->dao('Auth_groups_dao');
        return $this->Auth_groups_dao->update($set, $where, 1);
    }


    /**
     * 编辑绑定的关联仓库
     *
     * @access  public
     *
     * @param   array    $params
     *
     * @return  array
     */
    public function edit_bind_warehouse(array $params)
    {
        data_filter($params);

        if (!intval($params['id'])) {
            $this->set_error('缺少ID号！');
            return false;
        }

        $role = $this->get_by_role_id($params['id']);
        if (empty($role)) {
            $this->set_error('角色组不存在！');
            return false;
        }

        $warehouse_where = array(
            'fields' => 'warehouse_id,warehouse_name,code,contact',
        );
        $this->load->model('adm/Warehouse_model');
        $warehouse = $this->Warehouse_model->get_warehouse_list($warehouse_where);

        return array(
            'info'           => $role,
            'list'           => $warehouse['list'],
            'bind_warehouse' => explode(',', $role['bind_warehouse']),
        );
    }


    /**
     * 更新绑定仓库
     *
     * @access  public
     *
     * @param   array    $params
     *
     * @return  array
     */
    public function do_bind_warehouse(array $params)
    {
        data_filter($params);

        if (!intval($params['id'])) {
            $this->set_error('缺少ID!');
            return false;
        }

        $role = $this->get_by_role_id($params['id']);
        if (empty($role)) {
            $this->set_error('指定的ID不存在！');
            return false;
        }

        // 检查所选的仓库 ID 是否都存在
        if (!empty($params['warehouse_id'])) {

            $warehouse_ids = array_unique(explode(',', $params['warehouse_id']));

            $warehouse_where = array(
                'fields'    => 'warehouse_id',
                'where_in'  => array(
                    'warehouse_id' => $warehouse_ids,
                ),
            );
            $this->load->model('adm/Warehouse_model');
            $warehouse = $this->Warehouse_model->get_warehouse_list($warehouse_where);
            if ($warehouse['total'] != count($warehouse_ids)) {
                $this->set_error('选择的仓库中存在异常！');
                return false;
            }

            $params['warehouse_id'] = implode(',', $warehouse_ids);
        }

        if ($role['bind_warehouse'] == $params['warehouse_id']) {
            $this->set_error('数据未改变！');
            return false;
        }

        // 更新数据
        $update = array(
            'id' => intval($params['id']),
            'bind_warehouse' => empty($params['warehouse_id']) ? '' : $params['warehouse_id'],
        );
        return $this->update($update);
    }
}