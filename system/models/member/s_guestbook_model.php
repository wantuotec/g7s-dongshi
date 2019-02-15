<?php
/**
 * 用户留言相关服务
 *
 * @author      madesheng
 * @date        2017-06-02
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_guestbook_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'guestbook_id';
    public $dao = 'member/s_guestbook_dao';

    public $cfg = [
        'type' => [
            1 => '用户留言',
            2 => '管理员回复',
            3 => '用户回复',
        ],
        'audit_status' => [
            1 => '审核中',
            2 => '通过',
            3 => '不通过',
        ],
        'is_reply' => [
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
     * 根据唯一字段名获取留言信息
     *
     * @param   $params array   参数
     *
     * @return  array
     */
    public function get_by_params($params = [])
    {
        data_filter($params);

        $input  = ['guestbook_id','fields'];
        $output = [];

        // 输入过滤
        $this->filter($input, $params);

        $fields = filter_empty('fields', $params);

        $result = [];
        if (isset($params['guestbook_id']) && !empty($params['guestbook_id'])) {
            $result = $this->get_by_id($params['guestbook_id'], $fields);
        } else {
            $this->set_error(40007);
            return false;
        }

        return $result;
    }

    /**
     * 添加留言
     *
     * @access  public
     *
     * @param   array   $params    插入数据
     *
     * @return  bool|int
     */
    public function add_guestbook($params = [])
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
     * 通过唯一字段修改单条留言信息（审核状态等）
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
        $input  = ['guestbook_id', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按guestbook_id进行修改
        if (isset($params['guestbook_id']) && !empty($params['guestbook_id'])) {
            $result = $this->update_by_id($params['guestbook_id'], $params['set']);
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

    /**
     * 通过父级留言ID查找下面的子级留言
     *
     * @access  public
     *
     * @param   array   $params    查询参数
     *
     * @return  bool
     */
    public function find_sub_guestbook($params = [])
    {
        data_filter($params);
        $data = [];

        $search = [
            'logic'               => $params['logic'],
            'ip'                  => $params['ip'],
            'parent_guestbook_id' => $params['guestbook_id'],
        ];
        $result = $this->recursion_sub_guestbook($search);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 递归查找子级留言
     *
     * @access  public
     *
     * @param   array   $params    父级留言ID
     *
     * @return  bool
     */
    public function recursion_sub_guestbook($params = [], &$data = [])
    {
        //当前处理逻辑类型（1.前端 2.后台）
        if (1 == $params['logic']) {
            $or_where = ['audit_status' => 2,"(`ip`='{$params['ip']}' and `audit_status`=1)"];
        } else {
            $or_where = null;
        }

        // 查找子级留言
        $search = [
            'is_pages'            => false,
            'key_name'            => 'guestbook_id',
            'parent_guestbook_id' => $params['parent_guestbook_id'],
            'or_where'            => $or_where,
        ];
        $result = $this->get_list($search);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }
        if (empty($result['list'])) {
            return $data;
        }

        foreach ($result['list'] as $key => $val) {
            //保存子留言
            $data[] = $val;

            //继续查找下面的子留言
            $sub_search = ['logic' => $params['logic'], 'parent_guestbook_id' => $key, 'ip' => $params['ip']];
            $this->recursion_sub_guestbook($sub_search, $data);
        }
            
        return $data;
    }
}