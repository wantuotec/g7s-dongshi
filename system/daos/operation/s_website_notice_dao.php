<?php
 /**
 * 网站公告
 *
 * @author      madesheng
 * @date        2017-05-20
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_website_notice_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'website_notice';

    public    $_fields   = '`website_notice_id`, `content`, `item_type`, `is_enabled`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`, `update_user_id`, `update_user_name`, `update_time`';
}