<?php
 /**
 * 用户留言
 *
 * @author      madesheng
 * @date        2017-02-17
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_guestbook_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'guestbook';

    public    $_fields   = '`guestbook_id`, `member_id`, `ip`, `address`, `parent_guestbook_id`, `message`, `level`, `type`, `audit_status`, `is_reply`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`';
}