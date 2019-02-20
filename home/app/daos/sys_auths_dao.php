<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: sys_auths_dao.php
 * @create : Apr. 5, 2012
 * @update : Apr. 5, 2012
 */

class Sys_auths_dao extends CI_Dao
{
    private   $_db_write = array('cluster' => 1, 'mode' => 'write');
    private   $_db_read  = array('cluster' => 1, 'mode' => 'read');
    protected $_params;
    protected $_error;

    function __construct()
    {
        parent::__construct();
    }

    /* by get_auths_by_group_id */
    function get_auths_by_group_id($id)
    {
        $this->load->rwdb($this->_db_read);
        $sql = " SELECT `sa`.`id`,`sa`.`systemId`,`sa`.`sysGroupId`,`sa`.`access`,`sa`.`edit`,`sa`.`addnew`,`sa`.`del`,`sa`.`check`,`sa`.`managerCheck`,`p`.`progName`,`p`.`funcName`,`p`.`is_display` ";
        $sql .= " FROM `auth` AS `sa`";
        $sql .= " INNER JOIN `programs` AS `p` ON `sa`.`systemId` = `p`.`systemId`";
        $sql .= " INNER JOIN `groups` AS `ag` ON `sa`.`groupId` = `ag`.`id`";
        $sql .= " WHERE `p`.`is_deleted`=0 AND `sa`.`groupId` = ? AND `sa`.`sysGroupId` = `p`.`sysGroupId` AND `ag`.`status` = 'allow'";
        $sql .= " ORDER BY `p`.`sort` ASC,`sa`.`sysGroupId`,`sa`.`systemId` ASC";
        $query = $this->rwdb->query($sql, array($id));
        return $query->result_array('funcName');
    }

    /* delete_by_group_id */
    function delete_by_group_id($id)
    {
        $this->load->rwdb($this->_db_write);
        return $this->rwdb->delete('auth', array('groupId' => $id));
    }

    /* 批量插入 */
    function insert_auths($data)
    {
        if (count($data) < 1) {
            return true;
        }
        $binds = array();
        $this->load->rwdb($this->_db_write);
        $sql = "INSERT INTO auth (`groupId`,`systemId`,`sysGroupId`,`access`,`edit`,`addnew`,`del`,`check`,`managerCheck`,`createTime`) VALUES";
        foreach ($data as $item) {
            $sql .= "(?,?,?,?,?,?,?,?,?,?),";
            $binds[] = $item['groupId'];
            $binds[] = $item['systemId'];
            $binds[] = $item['sysGroupId'];
            $binds[] = $item['access'];
            $binds[] = $item['edit'];
            $binds[] = $item['addnew'];
            $binds[] = $item['del'];
            $binds[] = $item['check'];
            $binds[] = $item['managerCheck'];
            $binds[] = date('Y-m-d H:i:s');
        }
        if ($this->rwdb->query(substr($sql, 0, -1), $binds)) {
            return true;
        } else {
            $this->_error = '插入权限失败';
            return false;
        }
    }

    /* 返回错误 */
    function get_error()
    {
        return $this->_error;
    }
}
