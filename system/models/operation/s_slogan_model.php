<?php
/**
 * 网站slogan相关服务
 *
 * @author      madesheng
 * @date        2017-02-17
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_slogan_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'slogan_id';
    public $dao = 'operation/s_slogan_dao';

    public $cfg = [
        'is_enabled' => [
            1 => '有效',
            2 => '无效',
        ],
    ];

    // 获取配置
    public function get_cfg($params = [])
    {
        return $this->cfg;
    }

    /**
     * 添加slogan
     *
     * @access  public
     *
     * @param   array   $params    插入数据
     *
     * @return  bool|int
     */
    public function add_slogan($params = [])
    {
        data_filter($params);

        // 定义输入输出
        $input  = ['list', 'is_batch', 'is_insert_id'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        // 判断入库数据是否为空
        if (empty($params['list'])) {
            $this->set_error(30004);
            return false;
        }

        $is_batch     = ($params['is_batch']     && true == $params['is_batch'])     ? true : false;
        $is_insert_id = ($params['is_insert_id'] && true == $params['is_insert_id']) ? true : false;

        $result = $this->add($params['list'],$is_batch,$is_insert_id);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 通过唯一字段修改单条数据
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

        // 定义输入输出
        $input  = ['slogan_id', 'item_type', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按bd_develop_id进行修改
        if (isset($params['slogan_id']) && !empty($params['slogan_id'])) {
            $result = $this->update_by_slogan_id($params['slogan_id'], $params['set']);
        } elseif (isset($params['item_type']) && !empty($params['item_type'])) {
            $result = $this->update_by_item_type($params['item_type'], $params['set']);
        } else {
            $this->set_error(30003);
            return false;
        }

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
}