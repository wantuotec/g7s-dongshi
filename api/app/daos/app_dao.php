<?php
 /**
 * key 管理信息表
 *
 * @author      liunian
 * @date        2014-12-20
 * @category    App_dao
 * @copyright   Copyright(c) 2014
 *
 * @version     $Id$
 */
class App_dao extends CI_Dao
{
    protected    $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected    $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected    $_table    = 'app';

    protected    $_fields   = '`app_key`, `app_name`, `app_secret`, `app_session`, `session_expire_time`, `app_type`, `platform_id`, `project_id`, `is_internal`, `is_enabled`';
}