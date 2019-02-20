<?php
 /**
 * 配置参数信息管理
 *
 * @author      liunian
 * @date        2014-12-20
 * @category    configure_dao.php
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class S_configure_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'configure';

    public    $_fields   = 'configure_id, configure_name, configure_value, description, type, project_id, is_deleted';
}