<?php
/**
 * 后台内容管理系统
 *
 * @author      shaopengcheng
 * @date        2016-11-22
 * @category    content_manage_model
 * @copyright   Copyright(c) 2016
 * @version     $Id:$
 */
class S_content_manage_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'content_manage_id';
    public $dao = 'base/s_content_manage_dao';

    public $cfg = [
        'is_enabled' => [
            1 => '已启用',
            2 => '未启用',
        ],
        'is_deleted' => [
            1 => '已删除',
            2 => '未删除',
        ],
    ];

    // 获取配置
    public function get_cfg($params = [])
    {
        return $this->cfg;
    }

    /**
     * 获取列表
     *
     * @access  public
     *
     * @param   array   $params     查询参数
     *
     * @return  bool|array
     */
    public function get_content_manage_list($params = [])
    {
        data_filter($params);

        $input  = ['title', 'content' , 'is_enabled','remark' , 'project_id', 'is_pages','order_by' ,'page_size','like','style','is_deleted'];
        $output = ['list','total'];

        $this->filter($input, $params);

        $result = $this->get_list($params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 添加数据
     *
     * @access  public
     *
     * @param   array   $params     插入数据
     *
     * @return  bool|int
     */
    public function add_content($params = [])
    {
        data_filter($params);

        $is_batch     = ($params['is_batch'] && true == $params['is_batch'])          ? true : false; //是否多条插入数据
        $is_insert_id = ($params['is_insert_id'] && true === $params['is_insert_id']) ? true : false; //是否返回最后一条记录

        $result = $this->add($params['insert_data'], $params['is_batch'], $params['is_insert_id']);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 根据唯一字段名获取角色信息
     *
     * @param   $params array   参数
     *
     * @return  array
     */
    public function get_by_params($params = [])
    {
        data_filter($params);

        $input  = ['content_manage_id','fields'];
        $output = [];

        // 输入过滤
        $this->filter($input, $params);

        if (empty($params['content_manage_id'])) {
            $this->set_error(54005);
            return false;
        }

        $fields = filter_empty('fields', $params);

        if (isset($params['content_manage_id']) && !empty($params['content_manage_id'])) {
            $result = $this->get_by_id($params['content_manage_id'], $fields);
        } else {
            $this->set_error(54005);
            return false;
        }

        return $result;
    }

    /**
     * 更新数据
     *
     * @access  public
     *
     * @param   array   $params    要更新的信息
     *
     * @return  bool
     */
    public function update_by_params($params = [])
    {
        data_filter($params);

        $input  = ['content_manage_id','set'];
        $output = [];

        // 输入过滤
        $this->filter($input, $params);
        $ids = $params['content_manage_id'];

        if (empty($params['set'])) {
            $this->set_error(54002);
            return false;
        }

        if (isset($params['content_manage_id']) && !empty($params['content_manage_id'])) {
            $result = $this->update_by_id($params['content_manage_id'], $params['set']);
        } else {
            $this->set_error(54005);
            return false;
        }
    
        return $result;
    }

}