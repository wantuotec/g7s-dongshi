<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: user_dao.php
 * @create : Mar. 31, 2012
 * @update : Mar. 31, 2012
 */

class User_dao extends CI_Dao
{
    private   $_db_write = array('cluster' => 1, 'mode' => 'write');
    private   $_db_read  = array('cluster' => 1, 'mode' => 'read');
    protected $_params;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * [get 查询方法]
     * @param  [type]   $where [CI where条件]
     * @param  [string] $mode  [获得记录多行/单行]
     * @return [array] 
     */
    public function get( $where,$mode = 'row' )
    {
        $this->load->rwdb($this->_db_read);
        $this->rwdb
             ->select(" `id`,`groupId`,`user`,`userName`,`status`,`pass`")
             ->from('user')
             ->where($where);

        if( $mode === 'row' )
            return $this->rwdb->get()->row_array();

        return $this->rwdb->get()->result_array();
    }

    /* 分页查询 */
    function browse($params, $offset, $size)
    {
        $this->load->rwdb($this->_db_read);
        $this->_params = $params;

        $sql = "SELECT `id`,`groupId`,`user`,`userName`,`status` FROM `user`" .
            $this->_where();

        if (!empty($size)) {
            $sql .= " LIMIT $size";
            if (!empty($offset))
                $sql .= " OFFSET $offset";
        }
        
        $query = $this->rwdb->query($sql);
        return $query->result_array();
    }

    /* 获取所有用户 */
    function get_all_username()
    {
        $sql = "select id,user,userName from user ";
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query($sql);
        return $query->result_array();
    } 

    /* 统计用户总数 */
    function count()
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT COUNT(*) AS `count` FROM `user`" . $this->
            _where());
        $counts = $query->row_array();
        return $counts['count'];
    }


    /* 是否存在user_id，存在返回count */
    function exist_user_id($user_id)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT COUNT(*) AS `count` FROM `user` WHERE `id` = ?",
            array($user_id));
        return $query->row()->count;
    }
    
    /* 是否存在user_name，存在返回count */
    function exist_user_by_name($user_name)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT COUNT(*) AS `count` FROM `user` WHERE `user` = ?",
            array($user_name));
        return $query->row()->count;
    }
    
    /* 插入用户 */
    function insert_user($data)
    {
        $this->load->rwdb($this->_db_write);
        $data['createTime'] = date("Y-m-d H:i:s");
        if($this->rwdb->insert('user',$data)){
            return true;
        }else{
            $this->_error = '插入用户失败';
            return false;
        }
    }
    
    /* 修改用户 */
    function update_user($data,$id)
    {
        $this->load->rwdb($this->_db_write);
        if($this->rwdb->update('user',$data,array('id'=>$id))){
            return true;
        }else{
            $this->_error = '修改用户失败';
            return false;
        }
    }
    
    /* 修改多个用户 */
    function update_users($data,$ids)
    {
        $this->load->rwdb($this->_db_write);
        $this->rwdb->where_in('id',$ids);

        if($this->rwdb->update('user',$data)){
            return true;
        }else{
            $this->_error = '修改用户失败';
            return false;
        }
    }
    
    /* 返回错误 */
    function get_error()
    {
        return $this->_error;
    }
    
    /* 通过user_id获取用户信息 */
    function get_by_user_id($id)
    {
    	$this->load->rwdb($this->_db_read);
    	$query = $this->rwdb->query('SELECT `id`,`groupId`,`user`, `phone_number`,`pass`,`userName`,`email`,`status` FROM `user` WHERE `id` = ?',array($id));
    	return $query->row_array();
    }
    
    /* 通过groupId获取用户信息 */
    function get_by_group_id($id)
    {
    	$this->load->rwdb($this->_db_read);
    	$query = $this->rwdb->query("SELECT `id`,`groupId`,`user`,`userName`,`email`,`status` FROM `user` WHERE `status` = 'allow' AND `groupId` = ?",array($id));
    	return $query->result_array();
    }

    /* 通过groupIds批量获取用户信息 */
    function get_count_by_group_ids($id_list)
    {
    	$this->load->rwdb($this->_db_read);
        
        $this->rwdb->select('groupId,COUNT(*) AS count');
        $this->rwdb->from('user');
        $this->rwdb->where_in('groupId', $id_list);
        $this->rwdb->where('status', 'allow');
    	$this->rwdb->group_by('groupId');
        $query = $this->rwdb->get();
        
        return $query->result_array('groupId');
    }
    
    /* 通过Ids批量获取用户信息 */
    function get_name_by_ids($ids)
    {
    	$this->load->rwdb($this->_db_read);
        
        $this->rwdb->select('id,userName');
        $this->rwdb->from('user');
        $this->rwdb->where_in('id', $ids);
        $query = $this->rwdb->get();
        
        return $query->result_array('id');
    }
    
    /* 通过用户名及密码，验证用户名 */
    function get_by_account($user,$pass)
    {
    	$this->load->rwdb($this->_db_read);
        
        $sql = "SELECT `id`,`groupId`,`user`,`userName`,`email` FROM `user` WHERE `status` = 'allow' AND `user` = ? AND `pass` = ? ";
        $query = $this->rwdb->query($sql, array($user,$pass));
        return $query->row_array();
    }

    /* 通过手机号，验证用户 */
    function get_account_by_phone($phone_number)
    {
        $this->load->rwdb($this->_db_read);
        
        $sql = "SELECT `id`,`groupId`,`user`, `phone_number`, `userName`,`email` FROM `user` WHERE `status` = 'allow' AND `phone_number` = ?";
        $query = $this->rwdb->query($sql, array($phone_number));
        return $query->row_array();
    }

    /* 拼接条件语句 */
    protected function _where()
    {
        $sql = ' WHERE 1';

        if (isset($this->_params['groupId']) && $this->_params['groupId'] > 0) {
            $sql .= " AND `groupId` =" . $this->_params['groupId'];
        }
        
        if (isset($this->_params['user']) && !empty($this->_params['user'])) {
            $sql .= " AND `user` like '%" . $this->_params['user']."%'";
        }
        
        if (isset($this->_params['userName']) && !empty($this->_params['userName'])) {
            $sql .= " AND `userName` like '%" . $this->_params['userName']."%'";
        }
        
        if (isset($this->_params['status']) && !empty($this->_params['status'])) {
            $sql .= " AND `status` like '%" . $this->_params['status']."%'";
        }

        if (isset($this->_params['phone_number']) && !empty($this->_params['phone_number'])) {
            $sql .= " AND `phone_number` like '%" . $this->_params['phone_number']."%'";
        }

        return $sql;
    }
}
