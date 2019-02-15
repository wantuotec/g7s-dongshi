<?php
/**
 * Redis 操作类
 *
 * @author      Mark
 * @date        2016-04-07
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class CI_Redis extends Redis {
    // 扩展名称
    private $__extension = 'redis';
    // 用来保存配置信息
    private $__config    = null;

    /**
     * 初始化
     * 
     * @access  public
     * @param   array   $config 自定义配置
     * config 格式如下
     *  $config = [
     *      'host'             => '127.0.0.1',
     *      'port'             => 6379,
     *      'timeout'          => 10,  // 单位 s 0 为不限制
     *      'reserved'         => NULL,
     *      'retry_interval'   => 100, // 单位 ms 重连
     *      'password'         => 'password',  
     *      'default_db_index' => 0,   // 默认切换到哪个db
     *      'serializer'       => 1,   // 0 不序列化 1 使用PHP内置的serialize/unserialize 2 使用 igBinary serialize/unserialize
     *  ];
     *
     * @return void
     */
    public function __construct($config = [])
    {
        if (!extension_loaded($this->__extension)) {
            show_error("The {$this->__extension} extension is not loaded!");
        }

        if (!empty($config) && is_array($config)) {
            $this->__config = $config;
        } else { // 如果没有定义 从配置文件获取
            $CI =& get_instance();
            if ($CI->config->load('redis', TRUE, TRUE)) {
                if (is_array($CI->config->config['redis'])) {
                    $this->__config = $CI->config->config['redis'];         
                }            
            }
        }

        // host 配置至少要存在
        if (empty($config['host'])) {
            show_error("The {$this->__extension} configure is wrong");
        }

        try {
            // 初始化
            $this->connect($config['host'], $config['port'], $config['timeout'], $config['reserved'], $config['retry_interval']);

            // 如果需要认证
            if (isset($config['password']) && strlen($config['password']) > 0) {
                $result = $this->auth($config['password']);

                if (true !== $result) {
                    throw new Exception("password is wrong", 1);
                }
            }

            // 切换到默认数据库
            $config['default_db_index'] = max(0, isset($config['default_db_index']) ? intval($config['default_db_index']) : 0);
            $result = $this->select($config['default_db_index']);

            if (true !== $result) {
                throw new Exception("redis db {$config['default_db_index']} is not exist", 2);
            }

            // 序列化工具
            if (isset($config['serializer']) && '' !== $config['serializer']) {
                $this->setOption(Redis::OPT_SERIALIZER, $config['serializer']);
            }

        } catch (Exception $e) {
            show_error(" Redis Exception: " . $e->getMessage());
        }
    }

    /**
     * String 添加一个元素 （如果该元素已经存在，则替换）
     * 
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   string  $data   要存储的数据
     * @param   int     $expire 过期时间 (s)
     *
     * @return  bool
     */
    // public function set($key, $value, $expire = 0)
    // {
    //     return parent::set($key, $value, $expire);
    //     // return $this->__obj->set($key, $value);
    // }

    /**
     * CAS 实现
     * 
     * @access  public
     *
     * @param   float   $cas_token  与已存在元素关联的唯一的值
     * @param   string  $key        元素名称
     * @param   mixed   $data       要存储的数据
     * @param   int     $expire     过期时间 (s)
     *
     * @return  bool
     */
    public function cas($key, $data, $expire)
    {
        // 以下为 redis CAS 的示例代码
        /*
        $key='b01';
        $subkey='bs01';
        do {
            $redis->watch($key);

            $val = $redis->hget($key, $subkey);
            empty($val) && $val = 0;
            $val++;

            $redis->multi();
            $result = $redis->hset($key, $subkey, $val);
            $result = $redis->exec();
        } while (false == $result);
        */
    }

    /**
     * 添加一个锁
     *
     * @access  public
     *
     * @param   string  $key    元素名称
     * @param   mixed   $value  要存储的数据
     * @param   int     $expire 过期时间 (s)
     *
     * @return  bool
     */
    public function lock($key, $value, $expire)
    {
        return $this->set('lock:' . $key, $value, ['nx', 'ex' => $expire]);
    }

    /**
     * 获取一个锁的值
     *
     * @access  public
     *
     * @param   string  $key    元素名称
     *
     * @return  bool
     */
    public function get_lock($key)
    {
        return $this->get('lock:' . $key);
    }

    /**
     * 删除锁
     *
     * @access  public
     *
     * @param   string  $key    元素名称
     *
     * @return  bool
     */
    public function unlock($key)
    {
        return $this->delete('lock:' . $key);
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
        $this->close();
        return true;
    }
}