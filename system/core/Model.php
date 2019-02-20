<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model {
    // 错误信息
    protected $_ci_model_error = null;

    // 替换数据
    protected $_replace   = null;

    // dao 名称
    public $dao = null;

    // 唯一 id
    public $id = null;

    // dao 对象
    public $dao_obj = null;

    /**
     * Constructor
     *
     * @access public
     */
    function __construct()
    {
        $model = get_class($this);
        if ('CI_Model' != $model) {
            if (is_null($this->dao) || empty($this->dao)) {
                $this->dao = str_replace('_model', '_dao', $model);
            }

            if (is_null($this->id)) {
                $this->id = strtolower(str_replace('_dao', '_id' , $this->dao));
                if (0 === strpos($this->id, 's_')) {
                    $this->id = substr($this->id, 2);
                }
            }
        }

        log_message('debug', "Model Class Initialized");
    }

    /**
     * __get
     *
     * Allows models to access CI's loaded classes using the same
     * syntax as controllers.
     *
     * @param	string
     * @access private
     */
    function __get($key)
    {
        $CI =& get_instance();
        return $CI->$key;
    }

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->_ci_model_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_ci_model_error;
    }

    /**
     * 设置替换数据
     *
     * @return  array
     */
    public function set_replace()
    {
        $params = func_get_args();

        if (empty($params) || !is_array($params)) {
            $this->_replace = null;
        }

        // 如果 params 第一个参数是数组，那么表示输入的参数是数组 否则表示是多个参数
        if (is_array($params[0])) {
            $this->_replace = $params[0];
        } else {
            $this->_replace = $params;
        }
    }

    /**
     * 获取替换数据
     *
     * @return  array
     */
    public function get_replace()
    {
        return $this->_replace;
    }

    /**
     * 将dao中定义的字段字符串组成数组，供新增或更新时验证字段有效性
     *
     * @return  array   字段数组
     */
    public function get_fields()
    {
        return $this->init_dao()->get_fields();
    }

    /**
     * 过滤输入
     *
     * @access  private
     *
     * @param   array   $input      输入参数
     * @param   array   $allowed    允许的参数
     *
     * @return  array
     */
    public function filter(array $allowed = array(), array &$input = array())
    {
        $result = array();

        if (!empty($input) && is_array($input) && !empty($allowed) && is_array($allowed)) {
            foreach ($allowed as $allow) {
                isset($input[$allow]) && $result[$allow] = $input[$allow];
            }
        }

        // 直接改变 input 的值
        $input = $result;
        unset($result);

        return $input;
    }

    /**
     * __get
     *
     * default function
     * 
     *
     * @param   string
     * @access private
     */
    public function __call($method, $params = array())
    {
        if (!empty($method)) {
            // get by
            if (0 === strpos($method, 'get_by_')) {
                $key = str_replace('get_by_', '', $method);

                data_filter($params);

                if (empty($params[0])) {
                    $this->set_error(40003);
                    return false;
                }

                $params[0] = array(
                    $key => $params[0],
                );

                return call_user_func_array(array($this, 'get'), $params);
            }

            // update by
            if (0 === strpos($method, 'update_by_')) {
                $key = str_replace('update_by_', '', $method);

                if (empty($params[0]) ) {
                    $this->set_error(40003);
                    return false;
                }

                if (empty($params[1])) {
                    $this->set_error(40001);
                    return false;
                }

                $where = array(
                    $key => $params[0],
                );

                // 把 set 放到 第一位
                $params[0] = $params[1];
                $params[1] = $where;

                return call_user_func_array(array($this, 'update'), $params);
            }
        }
    }

    /**
     * 初始化 DAO
     *
     * @access  private
     *
     * @param   array   $params     查询参数
     *
     * @return  object
     */
    public function init_dao()
    {
        $name = str_replace('/', '_', $this->dao);
        $this->load->dao($this->dao, $name);

        $this->dao_obj = $this->$name;

        return $this->dao_obj;
    }

    /**
     * 获取执行的SQL语句
     *
     * @access  private
     *
     * @param   array   $params     查询参数
     *
     * @return  object
     */
    public function get_sql()
    {
        return $this->init_dao()->get_sql();
    }

    /**
     * 初始化查询参数
     *
     * @access  private
     *
     * @param   array   $params     查询参数
     *
     * @return  array
     */
    public function init_params(array $params = array())
    {
        data_filter($params);

        $where = array();

        // 只请允许dao中的分享出来
        $this->init_dao();
        $fields = explode(',', str_replace('`', '', $this->dao_obj->_fields));

        foreach ($fields as $v) {
            $v = trim($v);
            if (!empty($v) && isset($params[$v])) {
                $where[$v] = $params[$v];
            }
            if (!empty($v) && 'is_deleted' == $v) {
                $where[$v] = isset($params[$v]) ? $params[$v] : 2;
            }
        }

        $compare = array(
            'gt_' => ' >',
            'ge_' => ' >=',
            'lt_' => ' <',
            'le_' => ' <=',
            'ne_' => ' !=',
        );

        foreach ($params as $key => $val) {
            if (strlen($key) > 3 && in_array(substr($key, 0, 3), array_keys($compare))) {
                $comp = substr($key, 0, 3);
                $name = substr($key, 3);
                $where[$name . $compare[$comp]] = isset($val) ? $val : null;
            }
        }

        invalid_data_filter($where, array(null));

        return $where;
    }

    /**
     * 获取列表
     *
     * @access  public
     *
     * @param   array   $params     查询条件
     * @param   bool    $just_list  是否仅返回列表
     *
     * @return  array
     */
    public function get_list($params = array(), $just_list = false)
    {
        data_filter($params);

        $search = array(
            'where'        => empty($params['where'])        ? $this->init_params($params) : $params['where'],
            'escape'       => empty($params['escape'])       ? null : $params['escape'],
            'fields'       => empty($params['fields'])       ? null : $params['fields'],
            'key_name'     => empty($params['key_name'])     ? null : $params['key_name'],
            'where_in'     => empty($params['where_in'])     ? null : $params['where_in'],
            'like'         => empty($params['like'])         ? null : $params['like'],
            'page_size'    => empty($params['page_size'])    ? null : $params['page_size'],
            'page_no'      => empty($params['page_no'])      ? null : $params['page_no'],
            'is_pages'     => empty($params['is_pages'])     ? null : $params['is_pages'],
            'order_by'     => empty($params['order_by'])     ? null : $params['order_by'],
            'group_by'     => empty($params['group_by'])     ? null : $params['group_by'],
            'where_not_in' => empty($params['where_not_in']) ? null : $params['where_not_in'],
            'join'         => empty($params['join'])         ? null : $params['join'],
            'as'           => empty($params['as'])           ? null : $params['as'],
            'or_where'     => !empty($params['or_where'])     && is_array($params['or_where'])     ? $params['or_where']     : array(),
        );
        invalid_data_filter_recursive($search);

        $this->init_dao();
        $result = $this->dao_obj->get($search, false, false, (bool) $just_list);
//        print_r($this->dao_obj->get_sql());

        if (false === $result) {
            $this->set_error($this->dao_obj->get_error());
            return false;
        }

        // 附加 title 信息
        if (!empty($result['list']) && is_array($result['list']) && !empty($this->cfg) && is_array($this->cfg)) {
            foreach ($result['list'] as &$val) {
                foreach ($this->cfg as $k => $v) {
                    if (isset($val[$k]) && isset($v[$val[$k]])) {
                        $val[$k . '_title'] = $v[$val[$k]];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 根据条件获得单条数据
     *
     * @param   array   $params
     * @param   string  $fields
     *
     * @return array|bool
     */
    public function get(array $params, $fields = null)
    {
        data_filter($params);
        data_filter($fields);

        $search = array(
            'where'  => empty($params['where']) ? $this->init_params($params) : $params['where'],
            'fields' => empty($fields) ? null : $fields,
            'key_name'     => empty($params['key_name'])     ? null : $params['key_name'],
            'where_in'     => empty($params['where_in'])     ? null : $params['where_in'],
            'like'         => empty($params['like'])         ? null : $params['like'],
            'page_size'    => empty($params['page_size'])    ? null : $params['page_size'],
            'page_no'      => empty($params['page_no'])      ? null : $params['page_no'],
            'is_pages'     => empty($params['is_pages'])     ? null : $params['is_pages'],
            'order_by'     => empty($params['order_by'])     ? null : $params['order_by'],
            'group_by'     => empty($params['group_by'])     ? null : $params['group_by'],
            'where_not_in' => empty($params['where_not_in']) ? null : $params['where_not_in'],
            'or_where'     => !empty($params['or_where'])     && is_array($params['or_where'])     ? $params['or_where']     : array(),
            'no_filter'    => !isset($params['no_filter'])    ? null : $params['no_filter'],
        );

        invalid_data_filter_recursive($search);

        $this->init_dao();
        $result = $this->dao_obj->get_row($search);

        if (false === $result) {
            $this->set_error($this->dao_obj->get_error());
            return false;
        }
//        print_r($this->dao_obj->get_sql());

        // 附加 title 信息
        if (!empty($result) && is_array($result) && !empty($this->cfg) && is_array($this->cfg)) {
            foreach ($this->cfg as $k => $v) {
                if (isset($result[$k]) && isset($v[$result[$k]])) {
                    $result[$k . '_title'] = $v[$result[$k]];
                }
            }
        }
        return $result;
    }

    /**
     * 获取记录总数
     *
     * @access  public
     *
     * @param   array   $params     查询条件
     *
     * @return  int
     */
    public function get_total($params = array())
    {
        data_filter($params);

        $search = array(
            'where'        => empty($params['where'])        ? $this->init_params($params) : $params['where'],
            'fields'       => empty($params['fields'])       ? null : $params['fields'],
            'key_name'     => empty($params['key_name'])     ? null : $params['key_name'],
            'where_in'     => empty($params['where_in'])     ? null : $params['where_in'],
            'like'         => empty($params['like'])         ? null : $params['like'],
            'page_size'    => empty($params['page_size'])    ? null : $params['page_size'],
            'page_no'      => empty($params['page_no'])      ? null : $params['page_no'],
            'is_pages'     => empty($params['is_pages'])     ? null : $params['is_pages'],
            'order_by'     => empty($params['order_by'])     ? null : $params['order_by'],
            'group_by'     => empty($params['group_by'])     ? null : $params['group_by'],
            'where_not_in' => empty($params['where_not_in']) ? null : $params['where_not_in'],
            'or_where'     => empty($params['or_where'])     ? null : $params['or_where'],
            'join'         => empty($params['join'])         ? null : $params['join'],
            'as'           => empty($params['as'])           ? null : $params['as'],
        );
        invalid_data_filter_recursive($search);

        $this->init_dao();
        $result = $this->dao_obj->get_total($search);

        if (false === $result) {
            $this->set_error($this->dao_obj->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 根据 id 获得详情
     *
     * @param   int      $id
     * @param   string   $fields
     *
     * @return  array|bool
     */
    public function get_by_id($id, $fields = null)
    {
        data_filter($id);
        data_filter($fields);

        if (empty($id)) {
            $this->set_error(40003);
            return false;
        }

        $search = array(
            $this->id => $id,
        );

        $result = $this->get($search, $fields);

        return $result;
    }

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
        $this->init_dao();
        // --------在内容管理系统中，不需要过滤字符串，增加标识判断，执行完毕后unset;
        if (isset($params['no_filter']) && $params['no_filter']) {
            unset($params['no_filter']);
        }

        if (false == $is_batch) {
            $result = $this->dao_obj->insert($params, $is_insert_id);
        } else {
            $result = $this->dao_obj->insert_batch($params);
        }

        if (false === $result) {
            $this->set_error($this->dao_obj->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 更新数据
     *
     * @access  public
     *
     * @param   array   $set    要更新的信息
     * @param   array   $where  更新的条件
     * @param   int     $limit  更新条数 1 / null
     * @param   array   $extra  额外的更新条件
     *
     * @return  bool
     */
    public function update(array $set, $where = array(), $limit = 1, $extra = array())
    {
        $limit = intval($limit) < 1 ? null : intval($limit);
        $extra = !empty($extra) && is_array($extra) ? $extra : array();

        invalid_data_filter($set, array(null));

        invalid_data_filter($where);

        if (empty($set)) {
            $this->set_error(40001);
            return false;
        }

        if (!is_null($limit) && empty($where)) {
            $this->set_error(40002);
            return false;
        }
        // --------在内容管理系统中，不需要过滤字符串，增加标识判断，执行完毕后unset;
        if (isset($set['no_filter']) && $set['no_filter']) {
            unset($set['no_filter']);
        }

        $this->init_dao();
        $result = $this->dao_obj->update($set, $where, $limit, $extra);

        if (false === $result) {
            $this->set_error($this->dao_obj->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 根据 id 更新信息
     *
     * @param   string   $id        自增ID
     * @param   array    $params    更新参数
     *
     * @return  array|bool
     */
    public function update_by_id($id = null, array $set, $limit = 1)
    {
        if (empty($id) ) {
            $this->set_error(40003);
            return false;
        }

        $where = array(
            $this->id => $id,
        );

        $result = $this->update($set, $where, $limit);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 替换在error_list中定义的message占位符
     *
     * @param   string|int      $error_code
     * @param   string|array    $replace
     *
     * @return  string
     */
    public function replace_error_code($error_code, $replace)
    {
        $this->load->library('error_list');

        if (is_array($replace)) {
            $msg = str_replace(explode(',', '{' . implode("},{", array_keys($replace)) . '}'), $replace, $this->error_list->error_list[$error_code]);
        } else {
            $msg = str_replace('{0}', $replace, $this->error_list->error_list[$error_code]);
        }

        return $msg ?: $error_code;
    }
}
// END Model Class

/* End of file Model.php */
/* Location: ./system/core/Model.php */