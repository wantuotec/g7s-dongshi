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
class S_configure_model extends CI_Model
{
    // 初始化
    public $id  = 'configure_id';
    public $dao = 'base/s_configure_dao';

    // memcache 过期时间
    private $__memcache_expire_time = 86400;

    // memcache 前缀
    private $__memcache_prefix = 'configure_';

    // 类型 （10.客户端  20.服务端）
    public $cfg = [
        'type'  => [
            10 => '客户端',
            20 => '服务端',
        ],
    ];

    /**
     * 获取配置列表
     *
     * @access  public
     *
     * @param   array   $params    要更新的信息
     *
     * @return  bool
     */
    public function get_cfg_list($params = [])
    {
        return $this->cfg;
    }

    /**
     * 根据 configure_name 获得 configure_value
     *
     * @param   $params     array   获取的参数
     *
     * @return  string|bool
     */
    public function get_template_by_configure_name($params = [])
    {
        data_filter($params);

        $configure_name = $params['configure_name'];
        $replace        = $params['replace'];
        $is_refresh     = isset($params['is_refresh']) ? $params['is_refresh'] : false;

        if (empty($configure_name)) {
            $this->set_error(20034);
            return false;
        }

        $this->load->library('memcache');
        // 需要刷新 memcache
        if (!$is_refresh) {
            $result = $this->memcache->get($this->__memcache_prefix . $configure_name);
        }

        if (empty($result) || $is_refresh) {
            $result = $this->get_by_configure_name($configure_name);

            if (!empty($result['configure_name'])) {
                $this->memcache->set($this->__memcache_prefix . $result['configure_name'], $result, $this->__memcache_expire_time);
            }
        }

        $string = variable_template($result['configure_value'], $replace);

        return $string;
    }

    /**
     * 获取系统配置列表
     *
     * @access  public
     *
     * @param   array   $params     查询参数
     *
     * @return  bool|array
     */
    public function get_configure_list($params = [])
    {
        data_filter($params);

        $input  = ['fields','order_by','is_pages','like','where','project_id','where_in','key_name'];
        $output = ['list','total','pages'];

        $this->filter($input, $params);

        $result = $this->get_list($params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } 

        $this->filter($output, $result);

        return $result;
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

        $input  = ['configure_id', 'configure_name', 'fields'];
        $output = [];

        // 输入过滤
        $this->filter($input, $params);

        $fields = filter_empty('fields', $params);

        if (isset($params['configure_id']) && !empty($params['configure_id'])) {
            $result = $this->get_by_configure_id($params['configure_id'], $fields);

        } elseif (isset($params['configure_name']) && !empty($params['configure_name'])) {
            $result = $this->get_by_configure_name($params['configure_name'], $fields);

        } else {
            $this->set_error(40007);
            return false;
        }
            
        return $result;
    }

    /**
     * 根据唯一字段名修改信息
     *
     * @param   $params array   参数
     *
     * @return  array
     */
    public function update_by_params($params = [])
    {
        data_filter($params);

        $input  = ['configure_id', 'configure_name', 'set'];
        $output = [];
        // 输入过滤
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(40001);
            return false;
        }

        if (isset($params['configure_id']) && !empty($params['configure_id'])) {
            $result = $this->update_by_id($params['configure_id'], $params['set']);

        } elseif (isset($params['configure_name']) && !empty($params['configure_name'])) {
            $result = $this->update_by_configure_name($params['configure_name'], $params['set']);

        } else {
            $this->set_error(40007);
            return false;
        }

        return $result;
    }

    /**
     * 添加
     *
     * @param   string  $params    添加数据
     *
     * @return  bool|array
     */
    public function add_configure($params = [])
    {
        invalid_data_filter_recursive($params);

        $params['is_batch']     = (true === $params['is_batch'])     ? true : false; // 是否多条插入
        $params['is_insert_id'] = (true === $params['is_insert_id']) ? true : false; // 是否返回最后一条记录

        $result = $this->add($params['insert_data'], $params['is_batch'], $params['is_insert_id']);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
}