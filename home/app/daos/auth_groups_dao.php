<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: Auth_group_dao.php
 * @create : Mar. 31, 2012
 * @update : Mar. 31, 2012
 */
class Auth_groups_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    // 数据表定义
    protected $_table    = 'groups';

    // 字段定义
    protected $_fields   = '`id`, `groupName`, `status`, `createTime`, `udateTime`';

    protected $_params;
    protected $_error;

    function __construct()
    {
        parent::__construct();
    }

    /* 返回所有group */
    function get_all_auth_groups()
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT id,groupName FROM `groups`");
        return $query->result_array('id');
    }

    /* 返回错误 */
    function get_error()
    {
        return $this->_error;
    }

    /* 是否存在id，存在返回count */
    function exist_auth_id($id)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT COUNT(*) AS count FROM `groups` WHERE `id` = ?",
            array($id));
        return $query->row()->count;
    }

    /* 分页查询 */
    function browse($params, $offset, $size)
    {
        $this->load->rwdb($this->_db_read);
        $this->_params = $params;

        $sql = 'SELECT `id`,`groupName`,`status` FROM groups' . $this->_where();

        if (!empty($size)) {
            $sql .= " LIMIT $size";
            if (!empty($offset))
                $sql .= " OFFSET $offset";
        }

        $query = $this->rwdb->query($sql);
        return $query->result_array('id');
    }

    /* 统计总数 */
    function count()
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query('SELECT COUNT(*) AS count FROM groups' . $this->
            _where());
        $counts = $query->row_array();
        return $counts['count'];
    }

    /* 插入角色 */
    function insert_role($data)
    {
        $this->load->rwdb($this->_db_write);
        $data['createTime'] = date("Y-m-d H:i:s");
        if ($this->rwdb->insert('groups', $data)) {
            return true;
        } else {
            $this->_error = '插入角色失败';
            return false;
        }
    }

    /* 修改角色 */
    function update_role($data,$id)
    {
        $this->load->rwdb($this->_db_write);
        if($this->rwdb->update('groups',$data,array('id'=>$id))){
            return true;
        }else{
            $this->_error = '修改角色 失败';
            return false;
        }
    }

    /* by role_id*/
    function get_by_role_id($id)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query('SELECT `id`,`groupName`,`status` FROM `groups` WHERE `id` = ?', array($id));
        return $query->row_array();
    }

    /* 拼接条件语句 */
    protected function _where()
    {
        $sql = ' WHERE 1';

        if (isset($this->_params['id']) && $this->_params['id'] > 0) {
            $sql .= " AND id =" . $this->_params['id'];
        }

        if (isset($this->_params['groupName']) && !empty($this->_params['groupName'])) {
            $sql .= " AND groupName like '%" . $this->_params['groupName'] . "%'";
        }

        if (isset($this->_params['status']) && !empty($this->_params['status'])) {
            $sql .= " AND status = '" . $this->_params['status']."'";
        }
        return $sql;
    }
}
