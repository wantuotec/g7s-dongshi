<?php
/**
 * 物流查询类
 *
 * @author      willy
 * @date        2013-08-15
 * @copyright   Copyright(c) 2013
 * @version     $Id: Logistics.php 1288 2014-03-18 04:15:01Z 杨海波 $
 */
class CI_Logistics {
    // 错误信息
    protected $_error = null;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI =& get_instance();
        return $CI->$key;
    }

    /**
     * 把对象转换成数组
     *
     * @param   object  $object 要转换的对象
     *
     * @return  void
     */
    protected function _object2array($object)
    {
        $result = array();
        $object = is_object($object) ? get_object_vars($object) : $object;
        foreach ($object as $key => $val) {
            $val = (is_object($val) || is_array($val)) ? $this->_object2array($val) : $val;
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * 工厂方法
     *
     * @access  public
     * @param   string  $code   物流代号
     *
     * @return  bool
     */
    public function factory($code)
    {
        $code = 'Logistics_' . $code;
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Logistics' . DIRECTORY_SEPARATOR . $code . '.php';
        if (file_exists($class)) {
            // 修复 CLI 里一个进程下自动 factory 多个文件后，因 $this->load->library 有同名缓存而导致的后续文件直接使用前文件的类及变量的 BUG add by willy 2014-03-18
            $this->load->library('Logistics/' . $code, null, $code);
            $this->logistics_factory = $this->$code;
            unset($this->$code);

            return $this->logistics_factory;
        }

        // $this->set_error($code . ' is not exists');
        $this->set_error('请登录快递官网追踪');
        return false;
    }

    /**
     * 单个物流单号查询
     *
     * @access  public
     * @param   string  $code   物流代号
     * @param   string  $sn     物流单号
     *
     * @return  array|false
     */
    public function query($code, $sn)
    {
        if (empty($code)) {
            $this->set_error('物流代号不能为空');
            return false;
        }

        if (empty($sn)) {
            $this->set_error('物流单号不能为空');
            return false;
        }

        if (!is_callable(array($this, '_query'))) {
            $this->set_error('_query() function is not defined');
            return false;
        }

        $result = $this->_query($sn);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 多个物流单号查询
     *
     * @access  public
     * @param   string  $code   物流代号
     * @param   mixed   $sn     物流单号 可为数组或字符串 字符串用逗号隔开
     *
     * @return  array|false
     */
    public function query_batch($code, $sn)
    {
        if (empty($code)) {
            $this->set_error('物流代号不能为空');
            return false;
        }

        if (empty($sn)) {
            $this->set_error('物流单号不能为空');
            return false;
        }

        is_string($sn) && $sn = explode(',', $sn);

        if (!is_callable(array($this, '_query_batch'))) {
            $this->set_error('_query_batch() function is not defined');
            return false;
        }

        // 每个接口的批量查询都有最大查询个数
        if (empty($this->_max_query_number)) {
            $this->set_error('批量查询需定义最大查询数量变量_max_query_number');
            return false;
        }

        if ($this->_max_query_number < count($sn)) {
            $this->set_error('批量查询最大查询数量为' . $this->_max_query_number);
            return false;
        }

        $result = $this->query_batch($sn);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
}