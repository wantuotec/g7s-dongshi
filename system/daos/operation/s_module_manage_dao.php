<?php
 /**
 * 功能模块儿管理
 *
 * @author      liunian
 * @date        2014-12-20
 * @category    s_module_manage_dao.php
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class S_module_manage_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'module_manage';

    public    $_fields   = '`module_manage_id`, `module_explain`, `module_mark`, `is_open`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`, `update_user_id`, `update_user_name`, `update_time`';
}