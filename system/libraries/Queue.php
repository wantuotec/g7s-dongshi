<?php
/**
 * 入队列操作类
 *
 * @author      willy
 * @date        2012-10-08
 * @copyright   Copyright(c) 2012 
 * @version     $Id: Queue.php 1 2013-04-12 11:19:06Z 杨海波 $
 */
class CI_Queue {
    // 错误信息
    private $__error     = null;

    // 配置信息
    private $__config    = null;

    // 消息队列错误信息对照表
    private $__errorList = array(
        // 系统级别错误
        'HTTPSQS_PUT_OK'            => array('status' => true,  'eng' => 'HTTPSQS_PUT_OK',          'chs' => '插入队列成功'),
        'HTTPSQS_PUT_ERROR'         => array('status' => false, 'eng' => 'HTTPSQS_PUT_ERROR',       'chs' => '插入队列失败'),
        'HTTPSQS_PUT_END'           => array('status' => false, 'eng' => 'HTTPSQS_PUT_END',         'chs' => '队列已满'),
        'HTTPSQS_GET_END'           => array('status' => false, 'eng' => 'HTTPSQS_GET_END',         'chs' => '没有未取出的消息队列'),
        'HTTPSQS_RESET_OK'          => array('status' => true,  'eng' => 'HTTPSQS_RESET_OK',        'chs' => '重置队列成功'),
        'HTTPSQS_RESET_ERROR'       => array('status' => false, 'eng' => 'HTTPSQS_RESET_ERROR',     'chs' => '重置队列失败'),
        'HTTPSQS_MAXQUEUE_OK'       => array('status' => true,  'eng' => 'HTTPSQS_MAXQUEUE_OK',     'chs' => '最大队列数量设置成功'),
        'HTTPSQS_MAXQUEUE_CANCEL'   => array('status' => false, 'eng' => 'HTTPSQS_MAXQUEUE_CANCEL', 'chs' => '最大队列数量设置操作将被取消'),
        'HTTPSQS_SYNCTIME_OK'       => array('status' => true,  'eng' => 'HTTPSQS_SYNCTIME_OK',     'chs' => '修改定时刷新间隔时间成功'),
        'HTTPSQS_SYNCTIME_CANCEL'   => array('status' => false, 'eng' => 'HTTPSQS_SYNCTIME_CANCEL', 'chs' => '修改定时刷新间隔时间操作将被取消'),
        'HTTPSQS_ERROR'             => array('status' => false, 'eng' => 'HTTPSQS_ERROR',           'chs' => 'HTTPSQS全局错误'),
        'HTTPSQS_AUTH_FAILED'       => array('status' => false, 'eng' => 'HTTPSQS_AUTH_FAILED',     'chs' => '密码校验失败'),
        // 业务级别错误
        'OTHER_ERROR'               => array('status' => false, 'eng' => 'OTHER_ERROR',             'chs' => '其它错误'),
        'OTHER_CONFIG'              => array('status' => false, 'eng' => 'OTHER_CONFIG',            'chs' => '初始化时必须指定配置信息'),
        'OTHER_HOST'                => array('status' => false, 'eng' => 'OTHER_HOST',              'chs' => '必须指定队列主机'),
        'SOCKET_CREATE'             => array('status' => false, 'eng' => 'SOCKET_CREATE',           'chs' => 'SOCKET 创建失败'),
        'SOCKET_SELECT'             => array('status' => false, 'eng' => 'SOCKET_SELECT',           'chs' => 'SOCKET 选择失败'),
        'SOCKET_POST'               => array('status' => false, 'eng' => 'SOCKET_POST',             'chs' => 'SOCKET POST 数据应为数组'),
        'SOCKET_WRITE'              => array('status' => false, 'eng' => 'SOCKET_WRITE',            'chs' => 'SOCKET 写入失败'),
        'SOCKET_OUT'                => array('status' => false, 'eng' => 'SOCKET_OUT',              'chs' => 'SOCKET 返回值为空'),
        'CURL_POST'                 => array('status' => false, 'eng' => 'CURL_POST',               'chs' => 'CURL POST 数据应为数组'),
    );

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->__error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        $error = isset($this->__errorList[$this->__error]) ? $this->__errorList[$this->__error]['chs'] : $this->__errorList['OTHER_ERROR']['chs'];
        return $error;
    }

    /**
     * 构造函数
     *
     * @access  public
     * @param   string  $config 邮件配置
     *
     * @return  bool
     */
    public function __construct($config = null)
    {
        if (!is_null($config) && is_array($config)) {
            $this->__config = $config;
        }

        if (!empty($this->__config) && is_array($this->__config)) {
            if (empty($this->__config['host'])) {
                $this->set_error('OTHER_HOST');
                return false;
            }

            empty($this->__config['port'])    && $this->__config['port'] = '1218';
            empty($this->__config['charset']) && $this->__config['charset'] = 'utf-8';

            if (empty($this->__config['via']) || !in_array(strtolower($this->__config['via']), array('socket', 'curl'))) {
                $this->__config['via'] = 'socket';
            }

            if (empty($this->__config['method']) || !in_array(strtoupper($this->__config['method']), array('GET', 'POST'))) {
                $this->__config['method'] = 'POST';
            }

            return true;
        } else {
            $this->set_error('OTHER_CONFIG');
            return false;
        }
    }

    /**
     * 使用 socket post 数据 （不是所有的 PHP 环境都有 curl）
     *
     * @param   string  $params 待处理的数据
     *
     * @return string
     */
    private function __socket($params)
    {
        if ($params['method'] == 'GET') {
            if (!empty($params['data']) && is_array($params['data'])) {
                $params['url'] .= '?' . http_build_query($params['data']);
            }
        }

        $urlParts = parse_url($params['url']);
        $urlParts['path'] == '' && $urlParts['path'] = '/';
        $request =  $urlParts['path'] . '?' . $urlParts['query'];
        $fsock   = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if(!$fsock) {
            $this->set_error('SOCKET_CREATE');
            return false;
        }

        socket_set_nonblock($fsock);
        @socket_connect($fsock, $urlParts['host'], $urlParts['port']);
        $ret = socket_select($fd_read = array($fsock), $fd_write = array($fsock), $except = NULL, $params['timeout'], 0);
        if ($ret != 1) {
            @socket_close($fsock);
            $this->set_error('SOCKET_SELECT');
            return false;
        }

        $method = $params['method'] == 'GET' ? 'GET' : 'POST';

        $in  = $method .' ' . $request . " HTTP/1.0\r\n";
        $in .= "Accept: */*\r\n";
        $in .= "User-Agent: Mozilla/5.0\r\n";
        $in .= 'Host: ' . $urlParts['host'] . "\r\n";
        if ($params['method'] != 'GET') {
            if(!$params['data']) {
                $this->set_error('SOCKET_POST');
                return false;
            }
            $postData = $params['data'];
            $in .= "Content-type: application/x-www-form-urlencoded\r\n";
            $in .= 'Content-Length: ' . strlen($postData) . "\r\n";
        }

        $in .= "Connection: Close\r\n\r\n";
        $params['method'] != 'GET' && $in .= $postData . "\r\n\r\n";
        unset($postData);

        if (!@socket_write($fsock, $in, strlen($in))) {
            socket_close($fsock);
            $this->set_error('SOCKET_WRITE');
            return false;
        }
        unset($in);
        socket_set_block($fsock);
        @socket_set_option($fsock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $params['timeout'], 'usec' => 0));
        $out = '';
        while($buff = socket_read($fsock, 2048)){
            $out .= $buff;
        }
        @socket_close($fsock);
        if(!$out){
            $this->set_error('SOCKET_OUT');
            return false;
        }
        $pos  = strpos($out, "\r\n\r\n");
        $body = substr($out, $pos + 4);
        return trim($body);
    }

    /**
     * 使用 curl post 数据
     *
     * @param   string  $params 待处理的数据
     *
     * @return string
     */
    private function __curl($params)
    {
        $ch = curl_init();
        if ($params['method'] == 'GET') {
            if (!empty($params['data']) && is_array($params['data'])) {
                $params['url'] .= '?' . http_build_query($params['data']);
            }
        } else {
            if(!$params['data']) {
                $this->set_error('CURL_POST');
                return false;
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['data']);
        }
        curl_setopt($ch, CURLOPT_URL, $params['url']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 设置最大连接时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
        $error = curl_exec($ch);
        curl_close($ch);
        return trim($error);
    }

    /**
     * 入队列操作
     *
     * @access  public
     *
     * @param   string  $name   队列名称
     * @param   mixed   $data   要保存的数据
     *
     * @return  bool
     */
    public function send($name, $data)
    {
        $new = urlencode(base64_encode(json_encode($data)));
        $data   = array(
            'charset' => $this->__config['charset'],
            'name'    => $name,
            'opt'     => 'put',
            'auth'    => $this->__config['password'],
        );

        
        $url = 'http://' . $this->__config['host'] . ':' . $this->__config['port'] . '/';
        if (strtoupper($this->__config['method']) == 'POST') {
            $url  .= '?' . http_build_query($data);
            $data  = $new;
        } else {
            $data['data'] = $new;
        }

        $params = array(
            'url'     => $url,
            'timeout' => $this->__config['timeout'],
            'method'  => strtoupper($this->__config['method']),
            'data'    => $data,
        );

        $result = call_user_func(array($this, '__' . strtolower($this->__config['via'])), $params);
        if (false !== $result) {
            if (false === $this->__errorList[$result]['status']) {
                $result = isset($this->__errorList[$result]) ? $result : 'OTHER_ERROR';
                $this->set_error($result);
                return false;
            } else {
                return true;
            }
        }

        return $result;
    }

    /**
     * 出队列操作
     *
     * @access  public
     *
     * @param   string  $name   队列名称
     *
     * @return  bool
     */
    public function get($name)
    {
        $data   = array(
            'charset' => $this->__config['charset'],
            'name'    => $name,
            'opt'     => 'get',
            'auth'    => $this->__config['password'],
        );

        $params = array(
            'url'     => 'http://' . $this->__config['host'] . ':' . $this->__config['port'] . '/',
            'timeout' => $this->__config['timeout'],
            'method'  => 'GET',
            'data'    => $data,
        );

        $result = call_user_func(array($this, '__' . strtolower($this->__config['via'])), $params);

        return $result;
    }
}