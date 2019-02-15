<?php
/**
 * 相册相关服务
 *
 * @author      madesheng
 * @date        2017-04-26
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_photos_album_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'photos_album_id';
    public $dao = 'photos/s_photos_album_dao';

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
     * 添加相册
     *
     * @access  public
     *
     * @param   array   $params    插入数据
     *
     * @return  bool|int
     */
    public function add_photos_album($params = [])
    {
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
     * 通过唯一字段修改单条相册信息
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
        $input  = ['photos_album_id', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按photos_album_id进行修改
        if (isset($params['photos_album_id']) && !empty($params['photos_album_id'])) {
            $result = $this->update_by_id($params['photos_album_id'], $params['set']);
        // 都不存在时，给出提示
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