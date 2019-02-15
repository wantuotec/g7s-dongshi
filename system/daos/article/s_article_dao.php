<?php
 /**
 * 文章
 *
 * @author      madesheng
 * @date        2017-04-18
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_article_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'article';

    public    $_fields   = '`article_id`, `article_title`, `cover_words`, `content`, `cover_photo`, `article_category_id`, `origin_type`, `read_num`, `like_num`, `is_recommend`, `sort`, `is_enabled`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`, `update_user_id`, `update_user_name`, `update_time`';
}