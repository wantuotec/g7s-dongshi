<?php
 /**
 * 推送
 *
 * @author      熊飞龙
 * @date        2014-12-25
 * @category    Push.php
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class MY_Push
{
    // 是否走队列
    public $is_via_queue = true;

    // 是否是IOS客户端 3 公司IOS商家端使用 4 公司IOS配送端使用
    public $ios_app_type = array(3, 4, 23, 29, 31);

    // 数据库配置
    private $__log_read  = array('cluster' => 2, 'mode' => 'read');
    private $__log_write = array('cluster' => 2, 'mode' => 'write');

    // 数据表定义
    private $__table_push = 'push';

    // push 当前目录
    protected $_root      = null;

    // SDK 存放目录
    protected $_sdk_root  = null;

    // 实例化 SDK 的句柄
    public $_sdk          = null;

    // sdk 名称
    protected $_sdk_name  = null;

    // sdk 配置文件
    protected $_sdk_config_key = null;

    // 记录错误消息
    private $__error = "";

    public function __construct()
    {
        // 渠道配制所在目录
        $this->_root     = dirname(__FILE__) . DIRECTORY_SEPARATOR;

        // 设置 SDK 根目录
        $this->_sdk_root = $this->_root;
    }

    /**
     * 自动加载 CI 的属性
     */
    public function __get($key)
    {
        $CI = &get_instance();
        return $CI->$key;
    }

    /**
     * 设置错误信息
     */
    public function set_error($msg = '')
    {
        $this->__error = $msg;
    }

    /**
     * 获取错误信息
     */
    public function get_error()
    {
        return $this->__error;
    }

    /**
    * 初始化
    */
    public function initialize($sdk_name, $sdk_config_key, $extra_config = [])
    {
        $class_sdk_name = ucfirst($sdk_name) . '_push';

        // 获取配置文件路径
        $class = $this->_root . 'Push' . DIRECTORY_SEPARATOR . $class_sdk_name. '.php';
        if (file_exists($class)) {
            $this->load->library('Push/'. $class_sdk_name, null, $class_sdk_name);
            $this->_sdk = $this->$class_sdk_name;

            if (!is_object($this->_sdk)) {
                $this->set_error("加载 sdk 对象失败！");
                return false;
            }

            // 检查配置文件是否存在
            $CI = & get_instance();
            $CI->config->load('push', true, true);
            $config = $CI->config->config['push'];

            if (!isset($config[$sdk_name]) || empty($config[$sdk_name]) || empty($config[$sdk_name][$sdk_config_key])) {
                $this->set_error("加载 " . $sdk_name . " 配置失败，或未配置！");
                return false;
            }

            $sdk_config = $config[$sdk_name][$sdk_config_key];
            // 把为null 为空的配置过滤完
            invalid_data_filter_recursive($extra_config);
            $sdk_config = array_merge($sdk_config, $extra_config);

            // 初始 sdk 配置
            if(!$this->_sdk->initialize($sdk_config)){
                $this->set_error($this->_sdk->get_error());
                return false;
            }

            $this->_sdk_name = $sdk_name;
            $this->_sdk_config_key = $sdk_config_key;

        } else {
            $this->set_error('指定的推送 sdk 不存在！');
            return false;
        }

        unset($sdk_name, $sdk_config);

        return true;
    }

    /**
     * 获取队列数据表名
     *
     * @param   string  $suffix 表后缀
     *
     * @param   string
     */
    public function get_push_table($suffix = NULL)
    {
        is_null($suffix) && $suffix = date('ym');
        return $this->__table_push . '_' . $suffix;
    }

    /**
     * 判断某个表是否存在
     *
     * @access  public
     *
     * @param
     * @return  bool
     */
    public function is_table_exists(&$db, $table)
    {
        return $db->query("show tables like '{$table}'")->row_array() ? true : false;
    }

    /**
     * 创建数据表
     *
     * @param   resorce $db     数据库连接
     * @param   string  $table  数据表名
     *
     * @param   string
     */
    public function create_push_table(&$db, $table)
    {
        if (!$this->is_table_exists($db, $table)) {
            $sql = <<<EOT
                CREATE TABLE `{$table}` (
                    `push_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `order_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
                    `push_action` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '操作标识',
                    `push_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'push 类型【1：通知+透传，2：透传】',
                    `push_token` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '推送设备 Id',
                    `token_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'token 的客户端类型 与 app 表 app_id 保持一致',
                    `push_data` TEXT NOT NULL COMMENT 'push 的数据内容',
                    `courier_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '配送员ID',
                    `courier_name` CHAR(50) NOT NULL DEFAULT '' COMMENT '快递员名称',
                    `shop_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商家ID',
                    `shop_name` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '商家名称',
                    `create_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
                    `is_deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '2' COMMENT '是否删除 1是 2否',
                    PRIMARY KEY (`push_id`),
                    INDEX `push_action` (`push_action`),
                    INDEX `order_id` (`order_id`)
                )
                COMMENT='push 记录表'
                COLLATE='utf8_general_ci'
                ENGINE=MyISAM;
EOT;

            $db->query($sql);
        }
    }

    /**
     * 推送消息
     *
     * @param   array   $params
     *
     * @return  bool
     */
    public function send(array $push_list)
    {
        if (empty($push_list) || !is_array($push_list)) {
            $this->set_error('没有需要推送的内容');
            return false;
        }

        // 如果是单条
        if (isset($push_list['push_action'])) {
            $push_list = array($push_list);
        }

        foreach ($push_list as $push_content) {
            // 如果是批量推送 如推送新闻 token_type 会为空，区分不了安卓或者IOS
            // 如果是批量信息，有 content 那么用通知的方式
            // if (empty($push_content['token_type']) && !empty($push_content['content'])) {
            //     $method = 'push_message';
            // }

            if (1 != $push_content['push_type']) {
                $method = 'push_transmission';
            } else {
                $method = 'push_message';
            }

            if(!call_user_func(array($this, $method), $push_content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 推送通知 - 点击打开应用
     *
     * @param   mixed   $cid            接收的设备 【如果为字符串，使用 tag 通道，数组则为多用户推送】
     * @param   string  $title          通知标题
     * @param   string  $content        通知内容
     * @param   string  $instruction    透传指令
     *
     * @return  array|bool
     */
    public function push_message(array $params)
    {
        if (empty($params['push_tokey'])) {
            $this->set_error("未指定接收通知的设备号！");
            return false;
        }

        if (empty($params['title'])) {
            $this->set_error("未指定接收通知的标题信息！");
            return false;
        }

        if (empty($params['content'])) {
            $this->set_error("未指定接收通知的内容信息！");
            return false;
        }

        if (empty($params['sdk_name'])) {
            $this->set_error('请指定推送的SDK名称');
            return false;
        }

        if (empty($params['sdk_config_key'])) {
            $this->set_error('请指定推送配置key');
            return false;
        }

        if(!$this->__transmission_template($params)) {
            return false;
        }

        // push 类型【1：通知+透传，2：透传】
        $params['push_type'] = 1;

        return $this->__add_queue($params);
    }

    /**
     * 推送透传消息
     *
     * @param   mixed   $params['push_tokey']       接收的设备 【如果为字符串，使用 tag 通道，数组则为多用户推送】
     * @param   array   $params['push_data']        通知的内容
     *
     * @return  bool
     */
    public function push_transmission(array $params)
    {
        if (empty($params['push_tokey'])) {
            $this->set_error("未指定接收通知的设备号！");
            return false;
        }

        if (!isset($params['push_data'])) {
            $this->set_error("未指定透传内容！");
            return false;
        }

        if (empty($params['sdk_name'])) {
            $this->set_error('请指定推送的SDK名称');
            return false;
        }

        if (empty($params['sdk_config_key'])) {
            $this->set_error('请指定推送配置key');
            return false;
        }

        if(!$this->__transmission_template($params)) {
            return false;
        }

        // push 类型【1：通知+透传，2：透传】
        $params['push_type'] = 2;

        return $this->__add_queue($params);
    }

    /**
    * 透传消息模板
    */
    private function __transmission_template(array $params)
    {
        if(isset($params['push_data']) && !is_array($params['push_data'])) {
            $this->set_error("push_data 数据必须为数组！");
            return false;
        }

        if(empty($params['push_action'])) {
            $this->set_error("push_action 未指定！");
            return false;
        }

        $expire_time = empty($params['expire_time']) ? 0 : intval($params['expire_time']);
        $expire_time = max($expire_time, 120);

        $result = array(
            'type'        => $params['push_type'],
            'action'      => $params['push_action'],
            'data'        => empty($params['push_data']) ? array() : $params['push_data'],
            'expire_time' => date('Y-m-d H:i:s', time() + $expire_time), // 到期时间（单位：秒）
            'app'         => empty($params['app'])      ? 'all' : $params['app'],      // all shop courier
            'platform'    => empty($params['platform']) ? 'all' : $params['platform'], // all android ios winhpone
            'title'       => empty($params['title'])    ? ''    : $params['title'],
            'content'     => empty($params['content'])  ? ''    : $params['content'],
        );

        return json_encode($result);
    }

    /**
    * 将需要处理的数据放入队列
    */
    private function __add_queue(array $params = array())
    {
        if (empty($params) || empty($params['push_tokey'])) {
            $this->set_error("未提供数据或 push_token 为空！");
            return false;
        }

        // 目前不记录日志 2016-01-13
        // -------------------------------------------- 添加 push 记录 --------------------------------------------
        if (false) {

            $this->load->rwdb($this->__log_write);

            $insert = array(
                'order_id'      => empty($params['order_id'])    ? '' : $params['order_id'],
                'push_action'   => empty($params['push_action']) ? '' : $params['push_action'],
                'push_type'     => empty($params['push_type'])   ? '' : $params['push_type'],
                'push_token'    => '',
                'token_type'    => empty($params['token_type'])  ? '' : $params['token_type'],
                'push_data'     => $this->__transmission_template($params),
                'create_time'   => date('Y-m-d H:i:s'),
                'is_deleted'    => 2,
            );

            is_string($params['push_tokey']) && $params['push_tokey'] = array($params['push_tokey']);

            $push_inserts = array();
            if (is_array($params['push_tokey'])) {
                $params['push_tokey'] = array_unique($params['push_tokey']);

                foreach ($params['push_tokey'] as $val) {
                    $temp = $insert;
                    $temp['push_token']   = empty($val) ? '' : $val;
                    $temp['courier_id']   = isset($params['courier_list'][$val]) && isset($params['courier_list'][$val]['courier_id'])   ? $params['courier_list'][$val]['courier_id']   : '' ;
                    $temp['courier_name'] = isset($params['courier_list'][$val]) && isset($params['courier_list'][$val]['courier_name']) ? $params['courier_list'][$val]['courier_name'] : '' ;
                    $temp['shop_id']      = isset($params['shop_list'][$val])    && isset($params['shop_list'][$val]['shop_id'])         ? $params['shop_list'][$val]['shop_id']         : '' ;
                    $temp['shop_name']    = isset($params['shop_list'][$val])    && isset($params['shop_list'][$val]['shop_name'])       ? $params['shop_list'][$val]['shop_name']       : '' ;
                    $push_inserts[] = $temp;
                }
            } else if (is_string($params['push_tokey'])) {
                $insert['push_token']   = empty($params['push_tokey'])   ? '' : $params['push_tokey'];
                $insert['courier_id']   = empty($params['courier_id'])   ? '' : $params['courier_id'];
                $insert['courier_name'] = empty($params['courier_name']) ? '' : $params['courier_name'];
                $insert['shop_id']      = empty($params['shop_id'])      ? '' : $params['shop_id'];
                $insert['shop_name']    = empty($params['shop_name'])    ? '' : $params['shop_name'];
                $push_inserts[] = $insert;
            }

            if (!empty($push_inserts) && is_array($push_inserts)) {
                // 如果表不存在则自动创建表
                $push_table = $this->get_push_table();
                $this->create_push_table($this->rwdb, $push_table);

                // 写入数据
                $this->rwdb->insert_batch($push_table, $push_inserts);

                // 关闭数据库连接
                $this->rwdb->close();
            }
        }
        // -------------------------------------------- END push 记录 --------------------------------------------

        if ((bool)$this->is_via_queue) {
            // 附加额外参数
            $params['extra_1'] = $params['push_action'];

            $this->load->library('queue');
            $result = $this->queue->send('push', $params);
        } else {
            $result = $this->run($params);
        }

        if (false === $result) {
            $this->set_error((bool)$this->is_via_queue ? $this->queue->get_error() : $this->get_error());
            return false;
        } else {
            return true;
        }
    }

    /**
     * 处理队列 【脚本调用】
     *
     * @param   array   $params 待处理的数据
     *
     * @return  void
     */
    public function run($params)
    {
        // 可以改写一些参数
        $extra_config = [
            'expire_time' => is_null($params['expire_time']) ? null : $params['expire_time'],
        ];

        // 初始化 $this->_sdk 不管什么时候，都重新初始化一下 sdk_config_key 可能每次都不一样
        if(!$this->initialize($params['sdk_name'], $params['sdk_config_key'], $extra_config)) {
            return false;
        }

        // 通过队列发通知+透传消息
        if($params['push_type'] == 1) {

            $success = $this->_sdk->push_open_message($params['push_tokey'],  $params['title'], $params['content'], $this->__transmission_template($params));

        // 通过队列发透传消息
        } else if($params['push_type'] == 2) {

            $success = $this->_sdk->push_transmission($params['push_tokey'], $this->__transmission_template($params));
        } else {

            $this->set_error("未知 push_type 选项 " . $params['push_type']);
            return false;
        }

        if (false === $success) {
            $this->set_error($this->_sdk->get_error());
            return false;
        }

        return $success;
    }
}