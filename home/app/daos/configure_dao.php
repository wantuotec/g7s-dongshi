<?php
 /**
 * 配置参数信息管理
 *
 * @author      madesheng
 * @date        2017-02-26
 * @category    configure_dao.php
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class Configure_dao extends CI_Dao
{
    protected    $_db_write = array('cluster' => 2, 'mode' => 'write');
    protected    $_db_read  = array('cluster' => 2, 'mode' => 'read');

    protected    $_table    = 'configure';

    protected    $_fields   = '`configure_id`, `configure_name`, `configure_value`, `description`, `type`';
}