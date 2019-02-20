<?php
 /**
 * Api_log 接口日志
 *
 * @author      yanghaibo
 * @date        2014-12-23
 * @category    Api_log_model
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class Api_log_model extends CI_Model
{
    /**
     * 添加数据
     *
     * @access  public
     *
     * @param   array   $params         插入数据
     * @param   bool    $is_batch       是否是多条数据
     * @param   bool    $is_insert_id   单条的时候是否需要返回插入ID
     *
     * @return  bool|int
     */
    public function add($params = array(), $is_batch = false, $is_insert_id = false)
    {
        array_map_recursive('strip_tags', $params);
        array_map_recursive('trim'      , $params);
        invalid_data_filter_recursive($params);

        $this->load->dao('Api_log_dao');
        if (false == $is_batch) {
            $result = $this->Api_log_dao->insert($params, $is_insert_id);
        } else {
            $result = $this->Api_log_dao->insert_batch($params);
        }

        if (false === $result) {
            $this->set_error($this->Api_log_dao->get_error());
            return false;
        } else {
            return $result;
        }
    }
}