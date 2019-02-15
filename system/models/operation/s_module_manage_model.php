<?php
/**
 * 网站功能模块管理相关服务
 *
 * @author      madesheng
 * @date        2017-02-17
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_module_manage_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'module_manage_id';
    public $dao = 'operation/s_module_manage_dao';

    public $cfg = [
        'is_open' => [
            1 => '已启用',
            2 => '已关闭',
        ],
    ];

    // 获取配置
    public function get_cfg($params = [])
    {
        return $this->cfg;
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
        $input  = ['module_manage_id', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按module_manage_id进行修改
        if (isset($params['module_manage_id']) && !empty($params['module_manage_id'])) {
            $result = $this->update_by_id($params['module_manage_id'], $params['set']);
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