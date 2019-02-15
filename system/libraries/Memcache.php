<?php
/**
 * Memcache 操作类
 *
 * @author      willy
 * @date        2012-05-24
 * @copyright   Copyright(c) 2012 
 * @version     $Id: Memcache.php 1 2013-04-12 11:19:06Z 杨海波 $
 */
class CI_Memcache {
    // 可以定义使用 memcache 或 memcached
    private $__extension = 'memcache';
    // 用来保存 memcache 对象
    private $__memcached = null;
    // 用来保存 memcache 配置信息
    private $__config    = null;

    /**
     * 初始化 memcache
     * 
     * @access  public
     * @param   array   $config 自定义 memcache 配置
     * config 格式如下
     *  $config = array(
     *    'default' => array(
     *      'hostname'   => '127.0.0.1',
     *      'port'       => 11211,
     *      'persistent' => false,
     *      'weight'     => 1,
     *      'timeout '   => 10,
     *  ),);
     *
     * @return void
     */
    public function __construct($config = array())
    {
        if (!in_array($this->__extension, array('memcache', 'memcached'))) {
            show_error("The extension just can be memcache or memcached!");
        }

        if (!extension_loaded($this->__extension)) {
            show_error("The {$this->__extension} extension is not loaded!");
        }

        if (empty($config) || !is_array($config)) { // 如果没有定义 从配置文件获取
            $CI =& get_instance();
            if ($CI->config->load('memcached', TRUE, TRUE)) {
                if (is_array($CI->config->config['memcached'])) {
                    $this->__config = $CI->config->config['memcached'];         
                }            
            }
        } else {
            // 如果是一维数据 则先转换成二维数组
            if (isset($config['hostname']) && is_string($config['hostname'])) {
                $config = array($config);
            }
            $this->__config = $config;
        }

        // 初始化 memcache
        $memcache = ucfirst($this->__extension);
        $this->__memcached = new $memcache();

        foreach ($this->__config as $server) {
            if (empty($server['hostname']) || empty($server['port'])) {
                show_error("The {$this->__extension} configure is wrong!");
            } else {
                $server['persistent'] = isset($server['persistent']) ? (bool) $server['persistent'] : false;
                $server['weight']     = isset($server['weight'])     ? intval($server['weight'])    : 1;
                $server['timeout']    = isset($server['timeout'])    ? intval($server['timeout'])   : 10;

                if ('memcache' == $this->__extension) {
                    $this->__memcached->addServer($server['hostname'], $server['port'], $server['persistent'], $server['weight'], $server['timeout']);
                } else if ('memcached' == $this->__extension) {
                    $this->__memcached->addServer($server['hostname'], $server['port'], $server['weight']);
                }
            }
        }
    }

    /**
     * 回调函数
     * 
     * @access  private
     *
     * @param   string  $callback   要回调的函数名称
     * @param   string  $key        元素名称
     * @param   mixed   $data       要存储的数据
     * @param   int     $expire     过期时间 (s)
     *
     * @return  bool
     */
    private function __callback($callback, $key, $data, $expire)
    {
        if ('memcache' == $this->__extension) {
            $params = array($key, $data, 0, $expire);
        } else if ('memcached' == $this->__extension) {
            $params = array($key, $data, $expire);
        } else {
            return false;
        }
        return call_user_func_array(array($this->__memcached, $callback), $params);
    }

    /**
     * 向 memcache 里添加一个元素 （如果该元素已经存在，那么返回 false）
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $data   要存储的数据
     * @param   int     $expire 过期时间 (s)
     *
     * @return  bool
     */
    public function add($key, $data, $expire)
    {
        return $this->__callback('add', $key, $data, $expire);
    }

    /**
     * 向 memcache 里添加一个元素 （如果该元素已经存在，则替换）
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $data   要存储的数据
     * @param   int     $expire 过期时间 (s)
     *
     * @return  bool
     */
    public function set($key, $data, $expire)
    {
        return $this->__callback('set', $key, $data, $expire);
    }

    /**
     * 替换已存在元素的值
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $data   要存储的数据
     * @param   int     $expire 过期时间 (s)
     *
     * @return  bool
     */
    public function replace($key, $data, $expire)
    {
        return $this->__callback('replace', $key, $data, $expire);
    }

    /**
     * 获取一个元素
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     *
     * @return  mixed
     */
    public function get($key)
    {
        // 如果元素不存在则返回 false
        return $this->__memcached->get($key);
    }

    /**
     * 删除 memcache 里的元素
     *
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   int     $time   几秒后删除 0 为立即删除
     *
     * @return  bool
     */
    public function delete($key, $time = 0)
    {
        return $this->__memcached->delete($key, $time);
    }

    /**
     * 删除 memcache 里所有的元素
     * 
     * @access  public
     *
     * @return  bool
     */
    public function flush()
    {
        return $this->__memcached->flush();
    }

    /**
     * 增加一个元素的值 (如果这个元素不存在，不会创建，会返回 false)
     * 如果元素的值不是数字，则会当成数字处理
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $value  要减去的值
     *
     * @return  int|false
     */
    public function increment($key, $value = 1)
    {
        $result = $this->__memcached->increment($key, $value);
        if (false === $result) {
            return false;
        } else {
            if ('memcache' == $this->__extension) {
                return $result; // memcache 模式 直接返回结果
            } else if ('memcached' == $this->__extension) {
                return $this->__memcached->get($key);
            }
        }
    }

    /**
     * 减少一个元素的值 (如果这个元素不存在，不会创建)
     * 如果元素的值不是数字，则会当成数字处理，减完后的值不会小于 0 如果值为 5 - 10 那么还是 0
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $value  要减去的值
     *
     * @return  int|false
     */
    public function decrement($key, $value = 1)
    {
        $result = $this->__memcached->decrement($key, $value);
        if (false === $result) {
            return false;
        } else {
            if ('memcache' == $this->__extension) {
                return $result; // memcache 模式 直接返回结果
            } else if ('memcached' == $this->__extension) {
                return $this->__memcached->get($key);
            }
        }
    }

    /**
     * 获取服务器池的统计信息
     *
     * @return  array
     */
    public function stats()
    {
        return $this->__memcached->getStats();
    }

    /**
     * 关闭连接
     *
     * @access  public
     *
     * @return  bool
     */
    public function __destruct()
    {
        if ('memcache' == $this->__extension) {
            return $this->__memcached->close();
        }

        return true;
    }
}