<?php
 /**
 * 网站slogan
 *
 * @author      madesheng
 * @date        2017-02-17
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_slogan_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'slogan';

    public    $_fields   = '`slogan_id`, `content`, `item_type`, `item_explain`, `is_enabled`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`, `update_user_id`, `update_user_name`, `update_time`';
}