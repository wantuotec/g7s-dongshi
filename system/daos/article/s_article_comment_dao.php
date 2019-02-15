<?php
 /**
 * 文章评论
 *
 * @author      madesheng
 * @date        2017-04-18
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_article_comment_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'article_comment';

    public    $_fields   = '`article_comment_id`, `article_id`, `customer_id`, `customer_name`, `level`,`parent_comment_id`, `comment`, `type`, `audit_status`, `is_return`,`is_deleted`, `create_user_id`, `create_user_name`, `create_time`';
}