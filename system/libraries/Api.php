<?php
/**
 * API接口 SDK
 *
 * @author      yanghaibo
 * @date        2014-12-20
 * @category    Api
 *
 * @version     $Id$
 */
class CI_Api
{
    // SDK 通用配置
    public $app_key     = '';
    public $app_session = '';
    public $app_secret  = '';
    public $app_version = '';
    public $app_type    = '';
    public $uuid        = '';

    public $method      = '';
    public $timestamp   = '';
    public $format      = 'json';
    public $version     = '1.0';
    public $charset     = 'UTF-8';
    public $sign        = '';
    public $sign_method = 'md5';
    public $params      = '';
    public $is_ssl      = false;  // 是否加密通信

    // 接口地址
    private $__api_uri  = null ;

    // 通用错误
    private $__error    = null;

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        // 接口请求地址
        $CI =& get_instance();
        $CI->config->load('api');

        $this->__api_uri    = empty($this->api_uri)         ? $CI->config->config['api_uri']        : $this->api_uri;
        $this->app_secret   = empty($this->app_secret)      ? $CI->config->config['app_secret']     : $this->app_secret;
        $this->app_key      = empty($this->app_key)         ? $CI->config->config['app_key']        : $this->app_key;
        $this->app_session  = empty($this->app_session)     ? $CI->config->config['app_session']    : $this->app_session;
        $this->app_version  = empty($this->app_version)     ? $CI->config->config['app_version']    : $this->app_version;
        $this->app_type     = empty($this->app_type)        ? $CI->config->config['app_type']       : $this->app_type;
        $this->uuid         = empty($this->uuid)            ? $CI->config->config['uuid']           : $this->uuid;
        $this->charset      = empty($this->charset)         ? $CI->config->config['charset']        : $this->charset;
        $this->format       = empty($this->format)          ? $CI->config->config['format']         : $this->format;
        $this->sign_method  = empty($this->sign_method)     ? $CI->config->config['sign_method']    : $this->sign_method;
        $this->version      = empty($this->version)         ? $CI->config->config['version']        : $this->version;
    }

    /**
     * 单独配置函数
     *
     * @params  array   $params     配置信息
     *
     * @return  void
     */
    public function config(array $params = array())
    {
        $this->__api_uri   = empty($params['api_uri'])     ? $this->api_uri     : $params['api_uri'];
        $this->app_secret  = empty($params['app_secret'])  ? $this->app_secret  : $params['app_secret'];
        $this->app_key     = empty($params['app_key'])     ? $this->app_key     : $params['app_key'];
        $this->app_session = empty($params['app_session']) ? $this->app_session : $params['app_session'];
        $this->app_version = empty($params['app_version']) ? $this->app_version : $params['app_version'];
        $this->app_type    = empty($params['app_type'])    ? $this->app_type    : $params['app_type'];
        $this->uuid        = empty($params['uuid'])        ? $this->uuid        : $params['uuid'];
        $this->charset     = empty($params['charset'])     ? $this->charset     : $params['charset'];
        $this->format      = empty($params['format'])      ? $this->format      : $params['format'];
        $this->sign_method = empty($params['sign_method']) ? $this->sign_method : $params['sign_method'];
        $this->version     = empty($params['version'])     ? $this->version     : $params['version'];
        $this->is_ssl      = empty($params['is_ssl'])      ? $this->is_ssl      : $params['is_ssl'];
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
        $this->__error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->__error;
    }

    /**
     * 设置接口请求地址
     *
     * @param   string  $api_uri   接口请求地址 
     *
     * @return  void
     */
    public function set_uri($api_uri)
    {
        $this->__api_uri = $api_uri;
    }

    /**
     * 设置是否加密通信
     *
     * @param   bool  $is_ssl   是否加密通信
     *
     * @return  void
     */
    public function set_ssl($is_ssl)
    {
        $this->is_ssl = (bool) $is_ssl;
    }

    /**
     * CURL POST 请求接口
     *
     * @param   string  $params     请求参数
     *
     * @return  array
     */
    public function post($params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $this->__api_uri);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($params))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        ob_start();
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code != 200){ //数据未完整读取
            $this->set_error('请求失败，http状态码为' . $http_code);
            return false;
        }

        return $response;
    }

    /**
     * 数据签名
     *
     * @param   array   $params   待签名数据
     * @param   string  $secret   签名密钥
     *
     * @return  array`
     */
    public function sign($params, $secret = '')
    {
        // 初始化参数
        $params = array(
            'method'      => !empty($params['method'])      ? $params['method']      : (!empty($this->method)      ? $this->method      : ''),
            'timestamp'   => !empty($params['timestamp'])   ? $params['timestamp']   : (!empty($this->timestamp)   ? $this->timestamp   : date('Y-m-d H:i:s')),
            'format'      => !empty($params['format'])      ? $params['format']      : (!empty($this->format)      ? $this->format      : ''),
            'version'     => !empty($params['version'])     ? $params['version']     : (!empty($this->version)     ? $this->version     : ''),
            'charset'     => !empty($params['charset'])     ? $params['charset']     : (!empty($this->charset)     ? $this->charset     : ''),
            'sign_method' => !empty($params['sign_method']) ? $params['sign_method'] : (!empty($this->sign_method) ? $this->sign_method : ''),
            'app_key'     => !empty($params['app_key'])     ? $params['app_key']     : (!empty($this->app_key)     ? $this->app_key     : ''),
            'app_session' => !empty($params['app_session']) ? $params['app_session'] : (!empty($this->app_session) ? $this->app_session : ''),
            'app_version' => !empty($params['app_version']) ? $params['app_version'] : (!empty($this->app_version) ? $this->app_version : ''),
            'app_type'    => !empty($params['app_type'])    ? $params['app_type']    : (!empty($this->app_type)    ? $this->app_type    : ''),
            'uuid'        => !empty($params['uuid'])        ? $params['uuid']        : (!empty($this->uuid)        ? $this->uuid        : ''),
            'params'      => !empty($params['params'])      ? $params['params']      : (!empty($this->params)      ? $this->params      : ''),
        );


        /*************** 加密过程 开始 ***************/
        ksort($params);

        // 仅做拼接，不做转码
        $sign = '';
        foreach ($params as $key => $val) {
            $sign .= $key . $val;
        }

        $sign = $secret . $sign . $secret;

        if ('md5' == $params['sign_method']) {
            $sign = md5(md5($sign));
        } else if ('sha1' == $params['sign_method']) {
            $sign = sha1(sha1($sign));
        } else {
            // 默认 md5 加密
            $sign = md5(md5($sign));
        }
        /*************** 加密过程 结束 ***************/

        $params['sign'] = $sign;

        return $params;
    }

    /**
     * 生成请求数据
     *
     * @param   array   $params   待签名数据
     *
     * @return  string
     */
    public function format($params)
    {
        $result = '';

        if ('json' == $params['format']) {
            $result = json_encode($params);
        } else {
            // 默认是 json 以后再加别的
            $result = json_encode($params);
        }

        return $result;
    }

    /**
     * 外部调用请求接口
     *
     * @param   string  $method             请求方法
     * @param   array   $params             请求参数
     * @param   bool    $is_return_origin   是否返回原始输出
     *
     * @return  string
     */
    public function request($method, $params = array(), $is_return_origin = false)
    {
        // 初始化参数
        $params = array(
            'method' => $method,
            'params' => !empty($params) && is_array($params) ? json_encode($params) : (empty($params) ? '' : $params),
        );

        // 生成签名
        $params = $this->sign($params, $this->app_secret);
        // 生成请求数据
        $data   = $this->format($params);

        // 是否需要加密
        if (true === $this->is_ssl) {
            // 用公钥加密
            $CI =& get_instance();
            $CI->load->library('rsa');

            $data = $CI->rsa->encrypt_public($data, true, true);
            if (false === $data) {
                $this->set_error($CI->rsa->get_error());
                return false;
            }
        }

        $result = $this->post($data);

        // 是否需要解密
        if (true === $this->is_ssl) {
            if (!empty($result)) {
                // 用公钥解密
                $CI =& get_instance();
                $CI->load->library('rsa');
                $result = $CI->rsa->decrypt_public($result, true, true);
                if (false === $result) {
                    $this->set_error($CI->rsa->get_error());
                    return false;
                }
            }
        }

        if (true === (bool) $is_return_origin) {
            return $result;
        }

        // 对结果进行解析
        if ('json' == $this->format) {
            $temp = json_decode($result, true);
            if (!empty($temp) && is_array($temp)) {
                $result = $temp;
            }
        } else {
            $this->set_error('不支持此格式');
            return false;
        }

        // 检查是不是一个有效的 JSON 字符串
        if (empty($result) || !is_array($result)) {
            $this->set_error('结果不是一个有效的JSON');
            return false;
        }

        // 结果正确的情况
        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }

    /**
     * 外部调用请求接口 (仅供API 测试工具使用 @retain@ add by willy 2014-12-22)
     *
     * @param   string  $params             请求参数
     *
     * @return  string
     */
    public function request_test(&$params)
    {
        $params['params'] = !empty($params['params']) && is_array($params['params']) ? json_encode($params['params']) : (empty($params['params']) ? '' : $params['params']);
        // 生成签名
        $params = $this->sign($params, $params['app_secret']);

        // 生成请求数据
        $data   = $this->format($params);
// error_log($data . "\n\n", 3, "e:/fdm.txt");
        // 是否需要加密
        if (true === $this->is_ssl) {
            // 用公钥加密
            $CI =& get_instance();
            $CI->load->library('rsa');

            $data = $CI->rsa->encrypt_public($data, true, true);
            if (false === $data) {
                $this->set_error($CI->rsa->get_error());
                return false;
            }
        }

        $result = $this->post($data);

        // 是否需要解密
        if (true === $this->is_ssl) {
            if (!empty($result)) {
                // 用公钥解密
                $CI =& get_instance();
                $CI->load->library('rsa');
                $result_copy = $result;  // 解密失败时，直接返回解密前的数据
                $result = $CI->rsa->decrypt_public($result, true, true);
                if (false === $result) {
                    return $result_copy;
                }
            }
        }

        // 对结果进行解析
        if ('json' == $this->format) {
            $temp = json_decode($result, true);
            if (!empty($temp) && is_array($temp)) {
                $result = $temp;
            }
        } else {
            $this->set_error('不支持此格式');
            return false;
        }

        return $result;
    }
}