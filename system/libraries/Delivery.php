<?php
/**
 * 第三方配送处理类
 *
 * @author      liunian
 * @date        2017-01-04
 * @copyright   Copyright(c) 2017
 * @version     $Id$
 */
class CI_Delivery {
    // 是否走队列
    public $is_via_queue  = true;

    // 配送平台ID
    public $delivery_config_id   = null;

    // 配送平台名称
    public $delivery_config_name = null;

    // 错误信息
    protected $_error     = null;

    // 调式模式
    protected $_is_debug  = false;

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
     * 记录平台日志到数据库
     *
     * @param   string  $title      日志标题
     * @param   mixed   $message    要记录的信息
     *
     * @return  int/false
     */
    protected function _add_log($title = '', $message = '')
    {
        if ((bool) $this->_is_debug) {
            $this->load->dao('base/S_platform_log_dao');

            return $this->S_platform_log_dao->insert(array(
                'platform_id' => intval($this->delivery_config_id),
                'title'       => $title,
                'message'     => (is_array($message) || is_object($message)) ? var_export($message, true) : $message,
            ), false, true);
        }
    }

    /**
     * 工厂方法
     *
     * @access  public
     * @param   string  $code   代号
     *
     * @return  bool
     */
    public function factory($code)
    {
        // 缓存配送平台ID
        $this->delivery_config_id = intval($code);

        // 根据code 获取
        $this->load->model('order/S_delivery_config_model');
        $result = $this->S_delivery_config_model->get_by_id($this->delivery_config_id);

        // 缓存平台名称
        $this->delivery_config_name = $result['delivery_config_name'];

        // 如果是无效状态
        if (1 != $result['is_enabled']) {
            $this->set_error('The delivery_config was disabled');
            return false;
        }

        // 比如是标准或者其它指定类型
        if (!empty($result['platform_code'])) {
            $code = $result['platform_code'];
        }

        $name = 'Delivery';
        $code = $name . '_' . $code;
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $code . '.php';

        if (file_exists($class)) {
            // 修复 CLI 里一个进程下自动 factory 多个文件后，因 $this->load->library 有同名缓存而导致的后续文件直接使用前文件的类及变量的 BUG add by willy 2014-03-18
            $this->load->library($name . '/' . $code, null, $code);
            $this->factory = $this->$code;
            unset($this->$code);

            // 传递数据到子类
            $this->factory->_is_debug    = 1 == $result['is_debug'] ? true : false; // 是否调试模式
            $this->factory->delivery_config_id   = $this->delivery_config_id;
            $this->factory->delivery_config_name = $this->delivery_config_name;
            $this->factory->request_url  = $result['request_url'];
            $this->factory->response_url = $result['response_url'];
            $this->factory->app_key      = $result['key'];
            $this->factory->app_secret   = $result['secret'];
            $this->factory->app_session  = $result['session'];

            return $this->factory;
        }

        $this->set_error('configure is not exists');
        return false;
    }
}