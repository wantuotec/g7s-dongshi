<?php
 /**
 * 配置管理
 *
 * @author      liunian
 * @date        2015-01-22
 * @category    Configure_model.php
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class Configure_model extends CI_Model
{
    // memcache 过期时间
    private $__memcache_expire_time = 86400;

    // memcache 前缀
    private $__memcache_prefix = 'configure_';

    // 类型 （10.客户端  20.服务端）
    public $type = array(
        '10' => '客户端',
        '20' => '服务端',
    );

    /**
     * 初始化查询参数
     *
     * @param   array   $params     查询参数
     *
     * @return  array
     */
    private function __init_params(array $params = array())
    {
        data_filter($params);

        $where = array(
            'configure_id'    => isset($params['configure_id'])    ? $params['configure_id']    : null,
            'configure_name'  => isset($params['configure_name'])  ? $params['configure_name']  : null,
            'configure_value' => isset($params['configure_value']) ? $params['configure_value'] : null,
            'type'            => isset($params['type'])            ? $params['type']            : null,
            'is_deleted'      => isset($params['is_deleted'])      ? $params['is_deleted']      : 2,
        );

        invalid_data_filter($where);

        return $where;
    }

    /**
     * 获得列表数据
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function get_list(array $params = array())
    {
        data_filter($params);

        $search = array(
            'where'        => $this->__init_params($params),
            'page_size'    => empty($params['page_size'])    ? null : $params['page_size'],
            'page_no'      => empty($params['page_no'])      ? null : $params['page_no'],
            'offset'       => empty($params['offset'])       ? null : $params['offset'],
            'is_pages'     => empty($params['is_pages'])     ? true : $params['is_pages'],
            'fields'       => empty($params['fields'])       ? null : $params['fields'],
            'key_name'     => empty($params['key_name'])     ? null : $params['key_name'],
            'where_in'     => empty($params['where_in'])     ? null : $params['where_in'],
            'order_by'     => empty($params['order_by'])     ? null : $params['order_by'],
            'or_where'     => empty($params['or_where'])     ? null : $params['or_where'],
            'where_not_in' => empty($params['where_not_in']) ? null : $params['where_not_in'],
            'like'         => array(
                'description' => !empty($params['description']) ? $params['description'] : null,
            ),
        );
        invalid_data_filter_recursive($search);

        $this->load->dao("Configure_dao");
        $result = $this->Configure_dao->get($search);

        if (false == $result) {
            $this->set_error($this->Configure_dao->get_error());
            return false;
        } else {
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]['type_title'] = $this->type[$value['type']];
            }

            return $result;
        }
    }

    /**
     * 根据条件取得1条数据
     *
     * @param   array   $params     搜索条件
     * @param   string  $fields     查询字段
     *
     * @return  array|bool
     */
    public function get(array $params, $fields = '')
    {
        data_filter($params);
        data_filter($fields);

        $search = array(
            'where'  => $this->__init_params($params),
            'fields' => empty($fields) ? null : $fields,
        );

        invalid_data_filter_recursive($search);

        $this->load->dao('Configure_dao');
        $result = $this->Configure_dao->get_row($search);

        if (false === $result) {
            $this->set_error($this->Configure_dao->get_error());
            return false;
        } else {
            if ($result['type']) {
                $result['type_title'] = $this->type[$result['type']];
            }
            return $result;
        }
    }

    /**
     * 根据 KEY 获得详情
     *
     * @param   string   $key       唯一KEY
     * @param   string   $fields    查询字段
     *
     * @return  array|bool
     */
    public function get_by_id($id, $fields = '')
    {
        data_filter($id);
        data_filter($fields);

        if (empty($id)) {
            $this->set_error('配置ID为空');
            return false;
        }

        $search = array(
            'configure_id' => $id,
        );

        return $this->get($search, $fields);
    }

    /**
     * 根据 name 获得详情
     *
     * @param   string   $name      配置名称
     * @param   string   $fields    查询字段
     *
     * @return  array|bool
     */
    public function get_by_name($name = null, $fields = '')
    {
        data_filter($name);
        data_filter($fields);

        if (empty($name)) {
            $this->set_error('配置名称为空');
            return false;
        }

        $search = array(
            'configure_name' => $name,
        );

        return $this->get($search, $fields);
    }

    /**
     * 添加数据
     *
     * @param   array   $params
     * @param   bool    $is_batch
     * @param   bool    $is_insert_id
     *
     * @return  array|bool
     */
    public function add($params = array(), $is_batch = false, $is_insert_id = false)
    {
        data_filter($is_batch);
        data_filter($is_insert_id);

        $this->load->dao('Configure_dao');
        if (false == $is_batch) {
            $result = $this->Configure_dao->insert($params, $is_insert_id);
        } else {
            $result = $this->Configure_dao->insert_batch($params);
        }

        if (false === $result) {
            $this->set_error($this->Configure_dao->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 根据 id 编辑信息
     *
     * @param   array    $params    修改数据
     * @param   int      $id        配置ID
     *
     * @return  array|bool
     */
    public function update_by_id(array $params = array(), $id = null)
    {
        data_filter($id);
        data_filter($params);
        array_map_recursive('urldecode', $params);

        if (empty($id)) {
            $this->set_error('配置ID为空');
            return false;
        }

        $set = array(
            'configure_value' => !isset($params['configure_value']) ? null : $params['configure_value'],
            'description'     => !isset($params['description'])     ? null : $params['description'],
            'type'            => !isset($params['type'])            ? null : $params['type'],
            'is_deleted'      => !isset($params['is_deleted'])      ? null : $params['is_deleted'],
        );

        append_update_info($set);

        $where = array(
            'configure_id' => $id,
        );

        invalid_data_filter($set, array(null));
        invalid_data_filter($where);

        if (empty($set)) {
            $this->set_error('没有要修改数据');
            return false;
        }

        if (empty($where)) {
            $this->set_error('没有修改条件');
            return false;
        }

        $this->load->dao('Configure_dao');
        $result = $this->Configure_dao->update($set, $where, 1);

        if (false === $result) {
            $this->set_error($this->Shop_dao->get_error());
            return false;
        } else {
            // 暂未使用 memcache，所以不去做刷新
            // if ($params['configure_name']) {
            //     $this->get_template_by_configure_name($params['configure_name'], array(), true);
            // }

            return $result;
        }
    }

    /**
     * 添加
     *
     * @param   array   $params     商家信息
     *
     * @return  bool
     */
    public function add_configure($params = array())
    {
        data_filter($params);
        $configure = array();

        if (empty($params['configure_name'])) {
            $this->set_error('配置名称为空');
            return false;
        }

        $result = $this->__is_verify($params);
        if (false == $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // 查找此名称是否存在相同的
        $configure_result = $this->get_by_name($params['configure_name'], 'configure_name, configure_value, type');
        if (false === $configure_result) {
            $this->set_error($this->get_error());
            return false;
        }

        if ($configure_result) {
            $this->set_error('配置名称已经存在');
            return false;
        }

        unset($configure_result);

        // 转义过来的
        array_map_recursive('urldecode', $params);

        $configure['configure_name']  = isset($params['configure_name'])  ? $params['configure_name']  : null;
        $configure['configure_value'] = isset($params['configure_value']) ? $params['configure_value'] : null;
        $configure['description']     = isset($params['description'])     ? $params['description']     : null;
        $configure['type']            = isset($params['type'])            ? $params['type']            : null;

        append_create_update($configure);

        $configure_result = $this->add($configure, false, true);
        if (false === $configure_result) {
            $this->set_error($this->get_error());
            return false;
        } else {
            $configure['configure_id'] = $configure_result;
        }

        // 成功以后放到 memcache 中
        // $this->load->library('memcache');
        // $this->memcache->set($this->__memcache_prefix . $params['configure_name'], $configure, $this->__memcache_expire_time);

        return $configure_result;
    }

    /**
     * 修改
     *
     * @param   array   $params     修改信息
     *
     * @return  bool
     */
    public function edit_configure($params = array())
    {
        data_filter($params);
        $configure = array();

        // 先获取下这个配置的数据 如果有就继续  有可能被删除
        $configure_info = $this->get_by_id($params['configure_id'], 'configure_id, configure_name, configure_value, type');

        if (false === $configure_info) {
            $this->set_error($this->get_error());
            return false;
        }

        if (empty($configure_info)) {
            $this->set_error('配置信息数据为空');
            return false;
        }

        $result = $this->__is_verify($params);
        if (false == $result) {
            $this->set_error($this->get_error());
            return false;
        }

        $configure['configure_value'] = isset($params['configure_value']) ? $params['configure_value'] : null;
        $configure['description']     = isset($params['description'])     ? $params['description']     : null;
        $configure['type']            = isset($params['type'])            ? $params['type']            : null;
        $configure['configure_name']  = $configure_info['configure_name'];

        // 修改
        $configure_result = $this->update_by_id($configure, $params['configure_id']);

        if (false === $configure_result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $configure_result;
    }

    /**
     * 根据 configure_name 获得 configure_value
     *
     * @param   string   $configure_name
     * @param   array    $replace           需要根据 key 替换的数组
     * @param   bool     is_refresh         是否需要刷新 memcache
     *
     * @return  string|bool
     */
    public function get_template_by_configure_name($configure_name, array $replace = array(), $is_refresh = false)
    {
        data_filter($configure_name);
        data_filter($replace);

        if (empty($configure_name)) {
            $this->set_error('配置名称为空');
            return false;
        }

        // 需要刷新 memcache
        if (!$is_refresh) {
            $this->load->library('memcache');
            $result = $this->memcache->get($this->__memcache_prefix . $configure_name);
        }

        if (empty($result) || $is_refresh) {
            $result = $this->get_by_name($configure_name);

            if(!empty($result['configure_name'])) {
                $this->load->library('memcache');
                $this->memcache->set($this->__memcache_prefix . $result['configure_name'], $result, $this->__memcache_expire_time);
            }
        }

        return variable_template($result['configure_value'], $replace);
    }

    /**
     * 检验数据
     *
     * @param   array   $params     检验数据
     *
     * @return  bool
     */
    public function __is_verify($params = array())
    {
        data_filter($params);
        array_map_recursive('urldecode', $params);

        if ('add' == $params['submit_type']) {
            if (empty($params['configure_name'])) {
                $this->set_error('配置名称为空');
                return false;
            }

            if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $params['configure_name'], $match)) {
                $this->set_error('配置名称中有中文');
                return false;
            }

            if (preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $params['configure_name'])) {
                $this->set_error('配置名称中有特殊字符');
                return false;
            }
        }

        if (empty($params['configure_value'])) {
            $this->set_error('配置的值为空');
            return false;
        }

        if (empty($params['type'])) {
            $this->set_error('配置类型为空');
            return false;
        }

        if (empty($params['description'])) {
            $this->set_error('描述说明为空');
            return false;
        }

        return true;
    }
}