<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 默认首页
 *
 * @author       杨海波
 * @date         2014-12-20
 * @category     Index
 * @copyright    Copyright(c) 2014
 * @version      $Id$
 */
class Index extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 接口默认入口地址
     */
    public function index()
    {
        echo '请使用标准的访问方式请求API数据【http://m.dreamma.cn/rest】';
        exit;
    }

}