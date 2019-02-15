<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: Programs_dao.php
 * @create : Apr. 6, 2012
 * @update : Apr. 6, 2012
 */

class Programs_dao extends CI_Dao
{
    //new 
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = '`programs`';
    
    protected $_fields   = '`id`, `systemId`, `sysGroupId`, `progName`, `funcName`, `createTime`, `udateTime`, `sort`, `is_display`, `is_deleted`';

    // 数据表定义
    private $__table_programs  = 'programs';
    
    //查询字段定义
    private $__fields_names    = '`id`,`systemId`,`sysGroupId`,`funcName`,`progName`,`sort`,`is_display`';
    private $__systemId_sysgGr = '`systemId`,`sysGroupId`';
    
    protected $_params;
    protected $_error;

    function __construct()
    {
        parent::__construct();
    }

    /* 返回所有 */
    function get_all()
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT `id`,`systemId`,`sysGroupId`,`funcName`,`progName`,`sort`,`is_display` FROM `programs` WHERE is_deleted=0 ORDER BY `sort` ASC,`systemId` ASC,`sysGroupId` ASC");
        return $query->result_array('funcName');
    }

    /* get_all_by_system_id */
    function get_all_by_system_id($id)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT `sysGroupId`,`progName`,`sort`,`is_display` FROM `programs` WHERE is_deleted=0 AND `systemId` = ?",array('system'=>$id));
        return $query->result_array('sysGroupId');
    }

    /**
     * 按 progName 获取功能列表
     * 
     * @access  public
     * @param   string  $name       progName
     * @param   string  $order_by   排序
     * @return  void
     */
    public function get_program_by_name($name)
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT `systemId`, `sysGroupId`, `progName`, `sort`,`is_display` FROM `programs` WHERE `progName` = '{$name}'");
        return $query->row_array();
    }

    /**
     * 按 system_id 获取功能列表
     * 
     * @access  public
     * @param   int     $id         system_id
     * @param   string  $order_by   排序
     * @return  void
     */
    public function get_programs_by_system_id($id, $order_by = 'sort asc, id asc')
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT `systemId`, `sysGroupId`, `progName`, `sort`,`is_display` FROM `programs` WHERE `systemId` = '{$id}' ORDER BY {$order_by}");
        return $query->result_array('sysGroupId');
    }

    /**
     * 按 group_id 获取功能列表
     * 
     * @access  public
     * @param   int     $id         group_id
     * @param   string  $order_by   排序
     * @return  void
     */
    public function get_programs_by_group_id($id, $order_by = 'sort asc, id asc')
    {
        $this->load->rwdb($this->_db_read);
        $query = $this->rwdb->query("SELECT `systemId`, `sysGroupId`, `progName`, `sort`,`is_display` FROM `programs` WHERE is_deleted=0 AND `systemId` > 0 AND `sysGroupId` = '{$id}' ORDER BY {$order_by}");
        return $query->result_array('systemId');
    }

    /**
     * 根据 ID 修改菜单的信息
     *
     * @access  public
     *
     * @param   $id        int      菜单ID
     * @param   $param     array    修改的数据信息
     *
     * @return  array|false
     */
     public function update_program_by_id($id, array $params)
     {
        $this->load->helper('common');
        data_filter($id);
        data_filter($params);

        $params = array(
            'progName'   => $params['progName'],   // 菜单名称
            'funcName'   => $params['funcName'],   // 入口文件
            'sort'       => $params['sort'],       // 排序
            'is_display' => $params['is_display'], // 是否显示 1显示 0 隐藏
        );

        $this->load->rwdb($this->_db_write);
        if ($this->rwdb->update($this->_table, $params, array('id' => $id))) {
            return true;
        } else {
            $this->_error = '修改菜单信息失败';
            return false;
        }
     }
     
     /**
      * 根据 id 查询一条菜单信息
      *
      * @access     public
      *
      * @param      $id       int     菜单ID
      *
      * @return     $result   array   获取的结果
      */
    public function get_program_by_id($id)
    {
        $this->load->helper('common');
        data_filter($id);

        $params = array(
            'id' => $id,
        );

        $this->load->rwdb($this->_db_read);
        $result = $this->rwdb->select($this->_fields)->where($params)->get($this->_table)->row_array();

        if (false === $result) {
            $this->set_error('数据有误');
            return array();
        } else {
            return $result;
        }
    }

    /**
     * 状态数据
     *
     * @access  public
     *
     * @return  $result     array   数据信息
     */
    public function get_all_status()
    {
        return array(
            'displays' => array(
                '隐藏',
                '显示',
            ),
            'onlines'  => array(
                '上线',
                '下线',
            ),
        );
    }

    /**
     * 根据ID修改菜单显示
     *
     * @access  public
     *
     * @param   $id         int 菜单ID
     * @param   $is_display int 隐藏状态
     *
     * @return  array|false
     */
    public function update_display_by_id($id, $is_display)
    {
        $this->load->helper('common');
        data_filter($id);
        data_filter($is_display);

        $where = array(
            'id'         => $id,
            'is_display' => $is_display,
        );

        $params = array(
            'is_display' => 1 == $is_display ? 0 : 1, // 是否显示 1显示 0 隐藏
        );

        $this->load->rwdb($this->_db_write);
        if ($this->rwdb->update($this->_table, $params, $where)) {
            return true;
        } else {
            $this->_error = '修改失败';
            return false;
        }
        
    }

    /**
     * 添加菜单信息
     *
     * @access  public
     *
     * @params  $params array   参数数组
     *
     * @return  array|false
     */
    public function add_programs(array $params)
    {
        $this->load->helper('common');
        data_filter($params);

        $this->load->rwdb($this->_db_write);

        if ($this->rwdb->insert('programs', $params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取目前最大的组ID
     *
     * @access  public
     *
     * @return  array|false
     */
    public function get_max_sysGroupId()
    {        
        $this->load->rwdb($this->_db_read);

        $result = $this->rwdb->query('select max(`sysGroupId`) as `sysGroupId` from `programs`');
        $result = $result->row_array();

        if (false === $result) {
            $this->_error = '没有此数据表';
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 根据ID 获取systemId 跟 sysgGroupId
     *
     * @access  public
     * 
     * @params  $id     int     菜单ID
     *
     * @return  array|false
     */
    public function get_systemId_sysGroupId_by_id($id)
    {
        $this->load->helper('common');
        data_filter($id);
        
        if ($id < 1) {
            $this->_error = '参数错误';
            return false;
        }

        $this->load->rwdb($this->_db_read);
        $where_sql  = array('id' => $id,);
        $result     = $this->rwdb->select($this->systemId_sysgGr)->where($where_sql)->get($this->_table)->row_array();

        if (false === $result) {
            $this->_error = '没有此数据表';
            return false;
        } else {
            return $result;
        }
    }
    
    /**
     * 根据 sysGroupId 获取progName
     * 
     * @access  public
     * @params  $sysGroupId   int   组ID
     * @return  array|false
     */
    public function get_progName_by_sysGroupId($sysGroupId = 1, $progName)
    {
        $this->load->helper('common');
        data_filter($sysGroupId);

        if ($id < 1) {
            $this->_error = '参数错误';
            return false;
        }
        if (empty($progName)) {
            $this->_error = '菜单名称不能为空';
            return false;
        }

        $this->load->rwdb($this->_db_read);
        $result = $this->rwdb->query('select `progName` from `programs` where `progName` = ? and `sysGroupId` = ? group by `sysGroupId`', array($progName, $sysGroupId));
        $result = $result->row_array();

        if (false === $result) {
            $this->_error = '没有此数据表';
            return false;
        } else {
            return $result;
        }
    }
    /**
     * 根据 sysGroupId 获取 最大的`systemId`
     * 
     * @access  public
     * @params  $sysGroupId   int   组ID
     * @return  array|false
     */
    public function get_maxSystemId_by_sysGroupId($sysGroupId)
    {
        $this->load->helper('common');
        data_filter($sysGroupId);

        if ($sysGroupId < 1) {
            $this->_error = '参数错误';
            return false;
        }

        $this->load->rwdb($this->_db_read);
        $result     = $this->rwdb->query("select max(`systemId`) as `systemId` from `programs` where `sysGroupId` = ? group by `sysGroupId` ", array($sysGroupId));
        $result     = $result->row_array();
        
        if (false === $result) {
            $this->_error = '没有此数据表';
            return false;
        } else {
            return $result;
        }
    }

    /* 返回错误 */
    function get_error()
    {
        return $this->_error;
    }
}
