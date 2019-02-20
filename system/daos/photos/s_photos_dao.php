<?php
 /**
 * 照片模块
 *
 * @author      madesheng
 * @date        2017-04-26
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class S_photos_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'photos';

    public    $_fields   = '`photos_id`, `photo_title`, `photo_describe`, `photo_url`, `photos_album_id`, `album_name`, `like_num`, `is_enabled`, `is_deleted`, `create_user_id`, `create_user_name`, `create_time`, `update_user_id`, `update_user_name`, `update_time`';
}