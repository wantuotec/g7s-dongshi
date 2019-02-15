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
 * CodeIgniter Dao Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Dao {
    // 错误信息
    protected $_ci_dao_error = null;
    // SQL 语句
    protected $_ci_dao_sql   = null;

    /**
     * Constructor
     *
     * @access public
     */
    function __construct()
    {
        log_message('debug', "Dao Class Initialized");
    }

    /**
     * __get
     *
     * Allows daos to access CI's loaded classes using the same
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
        $this->_ci_dao_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_ci_dao_error;
    }

    /**
     * 设置 SQL 信息
     *
     * @param   string  $sql    SQL 语句
     *
     * @return  void
     */
    public function set_sql($sql)
    {
        $this->_ci_dao_sql[] = $sql;
    }

    /**
     * 获取 SQL 信息
     *
     * @return  array
     */
    public function get_sql()
    {
        return $this->_ci_dao_sql;
    }

    /**
     * 将dao中定义的字段字符串组成数组，供新增或更新时验证字段有效性
     *
     * @return  array   字段数组
     */
    public function get_fields()
    {
        return explode(',', str_replace(['`',' ',PHP_EOL], ['','',''],$this->_fields));
    }

    /**
     * 设置数据库配置信息
     *
     * @param   array   $params     参数
     * @param   bool    $is_write   是否是写入
     *
     * @return  array|false
     */
    public function set_database_info($params = array(), $is_write = false)
    {
        $database = ''; // 数据库
        $table    = ''; // 表名
        $fields   = ''; // 字段

        // 数据库信息
        if (empty($params['database'])) {
            $db_name  = false === $is_write ? '_db_read' : '_db_write';
            $database = empty($this->$db_name) ? null : $this->$db_name;
        } else {
            $database = is_array($params['database']) ? $params['database'] : (empty($this->{'_' . $params['database']}) ? null : $this->{'_' . $params['database']});
        }

        if (empty($database) || !is_array($database)) {
            $this->set_error('请配置数据库信息');
            return false;
        }

        // 表名
        if (empty($params['table'])) {
            $table = empty($this->_table) ? null : $this->_table;
        } else {
            $table = is_string($params['table']) ? (empty($this->{'_' . $params['table']}) ? $params['table'] : $this->{'_' . $params['table']}) : null;
        }

        if (empty($table)) {
            $this->set_error('请配置表名');
            return false;
        }

        // 字段
        if (empty($params['fields'])) {
            $fields = empty($this->_fields) ? null : $this->_fields;
        } else {
            $fields = is_array($params['fields']) ? implode(', ', $params['fields']) : (empty($this->{'_' . $params['fields']}) ? $params['fields'] : $this->{'_' . $params['fields']});
        }

        if (empty($fields)) {
            $this->set_error('请配置字段信息');
            return false;
        }

        return array(
            'database' => $database,
            'table'    => $table,
            'fields'   => $fields,
        );
    }

    /**
     * 插入数据 (单条)
     *
     * @access  public
     *
     * @param   array   $params             待插入的信息
     * @param   bool    $return_insert_id   是否返回插入的 ID
     * @param   bool    $is_batch           是否插入多条
     *
     * @return  int|false
     */
    public function insert(array $params, $return_insert_id = false, $is_batch = false)
    {
        // 数据库信息
        $database = $this->set_database_info($params, true);
        if (false === $database) {
            return false;
        } else {
            // 生成变量 $database, $table, $fields
            extract($database, EXTR_OVERWRITE);
        }

        $this->load->rwdb($database);

        if (true === $is_batch) {
            $result = $this->rwdb->insert_batch($table, $params);
        } else {
            $result = $this->rwdb->insert($table, $params);
        }

        // 把 SQL 保存起来
        $this->set_sql($this->rwdb->last_query());

        if (true === $result && true === $return_insert_id && false === $is_batch) {
            return $this->rwdb->insert_id();
        } else {
            return $result;
        }
    }

    /**
     * 插入数据 (多条)
     *
     * @access  public
     *
     * @param   array   $params             待插入的信息
     *
     * @return  int|false
     */
    public function insert_batch(array $params)
    {
        return $this->insert($params, false, true);
    }

    /**
     * 更新数据
     *
     * @access  public
     *
     * @param   array   $set    要更新的信息
     * @param   array   $where  更新的条件
     * @param   array   $limit  更新条数
     * @param   array   $extra  额外的更新条件
     *
     * @return  bool
     */
    public function update(array $set, $where = array(), $limit = null, $extra = array())
    {
        // 数据库信息
        $params = null;
        $database = $this->set_database_info($params, true);
        if (false === $database) {
            return false;
        } else {
            // 生成变量 $database, $table, $fields
            extract($database, EXTR_OVERWRITE);
        }

        if (empty($set) || !is_array($set)) {
            $this->set_error('更新数据不能为空');
            return false;
        }

        $this->load->rwdb($database);
        foreach ($set as $key => $val) {
            // 可能存在值时拼接的或取的某字段的值
            $escape = (0 === strpos($val, 'concat(`') || '`' === $val{0}) ? false : true;
            $this->rwdb->set($key, $val, $escape);
        }

        if (!empty($where) && is_array($where)) {
            $this->rwdb->where($where);
        }

        $like         = !empty($extra['like'])         && is_array($extra['like'])         ? $extra['like']         : array();
        $where_in     = !empty($extra['where_in'])     && is_array($extra['where_in'])     ? $extra['where_in']     : null;
        $where_not_in = !empty($extra['where_not_in']) && is_array($extra['where_not_in']) ? $extra['where_not_in'] : null;
        $or_where     = !empty($extra['or_where'])     && is_array($extra['or_where'])     ? $extra['or_where']     : array();

        if (!empty($like) && is_array($like)) {
            $this->rwdb->like($like);;
        }

        // 处理 where_in
        if (!empty($where_in) && is_array($where_in)) {
            foreach ($where_in as $key => $in) {
                if (!empty($in) && is_array($in)) {
                    $this->rwdb->where_in($key, $in);
                }
            }
        }

        // 处理 where_not_in
        if (!empty($where_not_in) && is_array($where_not_in)) {
            foreach ($where_not_in as $key => $not_in) {
                if (!empty($not_in) && is_array($not_in)) {
                    $this->rwdb->where_not_in($key, $not_in);
                }
            }
        }

        // 处理 or_where
        if (is_array($or_where) && !empty($or_where)) {
            $or_where_sql = array();
            foreach ($or_where as $key => $val) {
                if (isset($val)) {
                    // 这里如果$key为数字，表示$val中拼接了sql语句。
                    // 为了类似 ((a and b ) or (d and c))这种sql
                    if (is_int($key)) {
                        $or_where_sql[] = $val;
                    } else {
                        if (!$this->rwdb->_has_operator($key)) {
                            $key .= ' = ';
                        }
                        // $or_where_sql[] = mysql_escape_string($key) . "'" . mysql_escape_string($val) . "'";
                        $or_where_sql[] = $key . "'" . $val . "'";
                    }
                }
            }
            if (!empty($or_where_sql) && is_array($or_where_sql)) {
                $or_where_sql = '(' . implode(' OR ', $or_where_sql) . ')';
                $this->rwdb->where($or_where_sql);
            }
        }

        if (!empty($limit) && is_numeric($limit) && intval($limit) > 0) {
            $this->rwdb->limit(intval($limit));
        }

        $result = $this->rwdb->update($table);

        // 把 SQL 保存起来
        $this->set_sql($this->rwdb->last_query());

        return $result;
    }

    /**
     * 删除数据
     *
     * @access  public
     *
     * @param   array   $where  更新的条件
     * @param   array   $limit  更新条数
     * @param   array   $extra  额外的更新条件
     *
     * @return  bool
     */
    public function delete($where = array(), $limit = null, $extra = array())
    {
        // 数据库信息
        $database = $this->set_database_info($params, true);
        if (false === $database) {
            return false;
        } else {
            // 生成变量 $database, $table, $fields
            extract($database, EXTR_OVERWRITE);
        }

        if (empty($where) || !is_array($where)) {
            $this->set_error('条件不能为空');
            return false;
        }

        $this->load->rwdb($database);

        if (!empty($where) && is_array($where)) {
            $this->rwdb->where($where);
        }

        $like         = !empty($extra['like'])         && is_array($extra['like'])         ? $extra['like']         : array();
        $where_in     = !empty($extra['where_in'])     && is_array($extra['where_in'])     ? $extra['where_in']     : null;
        $where_not_in = !empty($extra['where_not_in']) && is_array($extra['where_not_in']) ? $extra['where_not_in'] : null;

        if (!empty($like) && is_array($like)) {
            $this->rwdb->like($like);;
        }

        // 处理 where_in
        if (!empty($where_in) && is_array($where_in)) {
            foreach ($where_in as $key => $in) {
                if (!empty($in) && is_array($in)) {
                    $this->rwdb->where_in($key, $in);
                }
            }
        }

        // 处理 where_not_in
        if (!empty($where_not_in) && is_array($where_not_in)) {
            foreach ($where_not_in as $key => $not_in) {
                if (!empty($not_in) && is_array($not_in)) {
                    $this->rwdb->where_not_in($key, $not_in);
                }
            }
        }

        if (!empty($limit) && is_numeric($limit) && intval($limit) > 0) {
            $this->rwdb->limit(intval($limit));
        }

        $result = $this->rwdb->delete($table);

        // 把 SQL 保存起来
        $this->set_sql($this->rwdb->last_query());

        return $result;
    }

    /**
     * 获取列表数据 （多条）
     *
     * @param   array   $params     查询参数
     * @param   bool    $is_row     是否返回单条数据
     * @param   bool    $just_total 是否仅返回总条数
     * @param   bool    $just_list  是否仅返回列表
     *
     * @return  array|false
     */
    public function get($params = array(), $is_row = false, $just_total = false, $just_list = false)
    {
        $result = true === $is_row ? array() : array(
            'list'  => array(),
            'total' => 0,
            'pages' => 0,
        );

        data_filter($params);
        $key_name  = !empty($params['key_name']) ? $params['key_name']                : null;  // 以此做为返回数组中的 KEY
        $is_pages  = isset($params['is_pages'])  ? (bool) $params['is_pages']         : false; // 是否需要分页 默认不分页
        $size      = isset($params['size'])      ? intval($params['size'])            : null;  // 每页条数
        $offset    = isset($params['offset'])    ? max(0, intval($params['offset']))  : null;  // 从多少条开始
        $page_size = isset($params['page_size']) ? intval($params['page_size'])       : null;  // 每页条数
        $page_no   = isset($params['page_no'])   ? max(1, intval($params['page_no'])) : null;  // 页码
        $order_by  = !empty($params['order_by']) ? $params['order_by']                : null;  // 排序
        $group_by  = !empty($params['group_by']) ? $params['group_by']                : null;  // 排序

        $where        = !empty($params['where'])        && is_array($params['where'])        ? $params['where']        : array();
        $escape       = !empty($params['escape'])       && is_array($params['escape'])       ? $params['escape']       : array(); // where中，不需要将值当成字符串的条件
        $or_where     = !empty($params['or_where'])     && is_array($params['or_where'])     ? $params['or_where']     : array();
        $like         = !empty($params['like'])         && is_array($params['like'])         ? $params['like']         : array();
        $right_like   = !empty($params['right_like'])   && is_array($params['right_like'])   ? $params['right_like']   : array();
        $where_in     = !empty($params['where_in'])     && is_array($params['where_in'])     ? $params['where_in']     : null;
        $where_not_in = !empty($params['where_not_in']) && is_array($params['where_not_in']) ? $params['where_not_in'] : null;
        $join         = !empty($params['join'])         && is_array($params['join'])         ? $params['join']         : null; // 要联合查询的表
        $as           = !empty($params['as'])           && isset($params['as'])              ? $params['as']           : null; // $table 别名

        // 数据库信息
        $database = $this->set_database_info($params);
        if (false === $database) {
            return $result;
            // return false;
        } else {
            // 生成变量 $database, $table, $fields
            extract($database, EXTR_OVERWRITE);
        }

        // 不分页
        if (false === $is_pages) {
            unset($offset, $size);
        } else {
            // 如果需要分页
            is_null($offset) && !empty($_GET['offset']) && $offset = max(0 , intval($_GET['offset']));
            is_null($offset) && $offset = 0;

            // 默认页码
            if ((!is_null($offset) && is_null($size)) || (!is_null($size) && $size < 1)) {
                $size = PAGE_SIZE;
            }

            if (!empty($page_size)) {
                $size = $page_size;
            }

            // 如果有页码的情况下
            if (!is_null($page_no) || isset($_GET['page_no'])) {
                is_null($page_no) && !empty($_GET['page_no']) && $page_no = max(1 , intval($_GET['page_no']));
                is_null($page_no) && $page_no = 1;
                $offset = ($page_no - 1) * $size;
            }
        }



        $this->load->rwdb($database);

        // 直接用字段 where
        $escape_where = array();
        foreach ($where as $key => $val) {
            if ('`' === $val{0} || in_array($key, array_keys($escape))) {
                $escape_where[$key] = $val;
                unset($where[$key]);
            }
        }

        // $table 别名
        $table .= empty($as) ? '' : ' AS ' . $as;

        $query[0] = $this->rwdb->select($fields)->from($table)->where($where)->like($like)->like($right_like, null, 'after');

        // 处理联表查询
        if (!empty($join) && is_array($join)) {
            foreach ($join as $join_table => $val) {
                if (!empty($val)) {
                    if (is_array($val) && isset($val['on'])) {
                        empty($val['type']) && $val['type'] = 'left';
                        $val['as'] = empty($val['as']) ? '' : ' AS ' . $val['as'];
                        $query[0]->join($join_table . $val['as'], $val['on'], $val['type']);
                    } else {
                        $query[0]->join($join_table, $val);
                    }
                }
            }
        }

        !empty($escape_where) && $query[0]->where($escape_where, null, false);

        // 处理 where_in
        if (is_array($where_in)) {
            foreach ($where_in as $key => $in) {
                if (!empty($in) && is_array($in)) {
                    $is_in_field = true;  // 是否是查询字符串in某个表中的字段
                    foreach ($in as $in_item) {
                        if ($in_item{0} !== '`') {
                            $is_in_field = false;
                        }
                    }

                    if ($is_in_field) {
                        foreach ($in as $in_item) {
                            $query[0]->where("{$key} in ({$in_item})", null, false);
                        }
                    } else {
                        $query[0]->where_in($key, $in);
                    }
                } else if (!empty($in) && is_string($in) && $in{0} === '`') {  // in数据表中的字段
                    $query[0]->where("{$key} in ({$in})", null, false);
                }
            }
        }

        // 处理 where_not_in
        if (is_array($where_not_in)) {
            foreach ($where_not_in as $key => $not_in) {
                if (!empty($not_in) && is_array($not_in)) {
                    $is_in_field = true;  // 是否是查询字符串in某个表中的字段
                    foreach ($not_in as $not_in_item) {
                        if ($not_in_item{0} !== '`') {
                            $is_in_field = false;
                        }
                    }

                    if ($is_in_field) {
                        foreach ($not_in as $not_in_item) {
                            $query[0]->where("{$key} not in ({$not_in_item})", null, false);
                        }
                    } else {
                        $query[0]->where_not_in($key, $not_in);
                    }
                } else if (!empty($not_in) && is_string($not_in) && $not_in{0} === '`') {  // in数据表中的字段
                    $query[0]->where("{$key} not in ({$not_in})", null, false);
                }
            }
        }

        // 处理 or_where
        if (is_array($or_where)) {
            $or_where_sql = array();
            foreach ($or_where as $key => $val) {
                if (isset($val)) {
                    // 这里如果$key为数字，表示$val中拼接了sql语句。
                    // 为了类似 ((a and b ) or (d and c))这种sql
                    if (is_int($key)) {
                        $or_where_sql[] = $val;
                    } else {
                        if (!$query[0]->_has_operator($key)) {
                            $key .= ' = ';
                        }
                        // $or_where_sql[] = mysql_escape_string($key) . "'" . mysql_escape_string($val) . "'";
                        $or_where_sql[] = $key . "'" . $val . "'";
                    }
                }
            }
            if (!empty($or_where_sql) && is_array($or_where_sql)) {
                $or_where_sql = '(' . implode(' OR ', $or_where_sql) . ')';
                $query[0]->where($or_where_sql);
            }
        }

        // 如果仅需要返回总条数
        if (true === $just_total) {
            $result = $query[0]->count_all_results();
        } else {
            // 不是单行
            if (true !== $is_row) {
                $query[1] = clone($query[0]);
            }

            !empty($size)     && $query[0]->limit($size, $offset);
            !empty($order_by) && $query[0]->order_by($order_by);

            if (!empty($group_by)) {
                $query[0]->group_by($group_by);
                $query[1]->group_by($group_by);
            }

            // 是否返回单条数据
            if (true === $is_row) {
                // $result = $query[0]->get()->row_array();
                $result = $query[0]->limit(1)->get()->row_array();
            } else {
                // 如果需要 total pages
                if (false === $just_list) {
                    $result['total'] = $query[1]->count_all_results();

                    if (empty($size)) {
                        unset($result['pages']);
                    } else {
                        $result['pages'] = ceil($result['total'] / $size);
                    }
                }

                $result['list']  = $query[0]->get()->result_array($key_name);

                if (true === $just_list) {
                    $result = array('list' => $result['list']);
                }
            }
        }

        // 把 SQL 保存起来
        foreach ($query as $object) {
            $this->set_sql($object->last_query());
        }

//        print_r($this->get_sql());
        // 变量销毁
        unset($query, $params, $key_name, $size, $offset, $order_by, $where, $like, $where_in, $where_not_in, $join, $as);

        return $result;
    }

    /**
     * 获取列表数据 （单条）
     *
     * @param   array   $params 查询参数
     *
     * @return  array|false
     */
    public function get_row($params = array())
    {
        return $this->get($params, true);
    }

    /**
     * 获取数据总条数
     *
     * @param   array   $params     查询参数
     * @param   bool    $is_row     是否返回单条数据
     * @param   bool    $just_total 是否仅返回总条数
     *
     * @return  array|false
     */
    public function get_total($params = array())
    {
        return $this->get($params, true, true);
    }

    /**
     * 执行 SQL 语句
     *
     * @access  public
     *
     * @param   string  SQL 语句
     * @param   array   绑定数据
     * @param   bool    是否返回对象
     *
     * @return  mixed
     */
    public function query($sql, $binds = FALSE, $return_object = TRUE)
    {
        // 数据库信息
        $database = $this->set_database_info($params, true);
        if (false === $database) {
            return false;
        } else {
            // 生成变量 $database, $table, $fields
            extract($database, EXTR_OVERWRITE);
        }

        $this->load->rwdb($database);

        $result = $this->rwdb->query($sql, $binds, $return_object);

        // 把 SQL 保存起来
        $this->set_sql($this->rwdb->last_query());

        return $result;
    }

    /**
     * sql的limit段
     *
     * @param   array   $params 参数
     *
     * @return  string
     */
    public static function get_limit_sql($params)
    {
        $offset = intval($params['offset']);

        if (isset($params['size'])) {
            $size = intval($params['size']);
        } else {
            $size = PAGE_SIZE;
        }

        $limit = ' LIMIT '.$offset.','.$size;

        return $limit;
    }
}


/* End of file Dao.php */
/* Location: ./system/core/Dao.php */