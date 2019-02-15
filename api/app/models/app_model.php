<?php
 /**
 * app 信息表管理
 *
 * @author      liunian
 * @date        2014-12-20
 * @category    App_model.php
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class App_model extends CI_Model
{
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
            'app_key'     => isset($params['app_key'])     ? $params['app_key']     : null,
            'app_type'    => isset($params['app_type'])    ? $params['app_type']    : null,
            'is_internal' => isset($params['is_internal']) ? $params['is_internal'] : null,
            'is_enabled'  => isset($params['is_enabled'])  ? $params['is_enabled']  : null,
            'platform_id' => isset($params['platform_id']) ? $params['platform_id'] : null,
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
                'app_name' => !empty($params['app_name']) ? $params['app_name'] : null,
            ),
        );
        invalid_data_filter_recursive($search);

        $this->load->dao("App_dao");
        $result = $this->App_dao->get($search);

        if (false == $result) {
            $this->set_error($this->App_dao->get_error());
            return false;
        } else {
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

        $this->load->dao('App_dao');
        $result = $this->App_dao->get_row($search);

        if (false === $result) {
            $this->set_error($this->App_dao->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 根据 KEY 获得详情
     *
     * @param   string  $key    唯一KEY
     * @param   string  $fields 查询字段
     *
     * @return  array|bool
     */
    public function get_by_key($key, $fields = '')
    {
        data_filter($key);
        data_filter($fields);

        if (empty($key)) {
            $this->set_error(55001);
            return false;
        }

        $search = array(
            'app_key' => $key,
        );

        return $this->get($search, $fields);
    }

    /**
     * 根据 id 获得详情
     *
     * @param   int      $id        平台ID
     * @param   string   $fields    查询字段
     *
     * @return  array|bool
     */
    public function get_by_platform_id($id, $fields = '')
    {
        $id = intval($id);
        data_filter($fields);

        if (empty($id)) {
            $this->set_error(56001);
            return false;
        }

        $search = array(
            'platform_id' => $id,
        );

        $result = $this->get($search, $fields);

        return $result;
    }
}