<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Rest API 入口
 * 
 * ============================ Notice =================================
 *
 * 所有接口的入口文件
 * 
 * Add by 杨海波 2014-12-20
 * ============================ Notice =================================
 * 
 *
 * @author       杨海波
 * @date         2014-12-20
 * @category     Rest
 * @copyright    Copyright(c) 2014
 * @version      $Id$
 */

class Rest extends CI_Controller
{
    /**
     * 接口默认入口地址
     */
    public function index()
    {
        $this->load->library('request');
        $this->request->run();
    }
}