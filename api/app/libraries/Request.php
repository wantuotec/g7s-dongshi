<?php
/**
 * Request 接口抽象类
 *
 * @author      yanghaibo
 * @date        2014-12-20
 * @category    Request
 *
 * @version     $Id$
 */
class MY_Request
{
    // 蛙笑
    const WAXIAO = 'waxiao';

    // 错误信息
    protected $_error     = null;
    // 替换数据
    protected $_replace   = null;
    // PHP 错误
    protected $_php_error = null;

    // 返回的数据
    protected $_data      = null;

    // 响应格式
    protected $_format = 'json'; // @todowilly@ 2014-12-20

    /*** 预定义变量 **/
    // 允许的响应格式
    protected $_allow_format      = array('json');

    // 允许的接口版本
    protected $_allow_version     = array('1.0');

    // 允许的字符编码
    protected $_allow_charset     = array('UTF-8');

    // 允许的加密类型
    protected $_allow_sign_method = array('md5');

    // 允许的字段
    protected $_allow_field       = array('method', 'timestamp', 'format', 'version', 'charset', 'sign', 'sign_method', 'app_key', 'app_session', 'params');

    // 错误消息
    protected $_error_list        = null;

    // 不允许log 日志记录的接口
    protected $_not_allow_method  = array('courier.portrait');

    // 是否参数加密
    protected $is_ssl             = true;

    // request文件夹
    private $request_folders      = [
        '6a9ea58cb00d97feef5189e7bfda2e70' => self::WAXIAO
    ];

    // 判断是否为蛙笑访问
    private $is_waxiao = false;

    // 初始化信息
    public function __construct()
    {
        // 设置错误信息
        $this->load->library('error_list');
        $this->_error_list = $this->error_list->error_list;
    }

    /**
     * 获取定义的所有的错误信息列表
     *
     * @return  array
     */
    public function get_error_list()
    {
        return $this->_error_list;
    }

    /**
     * 获取替换后的message
     *
     * @param   $message    string  message_code
     *
     * @return  string
     */
    public function get_replace_message($message)
    {
        $replace = $this->get_replace();
        if (!is_null($replace) && is_array($replace)) {
            $search = array();
            foreach ($replace as $key => $val) {
                $search[] = '{' . $key . '}';
            }
            $message = str_replace($search, array_values($replace), $message);
            unset($search);
        }
        unset($replace);

        return $message;
    }

    /**
     * 设置错误信息
     *
     * @param   string $error_code  错误信息
     * @throws  Exception
     *
     * @return  void
     */
    public function set_error($error_code)
    {
        // 如果是子类，则抛出异常
        if (is_subclass_of($this, __class__)) {
            $this->_error = $error_code;
            throw new Exception('子类设置了错误代码');
        }

        // 以下代码顺序不能错
        $errcode = isset($this->_error_list[$error_code]) ? intval($error_code) : 99999;
        $success = (10000 == $errcode) ? true  : false;
        $errcode = (10000 == $errcode) ? 10000 : $errcode;
        $message = isset($this->_error_list[$error_code]) ? $this->_error_list[$error_code] : $error_code; // $error_code 可能是文字信息，不是代码
        $data    = is_null($this->get_data())   || !is_array($this->get_data())   ? (object)[]  : $this->get_data();

        // 替换 message 里的替位符
        $message = $this->get_replace_message($message);

        // 蛙笑的返回值做特殊处理
        if ($this->is_waxiao) {
            $waxiao_msg = '';
            if (is_array($data) && filter_empty('msg', $data) && is_array($data['msg'])) {
                $waxiao_msgs = [];
                foreach ($data['msg'] as $i => $msg) {
                    $msg_prefix = $msg_suffix = '';
                    // 用英文冒号为分隔，$msg_prefix为具体错误所对应的ID,$msg_suffix为具体的错误
                    if (false !== strpos($msg, ':')) {
                        $msg_prefix = substr($msg, 0, strpos($msg, ':') + 1);
                        $msg_suffix = ltrim($msg, $msg_prefix);
                    } else {
                        $msg_suffix = $msg;
                    }
                    $msg_suffix = isset($this->_error_list[$msg_suffix]) ? $this->_error_list[$msg_suffix] : $msg_suffix; // $msg 可能是文字信息，不是代码
                    if (filter_empty('replace', $data) && is_array($data['replace']) && isset($data['replace'][$i])) {
                        $this->set_replace($data['replace'][$i]);
                        $msg_suffix = $this->get_replace_message($msg_suffix);
                    }
                    $waxiao_msgs[] = $msg_prefix . $msg_suffix;
                }
                $waxiao_msg = implode(',', $waxiao_msgs);
            }
            $this->_error = [
                'ret' => (is_array($data) && isset($data['ret'])) ? $data['ret'] : ($success ? 200 : 400),
                'msg' => !empty($waxiao_msg) ? $waxiao_msg : $message,
            ];
            if (is_array($data) && isset($data['replace'])) {
                unset($data['replace']);
            }
            if (is_array($data)) {
                $this->_error = array_merge($data, $this->_error);
            }
        } else {
            $this->_error = array(
                'success' => $success,
                'errcode' => $errcode,
                'message' => $message,
                'data'    => $data,
            );
        }

        $this->_replace = null;

        if ('json' == $this->_format) {
            header('Content-type: application/json');
        }

//error_log(var_export($this->_error, true) . "\n\n", 3, "e:/52xianmi.txt");
// error_log(json_encode($this->_error) . "\n\n", 3, "e:/52xianmi.txt");
// dump($this->_error);
// dump(json_encode($this->_error));exit;

        $result = json_encode($this->_error);

        if ($this->is_ssl) {
            // 加密所有返回数据
            $this->load->library('rsa');
            $result = $this->rsa->encrypt_private($result, true, true);
            if (false === $result) {
                exit($this->rsa->get_error());
            }
        }

        exit($result);
    }

    /**
     * 获取错误信息
     *
     * @return  array
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * 设置替换数据
     *
     * @return  array
     */
    public function set_replace()
    {
        $params = func_get_args();

        if (empty($params) || !is_array($params)) {
            $this->_replace = null;
        }

        // 如果 params 第一个参数是数组，那么表示输入的参数是数组 否则表示是多个参数
        if (is_array($params[0])) {
            $this->_replace = $params[0];
        } else {
            $this->_replace = $params;
        }
    }

    /**
     * 获取替换数据
     *
     * @return  array
     */
    public function get_replace()
    {
        return $this->_replace;
    }

    /**
     * 设置返回的数据
     *
     * @param   mixed   $data   要返回的数据
     *
     * @return  array
     */
    public function set_data($data = null)
    {
        if (empty($data)) {
            $data = (object)[];
        }

        $this->_data = $data;
    }

    /**
     * 获取返回的数据
     *
     * @return  array
     */
    public function get_data()
    {
        return $this->_data;
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
     * 程序运行结束时要执行函数
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __shutdown($params)
    {
        // 是否成功
        $success = !empty($params['response']) && !empty($params['response']['success']) && true === $params['response']['success'] ? 1 : 2;

        // 正式环境日志太大，目前只记录错误的日志，成功就不记录了
        // 但是如果是蛙笑的访问记录则都记录下来
        if ('production' === ENVIRONMENT && 1 === $success && !$this->is_waxiao) {
            return;
        }

        // 初始化
        if (empty($params['request']) || !is_array($params['request'])) {
            $params['request'] = array();
        }

        // 初始化
        if (empty($params['response']) || !is_array($params['response'])) {
            $params['response'] = array();
        }

        // 初始化
        $data = array(
            'method'       => empty($params['request']['method'])      ? '' : $params['request']['method'],
            'timestamp'    => empty($params['request']['timestamp'])   ? '' : $params['request']['timestamp'],
            'format'       => empty($params['request']['format'])      ? '' : $params['request']['format'],
            'version'      => empty($params['request']['version'])     ? '' : $params['request']['version'],
            'charset'      => empty($params['request']['charset'])     ? '' : $params['request']['charset'],
            'sign'         => empty($params['request']['sign'])        ? '' : $params['request']['sign'],
            'sign_method'  => empty($params['request']['sign_method']) ? '' : $params['request']['sign_method'],
            'app_key'      => empty($params['request']['app_key'])     ? '' : $params['request']['app_key'],
            'app_session'  => empty($params['request']['app_session']) ? '' : $params['request']['app_session'],
            'app_version'  => empty($params['request']['app_version']) ? '' : $params['request']['app_version'],
            'app_type'     => empty($params['request']['app_type'])    ? '' : $params['request']['app_type'],
            'uuid'         => empty($params['request']['uuid'])        ? '' : $params['request']['uuid'],
            'errcode'      => empty($params['response']['errcode'])    ? '' : $params['response']['errcode'],
            'message'      => empty($params['response']['message'])    ? '' : $params['response']['message'],
            'from_ip'      => $this->input->ip_address(),
            'execute_time' => (microtime(true) - $params['run_begin']) * 1000, // 转换成毫秒
            'request'      => (is_string($params['origin']) && is_array($params['request'])) ? json_encode($params['request']) : $params['origin'],
            'response'     => empty($params['response']) ? '' : json_encode($params['response']),
            'success'      => $success,
            'create_time'  => date('Y-m-d H:i:s'),
        );

        // 捕获PHP内部错误 如 WARNING 等
        if (!empty($params['php_error']) && is_string($params['php_error'])) {
            $data['message'] .= ' ' . $params['php_error'];
        }

        // 捕获PHP内部错误 如 E_ERROR 等
        $last_error = error_get_last();
        if (NULL !== $last_error && is_array($last_error)) {
            $data['errcode'] = 99998;
            $data['message'] .= ' ' . strip_tags('type:' . $last_error['type'] . ' Message:' . $last_error['message'] . ' file:' . $last_error['file'] . ' line:' . $last_error['line']);
        }

        // 不允许的接口不记录日志
        if (!in_array($data['method'], $this->_not_allow_method)) {
            $this->load->model('Api_log_model');
            $this->Api_log_model->add($data);
        }
    }


    /**
     * 运行接口代理
     *
     * @return  mixed
     */
    public function run()
    {
        if (!$this->input->is_post_request()) {
            $this->set_error(20029);
        }

        $request = trim(file_get_contents("php://input"));
//         $request = <<< EOT
// {"app_key":"a4c6be681f1295f467ead5d356e2ba10","app_session":"5c4dcc9cbc86a09a65db06abb9893d3c","charset":"UTF-8","format":"json","method":"Example.add","params":"","sign_method":"md5","timestamp":"2015-04-22 14:51:56","version":"1.0","sign":"bb0c733eab16b3bc23c9c9bdbe6c3439"}
// EOT;

//error_log($request . "\n\n", 3, "e:/52xianmi.txt");
// var_dump($request);exit;
        // 程序运行结束时要执行函数
        // 里面涉及到的变量请不要随便修改
        register_shutdown_function(array($this, '__shutdown'), array(
            'run_begin' => microtime(true),
            'origin'    => $request,  // 记录原始信息，所以没有 & 符号
            'request'   => &$request,
            'response'  => &$this->_error,
            'php_error' => &$this->_php_error,
        ));

        if (empty($request)) {
            $this->set_error(20001);
        }

        // 如果请求参数不是json,则视为加密字串(接口测试工具过来的数据，一般不加密)
        if (is_json($request)) {
            $this->is_ssl = false;
        }

        if ($this->is_ssl) {
            // 用私钥解密
            $this->load->library('rsa');
            $request = $this->rsa->decrypt_private($request, true, true);
            if (false === $request) {
                $this->set_replace($this->rsa->get_error());
                $this->set_error(47021);
            }
        }

        // 如果是本地环境，使从api_log表中保存的request值可以直接使用
        // 保留从我们的测试工具的app_version等信息，而不是使用api_log表中保存的request值中的app_version等信息
        if (ENVIRONMENT == 'development') {
            if (false !== strpos($request, 'params')) {
                $request_decode = json_decode($request, true);
                $params_string = $request_decode['params'];
                if (false !== strpos($params_string, 'params')) {
                    $params_string2 = json_decode($params_string, true)['params'];
                    $request_decode['params'] = $params_string2;
                    $request = json_encode($request_decode);
                }
            }
        }

        $request = json_decode($request, true);

        // 检查是不是一个有效的 JSON 字符串
        if (empty($request) || !is_array($request)) {
            $this->set_error(20002);
        }

        /*
         * 处理IOS版本提交到苹果官方审核未通过的时候走test环境 todo liunian 2017-08-11 development testing production
         * app_type = 34,36 只有IOS版本
         */
        if (ENVIRONMENT == 'production' && in_array($request['app_type'], [34, 36])) {
            $this->request_test_api($request);
        }

        // 判断必要字段是否都存在
        $check_fields = array_keys($request);
        foreach ($this->_allow_field as $field) {
            if (!in_array($field, $check_fields)) {
                // 把字段名附加到错误信息中，方便调试
                $this->set_replace($field);
                $this->set_error(20003);
            }
        }
        unset($check_fields);

        // 阻止同一请求因为APP的重试策略，多次并发发送的原因
        if (empty($request['sign'])) {
            $this->set_error(20012);
        } else {
            // todo madesheng 目前未安装memcache服务，暂时取消此限制
            // $this->load->library('memcached');
            // $result = $this->memcached->lock('rest_request_' . $request['sign'], 1, 8);
            // // 为 false 表示重复的并发请求
            // if (false === $result) {
            //     $this->set_error(20031);
            // }
        }

        // 判断接口名称是否为空
        if (empty($request['method'])) {
            $this->set_error(20004);
        }

        // 判断调用时间是否为空
        if (empty($request['timestamp']) || !is_date($request['timestamp'])) {
            $this->set_error(20005);
        }

        // 判断是不是正确的响应格式
        if (empty($request['format']) || !in_array($request['format'], $this->_allow_format)) {
            $this->set_error(20006);
        }

        // 判断是不是正确的版本号
        if (empty($request['version']) || !in_array($request['version'], $this->_allow_version)) {
            $this->set_error(20007);
        }

        // 判断是不是正确的字符编码
        if (empty($request['charset']) || !in_array($request['charset'], $this->_allow_charset)) {
            $this->set_error(20008);
        }

        // 判断是不是正确的加密方式
        if (empty($request['sign_method']) || !in_array($request['sign_method'], $this->_allow_sign_method)) {
            $this->set_error(20009);
        }

        // 判断 app_key 是否为空
        if (empty($request['app_key'])) {
            $this->set_error(20010);
        }

        // 判断 app_session 是否为空
        if (empty($request['app_session'])) {
            $this->set_error(20011);
        }

        // 获取 APP 设置
        $this->load->model('App_model');
        $app_config = $this->App_model->get_by_key($request['app_key']);

        // 如果没有此 app_key 请求非法
        if (empty($app_config)) {
            $this->set_error(20017);
        }

        // 是否有效(1 有效， 2 无效)
        if (2 == $app_config['is_enabled']) {
            $this->set_error(20018);
        }

        // app_session 错误
        if ($request['app_session'] != $app_config['app_session']) {
            $this->set_error(20019);
        }

        // app_session 已过期
        if (time() > strtotime($app_config['session_expire_time'])) {
            $this->set_error(20020);
        }

        // 判断
        if (empty($request['sign'])) {
            $this->set_error(20012);
        }

        // 解析参数
        $params = array();

        if (!empty($request['params']) && is_string($request['params'])) {
            // 解析为参数
            $params = json_decode($request['params'], true);

            // 检查是不是一个有效的 JSON 字符串
            if (is_null($params) || !is_array($params)) {
                $this->set_error(20014);
            }
        }

        // request folder
        $request_folder = '';
        if (array_key_exists($app_config['app_key'], $this->request_folders)) {
            $request_folder = $this->request_folders[$app_config['app_key']] . '/';

            // 如果是蛙笑，对应的返回值需要做特殊处理
            if ($this->request_folders[$app_config['app_key']] === self::WAXIAO) {
                $this->is_waxiao = true;
            }
        }

        $method = explode('.', strtolower($request['method']));

        // 参数一和参数二一定要存在，并且是字母开头
        if (empty($method[0]) || empty($method[1]) || !preg_match('/^[a-zA-Z]/', $method[0]) || !preg_match('/^[a-zA-Z]/', $method[1])) {
            $this->set_error(20015);
        }

        // 文件不存在
        $file = 'Request/' . $request_folder . ucfirst($method[0]) . '_request';
        if (!file_exists(APPPATH . 'libraries/' . $file . '.php')) {
            $this->set_error(20016);
        }

        // 设置类别名，以免和系统目录下其它的类在加载的时候重复 add by willy 2015-01-26
        $method[0] = $method[0] . '_request';

        // 加载类文件，设置类别名
        $this->load->library($file, NULL, $method[0]);

        if (!is_callable(array($this->{$method[0]}, $method[1]))) {
            $this->set_error(20016);
        }
        unset($file);

        // 参数预处理

        // 每页条数
        $max_page_size = 100;
        if (isset($params['page_size'])) {
            $params['page_size'] = intval($params['page_size']);
            if ($params['page_size'] < 1) {
                $this->set_error(20022);
            }

            if ($params['page_size'] > $max_page_size) {
                $this->set_replace($max_page_size);
                $this->set_error(20024);
            }
        }
        unset($max_page_size);

        // 页码
        if (isset($params['page_no'])) {
            $params['page_no'] = intval($params['page_no']);
            if ($params['page_no'] < 1) {
                $this->set_error(20023);
            }
        }

        // 如果 page_size 存在 那边表示分页
        if (isset($params['page_size'])) {
            $params['is_pages'] = true;
        }

        // 平台对接专用，如饿了么等订单来源
        if (isset($app_config['platform_id']) && 0 < intval($app_config['platform_id'])) {
            $params['platform_id'] = intval($app_config['platform_id']);
        }

        // 应用信息，一些接口会记录这些信息
        $GLOBALS['app_info'] = array(
            'app_key'     => is_null($request['app_key'])     ? null : $request['app_key'],     // 这个是为了在access方法里区别system权限
            'app_type'    => is_null($request['app_type'])    ? null : $request['app_type'],
            'app_version' => is_null($request['app_version']) ? null : $request['app_version'],
            'uuid'        => is_null($request['uuid'])        ? null : $request['uuid'],
        );

        // access 使用的参数均需要放在这里
        $GLOBALS['params']      = $params;
        $GLOBALS['is_internal'] = $app_config['is_internal'];

        // 调用方法
        try {
            $data = call_user_func(array($this->{$method[0]}, $method[1]), $params);
        } catch (Exception $e) {
            if (99998 == $e->getCode()) {
                $this->_php_error = $e->getMessage();
                $this->set_error(99998);
            }

            // call_user_func 后的 $this->_error 不是当前文件下的方法，shutdown函数获取不到，所以这样改写了 add by willy 2014-12-23
            $this->set_replace(call_user_func(array($this->{$method[0]}, 'get_replace')));
            $this->set_data(call_user_func(array($this->{$method[0]}, 'get_data')));
            $this->set_error(call_user_func(array($this->{$method[0]}, 'get_error')));
        }

        // 正确的情况下
        $this->set_data($data);
        $this->set_error(10000);
    }

    /**
     * 获取 APP 信息
     *
     * @return  array
     */
    public function get_app_info()
    {
        data_filter($GLOBALS['app_info']);

        return $GLOBALS['app_info'];
    }

    /**
     * 判断用户权限组
     *
     * @param   array   $allow_groups   允许的权限组
     *
     * @return  bool
     */
    public function access(array $allow_groups)
    {
        $userinfo = array();
        $params   = $GLOBALS['params'];

        // 商家
        if (!empty($params['shop_session']) && !empty($params['shop_id'])) {
            $user_group = 'shop';

        // 鲜米C端用户
        } else if (!empty($params['member_session']) && !empty($params['member_id'])) {
            $user_group = 'member';

        // 后台
        } else if (1 == $GLOBALS['is_internal'] && 'c499fcc5298361eadf4357da306706ee' == $GLOBALS['app_info']['app_key']) {
            $user_group = 'system';

        // 公用
        } else {
            $user_group = 'public';
        }

        if (!in_array($user_group, $allow_groups)) {
            $this->set_error(20025);
        }

        // 身份为shop、member内部应用
        if (in_array($user_group, ['shop', 'member'])) {
            $model_name = ucfirst($user_group) . '_model';
            $this->load->model($model_name);

            if (!isset($params[$user_group . '_id'])) {
                $this->set_error(20030);
            }

            $userinfo = $this->{$model_name}->get_by_id($params[$user_group . '_id']);
            if (empty($userinfo) || 2 == $userinfo['is_enabled']) {
                $this->set_error(20030);
            }

            if (!empty($userinfo) && is_array($userinfo)) {
                $is_valid = false; // session是否验证成功

                // 这个分支中，是商家，配送员，内部应用中的API 使用
                if (1 == $GLOBALS['is_internal'] && INTERNAL_SESSION == $params[$user_group . '_session']) {
                    $is_valid = true;

                } else if ($userinfo[$user_group . '_session'] == $params[$user_group . '_session']) {
                    if (strtotime($userinfo['session_expire_time']) < time()) {
                        $this->set_error(20026);
                    }

                    // 加上操作人id name
                    $is_valid = true;
                    $userinfo['operate_user_id'] = $userinfo[$user_group . '_id'];
                    $userinfo['operate_user_name'] = $userinfo[$user_group . '_name'];

                } else if ('shop' == $user_group) { //商家允许同一账号多台设备同时登录
                    // 取所有该帐号在线的session
                    if ($sessions = $this->$model_name->get_shop_sessions($params[$user_group . '_id'])) {
                        foreach ($sessions as $session) {
                            if ($params[$user_group . '_session'] == $session['shop_session']) {
                                $is_valid = true;
                                break;
                            }
                        }
                    }

                    if (!$is_valid) {
                        $this->set_error(20026);
                    }
                } else {
                    $this->set_error(20027);
                }

                if ($is_valid) {
                    $userinfo['group']   = $user_group;
                    $GLOBALS['userinfo'] = $userinfo;
                }

            } else {
                $this->set_error(20027);
            }

        // 系统后台
        } else if ($user_group == 'system') {
            // 附加后台操作人信息
            if (!empty($params['operate_user_id']) && !empty($params['operate_user_name'])) {
                $userinfo['operate_user_id']   = $params['operate_user_id'];
                $userinfo['operate_user_name'] = $params['operate_user_name'];
            }

            $userinfo['group']   = $user_group;
            $GLOBALS['userinfo'] = $userinfo;

        // 公共的权限
        } else if (in_array($user_group, ['public'])) {
            $userinfo['group']   = $user_group;
            $GLOBALS['userinfo'] = $userinfo;
        }

        // 用完销毁
        unset($GLOBALS['is_internal'], $GLOBALS['params']);
    }

    /**
     * 获得当前用户信息
     *
     * @return mixed
     */
    public function get_user_info()
    {
        return $GLOBALS['userinfo'];
    }

    /**
     * 根据权限组输出对应可输出的值
     *
     * @param   array   $group_list
     * @param   array   $data
     * @param   int     $l
     *
     * @return array
     */
    public function group_params(array $group_list, array $data, $l = 2)
    {
        $userinfo   = $this->get_user_info();
        $this_group = $group_list[$userinfo['group']];
        if (!is_array($this_group)) {
            $this->set_error(20028);
        }

        $return_data = array();

        if ($l == 1) { //1维数据类型
            foreach ($data as $k => $val) {
                if (in_array($k, $this_group)) {
                    $return_data[$k] = $val;
                }
            }
        } else if ($l == 2) {
            foreach ($data as $item) {
                $_d = array();
                foreach ($item as $k => $val) {
                    if (in_array($k, $this_group)) {
                        $_d[$k] = $val;
                    }
                }
                $return_data[] = $_d;
            }
        }

        return $return_data;
    }

    /**
     * 附加账户验证信息
     *
     * @param   array   $params 请求参数
     *
     * @return  array
     */
    public function append_member_info(&$params)
    {
        $params['member_id']      = isset($params['member_id'])      ? $params['member_id']      : 0;
        $params['member_session'] = isset($params['member_session']) ? $params['member_session'] : '';

        return $params;
    }

    /**
     * 做接口转发（有提交到IOS审核没有通过的都走 test 环境）
     *
     * @param   array   $params 请求参数
     *
     * @return  array
     */
    public function request_test_api($request = [])
    {
        // 查找需要走test环境的版本(ios的这个版本需要走test环境) `project_id` IN ('3') and app_type in (34, 36) and is_audit = 2
        $search = [
            'is_pages'   => true,
            'is_audit'   => 2,
            'project_id' => 3,
            'app_type'   => $request['app_type'],
            'app_version'=> $request['app_version'],
            'fields'     => 'version_id, app_version, app_type, project_id, is_audit',
        ];
        $this->load->model('Version_model');
        $version_result = $this->Version_model->get_version_list($search);

        // 有数据证明有正在审核的需要走测试环境
        if (is_array($version_result['list']) && !empty($version_result['list'])) {
            $this->load->library('api');
            $this->api->set_uri('http://testapi.52xianmi.com/rest');
            $result = $this->api->post(trim(file_get_contents("php://input")));
            exit($result);
        }
    }

}