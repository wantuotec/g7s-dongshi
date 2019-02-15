<?php
 /**
 * 验证码管理
 *
 * @author      liunian
 * @date        2016-01-04
 * @category    s_captcha_dao
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class S_captcha_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'captcha';

    public    $_fields   = 'captcha_id, captcha_key, captcha_content, phone_number, project_id, is_used, create_time, is_deleted';
}