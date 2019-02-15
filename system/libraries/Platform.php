<?php
/**
 * 平台统一处理类
 *
 * @author      杨海波
 * @date        2015-05-09
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class CI_Platform {
    // 是否走队列
    public $is_via_queue  = true;

    // 平台ID
    public $platform_id   = null;

    // 平台名称
    public $platform_name = null;

    // 平台推送地址
    public $request_url   = null;

    // 平台 app_key
    public $app_key       = null;

    // 平台 app_secret
    public $app_secret    = null;

    // 平台 app_session
    public $app_session   = null;

    // GPS 类型
    // 1：GPS设备获取的角度坐标;
    // 2：GPS获取的米制坐标、sogou地图所用坐标;
    // 3：google地图、soso地图、aliyun地图、mapabc地图和amap地图所用坐标
    // 4：3中列表地图坐标对应的米制坐标
    // 5：百度地图采用的经纬度坐标
    // 6：百度地图采用的米制坐标
    // 7：mapbar地图坐标;
    // 8：51地图坐标
    public $gps_type      = [1, 2, 3, 4, 5, 6, 7, 8];

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
     * 把xml转换成数组
     *
     * @param   object  $object 要转换的对象
     *
     * @return  void
     */
    protected function xml2array($xml)
    {
        return @json_decode(json_encode(simplexml_load_string($xml)), true);
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
            $this->load->model('Platform_log_model');

            return $this->Platform_log_model->add(array(
                'platform_id' => intval($this->platform_id),
                'title'       => $title,
                'message'     => (is_array($message) || is_object($message)) ? var_export($message, true) : $message,
            ), false, true);
        }
    }

    /**
     * 使用 curl 发起网络请求
     *
     * @param   string  $url        请求地址
     * @param   array   $params     请求参数
     * @param   string  $method     请求方式
     * @param   int     $timeout    超时时间
     * @param   bool    $is_ca      https下是否使用CA根证书来校验
     *
     * @return  string/false
     */
    protected function _curl($url, $params = array(), $method = 'GET', $timeout = 30, $is_ca = false)
    {
        // 是否是 https
        $is_ssl = substr($url, 0, 8) == 'https://' ? true : false;
        // https下是否使用CA根证书来校验
        if (true === $is_ca) {
            // 以下证书暂时不起效果
            $cacert = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR . 'ca/') . DIRECTORY_SEPARATOR . 'cacert.pem';
        }

        $data = '';
        if (!empty($params) && is_array($params)) {
            // files[0]=1&files[1]=2&... 转成 files[]=1&files[]=2&...
            $data = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($params));
        }

        $method = strtoupper($method);

        $ch = curl_init();

        if ('GET' == $method) {
            $url .= empty($data) ? '' : '?' . $data;
        } else {
            if(empty($data)) {
                $this->set_error('no data');
                return false;
            }

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        // todo liunian 2015-05-22 加上此信息，访问接口会很慢
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 设置最大连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout * 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        // https 一种是标准CA证书，一种是自发认证
        if ($is_ssl && $is_ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);      // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // 检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if ($is_ssl && !$is_ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
        }

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $http_error = curl_error($ch);

        // 数据未完整读取, 网络请求超时
        if(0 === $http_code || false === $response) {
            $this->set_error('请求失败' . $http_error);
            return false;
        }

        // 请求成功，返回正确，没有返回JSON 结构体
        if ('' === $response && 200 == $http_code) {
            return true;
        }

        return $response;
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
        // 缓存平台ID
        $this->platform_id = intval($code);

        // 如果是标准接口
        $this->load->model('Platform_model');
        $result = $this->Platform_model->get_by_id($this->platform_id, 'platform_name, is_enabled, platform_code, request_url, is_debug');

        // 缓存平台名称
        $this->platform_name = $result['platform_name'];

        // 如果是无效状态
        if (1 != $result['is_enabled']) {
            $this->set_error('The platform was disabled');
            return false;
        }

        // 比如是标准或者其它指定类型
        if (!empty($result['platform_code'])) {
            $code = $result['platform_code'];
        }

        $name = 'Platform';
        $code = $name . '_' . $code;
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $code . '.php';
        if (file_exists($class)) {
            // 修复 CLI 里一个进程下自动 factory 多个文件后，因 $this->load->library 有同名缓存而导致的后续文件直接使用前文件的类及变量的 BUG add by willy 2014-03-18
            $this->load->library($name . '/' . $code, null, $code);
            $this->factory = $this->$code;
            unset($this->$code);

            // 传递数据到子类
            $this->factory->_is_debug     = 1 == $result['is_debug'] ? true : false; // 是否调试模式
            $this->factory->platform_id   = $this->platform_id;
            $this->factory->platform_name = $this->platform_name;

            // 比如是标准或者其它指定类型
            if (!empty($result['platform_code'])) {
                $this->load->model('App_model');
                $app = $this->App_model->get_by_platform_id($this->platform_id, 'app_key, app_secret, app_session');

                // 传递数据到子类
                !empty($result['request_url']) && $this->factory->request_url = $result['request_url'];
                !empty($app['app_key'])        && $this->factory->app_key     = $app['app_key'];
                !empty($app['app_secret'])     && $this->factory->app_secret  = $app['app_secret'];
                !empty($app['app_session'])    && $this->factory->app_session = $app['app_session'];
            }

            return $this->factory;
        }

        $this->set_error('configure is not exists');
        return false;
    }

    /**
     * 发送请求
     *
     * @access  public
     *
     * @param   mixed   $mobile         手机号(可以是字符串或者数组)
     * @param   bool    $is_via_queue   是否走队列
     *
     * @return  mixed
     */
    public function send($params = array(), $is_via_queue = null)
    {
        if (empty($params['callback'])) {
            $this->set_error('callback不能为空');
            return false;
        }

        $is_via_queue = is_null($is_via_queue) ? (bool) $this->is_via_queue : (bool) $is_via_queue;

        // 是否走队列
        if (true === $is_via_queue) {
            $extra = array(
                'platform_id' => $this->platform_id,
                'extra_1'     => $params['callback'],
                'extra_2'     => '',
            );

            $this->load->library('queue');
            $result = $this->queue->send('platform', array_merge($params, $extra));
        } else { // 直接调用
            $result = call_user_func(array($this, $params['callback']), $params);
        }

        if (false === $result) {
            $this->set_error(true === $is_via_queue ? $this->queue->get_error() : $this->get_error());
            return false;
        } else {
            return $result;
        }
    }
}