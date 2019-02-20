<?php
 /**
 *  内容管理系统
 *
 * @author      yankm
 * @date        2016-07-05
 * @category    S_content_manage_dao
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class S_content_manage_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'content_manage';

    public    $_fields   = 'content_manage_id, title, content, remark, project_id, is_enabled, is_deleted, create_time, update_time';
}