<?php
/**
 * 调用服务类
 */
class CI_Requester
{
    // 错误信息
    protected $_error = null;

    // 请求参数是否已检测
    private $_is_checked_params = false;

    // 请求参数
    private $_params = [];

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI = &get_instance();
        return $CI->$key;
    }

    /**
     * 设置请求参数
     *
     * @param  array $params 请求参数 
     */
    public function set_params(&$params)
    {
        $this->config->load('api');
        $config = $this->config->config;

        $this->_params = [
            'app_key'     => isset($params['app_key'])     ? $params['app_key']     : (isset($config['app_key'])     ? $config['app_key']     : ''),
            'app_session' => isset($params['app_session']) ? $params['app_session'] : (isset($config['app_session']) ? $config['app_session'] : ''),
            'app_version' => isset($params['app_version']) ? $params['app_version'] : (isset($config['app_version']) ? $config['app_version'] : ''),
            'app_type'    => isset($params['app_type'])    ? $params['app_type']    : (isset($config['app_type'])    ? $config['app_type']    : ''),
            'charset'     => isset($params['charset'])     ? $params['charset']     : (isset($config['charset'])     ? $config['charset']     : ''),
            'format'      => isset($params['format'])      ? $params['format']      : (isset($config['format'])      ? $config['format']      : ''),
            'method'      => isset($params['method'])      ? $params['method']      : (isset($config['method'])      ? $config['method']      : ''),
            'sign_method' => isset($params['sign_method']) ? $params['sign_method'] : (isset($config['sign_method']) ? $config['sign_method'] : ''),
            'timestamp'   => isset($params['timestamp'])   ? $params['timestamp']   : (isset($config['timestamp'])   ? $config['timestamp']   : ''),
            'version'     => isset($params['version'])     ? $params['version']     : (isset($config['version'])     ? $config['version']     : ''),
            'uuid'        => isset($params['uuid'])        ? $params['uuid']        : (isset($config['uuid'])        ? $config['uuid']        : ''),
            'sign'        => isset($params['sign'])        ? $params['sign']        : (isset($config['sign'])        ? $config['sign']        : ''),
            'ip'          => $this->input->ip_address()
        ];

        if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
            $this->_params['params'] = json_encode($this->input->get());
        } else if ('post' == strtolower($_SERVER['REQUEST_METHOD'])) {
            $post = $this->input->post();
            $this->_params['params'] = isset($post['params']) ? $post['params'] : null;
        }
    }

    /**
     * 验证请求参数信息
     *
     * @return array
     */
    public function check_request_params()
    {
        $error_msg = '';

        if (empty($this->_params['app_type'])) {
            $error_msg = '请填写应用类型';
            return $error_msg;
        }

        if (empty($this->_params['version'])) {
            $error_msg = '请填写应用接口版本';
            return $error_msg;
        }

        return $error_msg;
    }

    /**
     * 单个调用服务接口
     *
     * @param array  $service_info   获取服务需要的信息
     * @param array  $request_params 应用提交的参数
     *
     * @return array | bool
     */
    public function request($service_info, $request_params = [])
    {
        try {
            $this->load->library('error_list');
            $this->load->library('response');
            $ret = $this->response->response_error_info();

            if (!isset($service_info['service_name']) || !is_string($service_info['service_name'])) {
                $ret['errcode'] = 29001;
                $ret['message'] = $this->error_list->error_list[$ret['errcode']];
                return $ret;
            }

            if (!isset($service_info['service_params']) || !is_array($service_info['service_params'])) {
                $ret['errcode'] = 29002;
                $ret['message'] = $this->error_list->error_list[$ret['errcode']];
                return $ret;
            }

            // 设置并验证请求参数
            if (false == $this->_is_checked_params) {
                $this->set_params($request_params);

                // 判断是否有错误信息
                if ($check_result = $this->check_request_params()) {
                    $ret['errcode'] = 29003;
                    $ret['message'] = $check_result;
                    return $ret;
                }

                $this->_is_checked_params = true;
            }

            $service_name    = explode('.', $service_info['service_name']);
            $method          = $service_name[2];
            $model_file_name = 'S_' . $service_name[1] . '_model';
            $model_name      = $service_name[0] . '/' . $model_file_name;

            if (!file_exists(BASEPATH . 'models/' . strtolower($model_name) . '.php')) {
                $ret['errcode'] = 29004;
                $ret['message'] = $this->error_list->error_list[$ret['errcode']];
                return $ret;
            }

            $this->load->model($model_name);

            // 用method_exists判断方法是否存在，为了严谨不使用is_callable去判断，因为存在魔术方法
            if(!method_exists($model_file_name, $method) && 0 !== strpos($method, 'get_by_') && 0 !== strpos($method, 'update_by_')) {
                $ret['errcode'] = 29005;
                $ret['message'] = $this->error_list->error_list[$ret['errcode']];
                return $ret;
            }

            $result  = call_user_func(array($this->$model_file_name, $method), $service_info['service_params']);
            $errcode = $this->$model_file_name->get_error();

            // 还原初始化，以免感染下一次同文件的其它方法的结果 fixbug add by mark
            $this->$model_file_name->set_error(NULL);

            if (!$errcode) { // 如果成功
                $ret = $this->response->response_success_info();
                $ret['data'] = $result;
            } else {

                if (isset($this->error_list->error_list[$errcode])) {

                    $ret['message'] = $this->error_list->error_list[$errcode];
                    // 10000-19999之间的为正确
                    if ($errcode >= 10000 && $errcode < 20000) {
                        $errcode        = 10000;
                        $ret['success'] = true;
                    }
                } else {
                    // 错误码不存在的设置为其它错误
                    $ret['message'] = $errcode;
                    $errcode        = 99999;
                }

                $ret['errcode'] = $errcode;
            }
        } catch (Exception $e) {
            $ret['message'] = $e->getMessage();

            // 记录日志
            $this->add_log(json_encode($ret));
        }

        return $ret;
    }

    /**
     * 批量调用服务接口 （并发调用，各接口没有依赖关系）
     */
    public function batch_request($service_info, $request_params = [])
    {
        $ret = [];

        foreach ($service_info as $key => $value) {
            $ret[$key] = $this->request($value, $request_params);
        }

        return $ret;
    }

    /**
     * 记录日志
     * 
     * @return bool
     */
    private function add_log($content)
    {
        try {
            $dir      = APPPATH . '/logs/';
            $suffix   = '_' . date('Ymd') . '.txt';
            $filename = 'app';
            $filename = $dir . $filename . $suffix;

            $content = '[' . get_date() . ']' . ' [' . $content . ']' . PHP_EOL;
            error_log($content, 3, $filename);
        } catch (Exception $e) {
            
        }

        return true;
    }
}