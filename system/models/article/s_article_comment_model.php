<?php
/**
 * 文章评论相关服务
 *
 * @author      madesheng
 * @date        2017-04-18
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_article_comment_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'article_comment_id';
    public $dao = 'article/s_article_comment_dao';

    public $cfg = [
        'type' => [
            1 => '用户评论',
            2 => '管理员回复',
        ],
        'audit_status' => [
            1 => '待审核',
            2 => '通过',
            3 => '不通过',
        ],
        'is_return' => [
            1 => '已回复',
            2 => '未回复',
        ],
    ];

    // 获取配置
    public function get_cfg($params = [])
    {
        return $this->cfg;
    }

    /**
     * 添加文章评论
     *
     * @access  public
     *
     * @param   array   $params    插入数据
     *
     * @return  bool|int
     */
    public function add_article_comment($params = [])
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
     * 通过唯一字段修改单条文章评论（审核）
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
        $input  = ['article_comment_id', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按article_id进行修改
        if (isset($params['article_comment_id']) && !empty($params['article_comment_id'])) {
            $result = $this->update_by_id($params['article_comment_id'], $params['set']);
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