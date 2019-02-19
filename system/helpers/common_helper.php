<?php
/**
 * 系统级别公用函数
 *
 * @author      willy
 * @date        2012-05-15
 * @category    Common
 * @copyright   Copyright(c) 2012
 * @version     $Id: common_helper.php 1617 2014-08-12 03:50:05Z 熊飞龙 $
 */

/**
 * 对参数进行递归调用
 *
 * @param   string  $callback   要调用的函数
 * @param   mixed   $params     要递归的参数
 *
 * @return  void
 */
if (!function_exists('array_map_recursive')) {
    function array_map_recursive($callback, &$params) {
        if (is_callable($callback)) {
            if (is_string($params)) {
                $params = call_user_func($callback, $params);
            } else if (is_array($params)) {
                foreach ($params as $key => $val) {
                    array_map_recursive($callback, $params[$key]);
                }
            }
        }
    }
}

/**
 * 对参数进行过滤
 *
 * @param   mixed   $params     要过滤的参数
 *
 * @return  void
 */
if (!function_exists('data_filter')) {
    function data_filter(&$params) {
        if (!isset($params['no_filter']) && !isset($params['set']['no_filter'])) {
            array_map_recursive('strip_tags'      , $params);
            array_map_recursive('htmlspecialchars', $params);
            array_map_recursive('trim'            , $params);
        }else {
            return $params;
        }
    }
}

/**
 * 对参数进行过滤
 *
 * @param   mixed   $params     要过滤的参数
 *
 * @return  void
 */
if (!function_exists('html_filter')) {
    function html_filter(&$params) {
        $params = htmlspecialchars_decode($params);
        $params = str_replace('&ldquo;' , '“', $params);
        $params = str_replace('&rdquo;' , '”', $params);
        $params = str_replace('&middot;', '·', $params);
        $params = str_replace('&lsquo;' , '‘', $params);
        $params = str_replace('&rsquo;' , '’', $params);
        $params = str_replace('&hellip;', '…', $params);
        $params = str_replace('&mdash;' , '—', $params);
        $params = str_replace('&amp;' ,   '&', $params);
    }
}

/**
 * 生成链接地址
 *
 * @param   string  $uri        相对路径
 * @param   string  $base_url   基本路径
 *
 * @return  void
 */
if (!function_exists('site_url')) {
    function site_url($uri = '', $base_url = '')
    {
        $CI =& get_instance();

        if (empty($base_url)) {
            return $CI->config->site_url($uri);
        } else {
            if ('/' === $uri) {
                return $base_url;
            } else {
                $origin_base_url = $CI->config->item('base_url');
                $CI->config->set_item('base_url', $base_url);
                $site_url = $CI->config->site_url($uri);
                $CI->config->set_item('base_url', $origin_base_url);
            }
            return $site_url;
        }
    }
}

/**
 * 跳转到指定链接
 *
 * @param   string  $url    要跳转的地址
 *
 * @return  void
 */
if (!function_exists('url_redirect')) {
    function url_redirect($url)
    {
        header('Location: '. $url);
        exit;
    }
}

/**
 * 返回 404
 *
 * @return  void
 */
if (!function_exists('url_404')) {
    function url_404()
    {
        header('HTTP/1.1 404 Not Found');
        exit;
    }
}

/**
 * 返回上一个请求url地址 HTTP_REFERER
 *
 * @return  void
 */
if (!function_exists('url_referer')) {
    function url_referer()
    {
        $referer = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST['referer'] : $_GET['referer'];
        //$referer = base64_decode($referer);
        if (empty($referer)) {
            return '';
        }
        data_filter($referer);
        return $referer;
    }
}

/**
 * 返回 json 格式数组
 *
 * @access  private
 * @param   string  $message        消息
 * @param   bool    $success        是否成功
 * @param   array   $data           要返回的数据
 * @param   string  $jsonpCallback  要返回的数据
 *
 * @return  string
 */
if (!function_exists('json_exit')) {
    function json_exit($message = '系统异常', $success = false, $data = '', $jsonpCallback = null)
    {
        header("Content-type: application/json");
        $result = array();
        $result['message'] = $message;
        $result['success'] = (bool) $success;
        $result['data']    = $data;

        // @retain@ add by wangyuanlei  date 2014-10-13 15:12 代码全部是utf-8 不需要转换
        // $response = json_encode(gbk_to_utf8($result));
        $response = json_encode($result);
        !empty($jsonpCallback) && is_string($jsonpCallback) && $response = $jsonpCallback . '(' . $response . ')';

        exit($response);
    }
}

/**
 * 检查一个变量是否是自然数 (0, 1, 2, 3……)
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
if (!function_exists('is_natural_number')) {
    function is_natural_number($param)
    {
        if (preg_match('/^(0|([1-9]\d*))$/', $param)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 检查一个变量是否是整数 (……-2, -1, 0, 1, 2, 3……)
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
if (!function_exists('is_integer_number')) {
    function is_integer_number($param)
    {
        if (preg_match('/^(0|(-?[1-9]\d*))$/', $param)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 检查一个变量是否是0-9A-Z
 *
 * @param   mixed   $param  待检测的字符串
 * @param   int     $length 待检测的长度
 *
 * @return  bool
 */
if (!function_exists('is_09az')) {
    function is_09az($param, $length = null)
    {
        $length  = is_int($length) && $length > 0 ? '{' . $length . '}' : '+';
        $pattern = '/^[0-9A-Za-z]' . $length . '$/';

        if (preg_match($pattern, $param)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 把对象转换成数组
 *
 * @param   object  $object 要转换的对象
 *
 * @return  void
 */
if (!function_exists('object2array')) {
    function object2array($obj) {
        return @json_decode(json_encode($obj), true);
    }
}

/**
 * 把xml转换成数组
 *
 * @param   string  $xml 要转换的 xml 字符串
 *
 * @return  void
 */
if (!function_exists('xml2array')) {
    function xml2array($xml) {
        // simplexml_load_string 最后一个参数表示连同 CDATA 的内容一起解析
        return @json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}

/**
 * 返回允许的参数
 * @param   mixed   $params     要过滤的参数
 * @param   mixed   $params     允许的键名
 * @return  void
 */
if (!function_exists('data_picker')) {
    function data_picker(&$params,$allow) {
        foreach ($params as $key => $item) {
            if(!in_array($key,$allow)){
                unset($params[$key]);
            }else{
                if(is_array($item)){
                    data_filter($item);
                    $params[$key] = $item;
                }else{
                    $params[$key] = trim($item);
                }
            }
        }
    }
}

/**
 * 返回多维数组里允许导出的数据项
 *
 * @param   array   $params 要过滤的参数
 * @param   array   $allow  允许的键名
 *
 * @return  array
 */
if (!function_exists('excel_data')) {
    function excel_data(array $params, array $allow)
    {
        $result = array();
        if (!empty($params) && is_array($params)) {
            if (!empty($allow) && is_array($allow)) {
                foreach ($params as $key => $val) {
                    foreach ($allow as $v) {
                        $result[$key][$v] = isset($val[$v]) ? $val[$v] : '';
                    }
                }
            }
        }

        return $result;
    }
}

/**
 * 过滤数组中无效的键值
 *
 * @param   array   $params 要过滤的参数
 * @param   array   $allow  要过滤的值
 *
 * @return  void
 */
if (!function_exists('invalid_data_filter')) {
    function invalid_data_filter(array &$params, array $allow = array(null, ''))
    {
        if (!empty($params) && is_array($params) && !empty($allow) && is_array($allow)) {
            foreach ($params as $key => $val) {
                foreach ($allow as $v) {
                    if ($val === $v) {
                        unset($params[$key]);
                    }
                }
            }
        }
    }
}

/**
 * 过滤数组中无效的键值 (递归过滤)
 *
 * @param   array   $params 要过滤的参数
 * @param   array   $allow  要过滤的值
 *
 * @return  void
 */
if (!function_exists('invalid_data_filter_recursive')) {
    function invalid_data_filter_recursive(array &$params, array $allow = array(null, ''))
    {
        if (!empty($params) && is_array($params) && !empty($allow) && is_array($allow)) {
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    invalid_data_filter_recursive($params[$key], $allow);
                } else {
                    foreach ($allow as $v) {
                        if ($val === $v) {
                            unset($params[$key]);
                        }
                    }
                }
            }
        }
    }
}

/**
 * 同时获取 GET 及 POST 参数
 *
 * @params  bool    $override   是否用 GET 参数 覆盖 POST 参数
 */
if (!function_exists('get_post')) {
    function get_post($override = true)
    {
        $params = true === $override ? array_merge($_POST, $_GET) : array_merge($_GET, $_POST);
        return $params;
    }
}

/**
 * 调试：格式化打印变量
 *
 * @return  string
 */
if (!function_exists('dump')) {
    function dump($is_utf8 = true)
    {
        $params = func_get_args();

        if ($is_utf8) {
            echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
        }

        echo '<pre>', call_user_func_array('var_dump', $params), '</pre>';
    }
}

/**
 * 格式化打印调试变量print_r
 *
 * @return  string
 */
if (!function_exists('to_print')) {
    function to_print($is_utf8 = true)
    {
        $params = func_get_args();

        if ($is_utf8) {
            echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
        }

        echo '<pre>', call_user_func_array('print_r', $params), '</pre>';
    }
}

/**
 * 获取系统用户的信息
 *
 * @return  string
 */
if (!function_exists('get_system_user')) {
    function get_system_user()
    {
        return array(
            'user_id'   => 1,
            'user_name' => 'system',
        );
    }
}

/**
 * 组合 javascript 标签
 *
 * @param   array   $data           要显示的js
 * @param   array   $append_data    要追加的js
 *
 * @return  string
 */
if (!function_exists('script_tag')) {
    function script_tag($data = array(),$append_data = array())
    {
        $output = '';
        if(!empty($append_data)){
            $data = array_merge($data,$append_data);
        }
        foreach ($data as &$item) {
            $output .= '<script src="' . HOME_DOMAIN . 'js/' . $item . (strstr($item, '?') ? '&' : '?') . 'v=' . JS_VERSION . '" type="text/javascript"></script>';
        }
        return $output;
    }
}

// ------------------------------------------------------------------------

/**
 * 组合 css 标签
 *
 * @param   array   $data           要显示的css
 * @param   array   $append_data    要追加的css
 *
 * @return  string
 */
if (!function_exists('css_tag')) {
    function css_tag($data = array(),$append_data = array())
    {
        $output = '';
        if(!empty($append_data)) {
            $data = array_merge($data,$append_data);
        }
        foreach ($data as &$item) {
            $output .= '<link href="' . HOME_DOMAIN . 'css/' . $item . (strstr($item, '?') ? '&' : '?') . 'v=' . CSS_VERSION . '" rel="stylesheet" type="text/css" />';
        }
        return $output;
    }
}


/**
 * 在模板里包含模板
 *
 * @param   string  $tpl_name   模板名称
 * @param   array   $vars       要在模板里使用的变量
 *
 * @return  void
 */
if (!function_exists('include_tpl')) {
    function include_tpl($tpl_name, $vars = array())
    {
        if (!empty($tpl_name)) {
            if (!empty($vars) && is_array($vars)) {
                extract($vars);
            }

            $ci_view_paths = array(APPPATH.'views/' => TRUE, BASEPATH.'views/' => TRUE);

            $file_exists = false;
            $tpl_path   = '';

            foreach($ci_view_paths as $view_file => $cascade) {
                if (file_exists($view_file . $tpl_name)) {
                    $tpl_path    = $view_file . $tpl_name;
                    $file_exists = true;
                    break;
                }

                if (!$cascade) {
                    break;
                }
            }

            if ( !$file_exists && !file_exists($_ci_path))
            {
                show_error('Unable to load the requested file: ' . $tpl_name);
            } else {
                include $tpl_path;
            }
        }
    }
}

/**
 * 获取运行环境名称
 *
 * 如果是本地开发环境显示本地环境信息
 * @param   $is_moblie  bool    是否移动测试环境
 *
 * @author  willy
 */
if (!function_exists('get_env_name')) {
    function get_env_name($is_moblie = false) {
        if (0 === strpos($_SERVER['SERVER_ADDR'], '127.')) {
            return true === $is_moblie ? '本机' : '测试环境：本机';
        } else {
            return '';
        }
    }
}

/**
 * 可变模板
 *
 * @param    int    $mobile
 * @param    string $content
 *
 * @return   bool
 */
if (!function_exists('variable_template')) {
    function variable_template($template, $value, $left = '{', $right = '}')
    {
        if (empty($value) || !is_array($value)) {
            return $template;
        }

        foreach ($value as $key => $val) {
            $content[$left . $key . $right] = $val;
            unset($content[$key]);
        }

        return str_replace(array_keys($content), array_values($content), $template);
    }
}


/**
 * 是否是我方的域名
 *
 * @return   bool
 */
if (!function_exists('is_our_domain')) {
    function is_our_domain() {
        if (preg_match("/^http(s)?\:\/\/[a-z0-9\-]+\." . DOMAIN . "/", $_SERVER['HTTP_REFERER'])) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 创建目录（根据路径创建不存在的目录,只能根据绝对路径创建)
 *
 * @param    string    $path
 *
 * @return   bool
 */
if (!function_exists('mkdir_recursive')) {
    function mkdir_recursive($path) {
        if(!is_dir($path)) {
            $dir = explode('/', $path);
            $current = $dir[0];

            // 如果带有文件名则过滤该文件名
            if (strstr(end($dir), '.')) {
                array_pop($dir);
            }

            for($i=1; $i < count($dir); $i++) {
                $current .= '/'.$dir[$i];
                if(!is_dir($current)){
                    mkdir($current, DIR_WRITE_MODE);
                }
            }
        }
        return true;
    }
}

/**
 * 返回当前登录用户的相关信息(非后台系统使用)
 *
 * @access  public
 *
 * @return  array
 */
if (!function_exists('get_user_info')) {
    function get_user_info()
    {
        if (empty($_SESSION['customer'])) {
            return array();
        } else {
            return $_SESSION['customer'];
        }
    }
}

/**
 * 返回当前登录用户的相关信息
 *
 * @access  public
 *
 * @return  array
 */
if (!function_exists('get_operate_user')) {
    function get_operate_user()
    {
        // 后台登录
        if ($_SESSION['admin']['user_id']) {
            return array(
                'user_id'  => $_SESSION['admin']['user_id'],
                'userName' => $_SESSION['admin']['userName'],
            );
        }

        return array(
            'user_id'  => 1,
            'userName' => 'system',
        );
    }
}

/**
 * 追加用户信息(后台使用)
 *
 * @access  public
 *
 * @params  array   $params     追加用户信息到此数组中
 * @params  string  $key        键名
 *
 * @return  void
 */
if (!function_exists('append_user_info')) {
    function append_user_info(&$params, $key)
    {
        $customer = get_operate_user();
        $params[$key . '_time']      = date('Y-m-d H:i:s');
        $params[$key . '_user_id']   = $customer['user_id'];
        $params[$key . '_user_name'] = $customer['userName'];
    }
}

/**
 * 追加用户创建信息(后台使用)
 *
 * @access  public
 *
 * @params  array   $params     追加用户信息到此数组中
 *
 * @return  void
 */
if (!function_exists('append_create_info')) {
    function append_create_info(&$params)
    {
        append_user_info($params, 'create');
    }
}

/**
 * 追加用户更新信息(后台使用)
 *
 * @access  public
 *
 * @params  array   $params     追加用户信息到此数组中
 *
 * @return  void
 */
if (!function_exists('append_update_info')) {
    function append_update_info(&$params)
    {
        append_user_info($params, 'update');
    }
}

/**
 * 追加用户更新信息(后台使用)
 *
 * @access  public
 *
 * @params  array   $params     追加用户信息到此数组中
 *
 * @return  void
 */
if (!function_exists('append_create_update')) {
    function append_create_update(&$params)
    {
        append_create_info($params);
        append_update_info($params);
    }
}

/**
 * 计算身份证校验码
 *
 * @return  bool
 */
if (!function_exists('sumCheckCode')) {
    function sumCheckCode($pid) {
        $set = [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2];
        $ver = ['1','0','X','9','8','7','6','5','4','3','2'];
        $sum = 0;
        $arr = str_split($pid);

        for ($i = 0; $i < 17; $i++) {
            if (!is_numeric($arr[$i])) {
                return false;
            }
            $sum += $arr[$i] * $set[$i];
        }

        $mod = $sum % 11;

        return $ver[$mod];
    }
}

/**
 * 是否为合法身份证
 *
 * @return  bool
 */
if (!function_exists('is_pid')) {
    function is_pid($idStr) {
        $result = [
            'success' => false,
            'message' => null,
            'idSn'    => $idStr,
            'realname'=> null,
            'address' => null,
            'birth'   => null,
            'sex'     => null,
        ];

        // 身份证第一位为1-6最后一位可能为X
        if (!preg_match('/^[1-6][\d]{16}[\dXx]$/', $idStr)) {
            $result['message'] = '请输入正确的身份证号码';
            return $result;
        }

        $birthday = substr($idStr, 6, 8);
        if ($birthday < '19010101') {
            $result['message'] = '请输入正确的身份证号码';
            return $result;
        }

        //比较身份证校验码
        $existNumber   = strtoupper(substr($idStr, 17, 1));
        $jiaoyanNumber = sumCheckCode($idStr);
        // dump($existNumber,$jiaoyanNumber);exit;
        if ($existNumber != $jiaoyanNumber) {
            $result['message'] = '此身份证号码有异常';
        } else {
            $result['success'] = true;
        }

        // 生日
        $result['birth'] = substr($idStr, 6, 4) . '-' . substr($idStr, 10, 2) . '-' . substr($idStr, 12, 2);
        // 性别
        $result['sex']   = (intval(substr($idStr, 16, 1))%2 == 1) ? '男' : '女';

        return $result;
    }
}

/**
 * 通过生日计算年龄
 *
 * @return  bool
 */
if (!function_exists('get_age')) {
    function get_age($birth) {
        $result = [
            'age'      => null,
            'birth' => null,
            'message'  => null,
        ];

        $age = strtotime($birth);
        if ($age === false) {
            $result['message'] = '请输入正确的出生年月日 如：1993-22-22';
            return $result;
        }

        list($year, $month, $date) = explode('-', date('Y-m-d', $age));
        $year_diff  = date('Y') - $year;
        $month_diff = date('m') - $month;
        $date_diff  = date('d') - $date;

        if ((int)$month_diff < 0 || (int)$date_diff < 0) {
            $year_diff--;
        }

        $result['age']   = $year_diff;
        $result['birth'] = date('Y-m-d', $age);

        return $result;
    }
}

/**
 * 是否为微信浏览器
 *
 * @return  bool
 */
if (!function_exists('is_weixin_browser')) {
    function is_weixin_browser() {
        $ag1  = strstr($_SERVER['HTTP_USER_AGENT'], "MicroMessenger");
        $ag2 = explode("/", $ag1);
        $ver = floatval($ag2[1]);
        if ( $ver < 5.0 || empty($ag1) ){
            return false;
        }else{
            return true;
        }
    }
}

/**
 * 是否安卓系统
 *
 * @return  bool
 */
if (!function_exists('is_android')) {
    function is_android() {
        // 大部分的安卓浏览器是 Mozilla/5.0 (Linux; Android 开头，Opera和Firefox是 Mozilla/5.0 (Android; Linux及Opera/9.80 (Android 2.3.3; Linux; Opera
        // HTCM8t 只有 Android 字样
        if (preg_match('/Android/', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 是否为IOS系统
 *
 * @return bool
 */
if (!function_exists('is_ios')) {
    function is_ios() {
        if (preg_match('/\(i[^;]+;( U;)? CPU.+Mac OS X/', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 生成图片链接地址
 *
 * @param   string  $uri        相对路径
 *
 * @return  string
 */
if (!function_exists('img_url')) {
    function img_url($uri)
    {
        return HOME_DOMAIN . $uri;
    }
}

/**
 * 生成数据库表的唯一key
 *
 * @return  string
 */
if (!function_exists('get_unique_key')) {
    function get_unique_key()
    {
        return md5(uniqid(microtime(TRUE), TRUE));
    }
}

/**
 * 生成一个随机验证码(默认6位)
 *
 * @return  string
 */
if (!function_exists('get_sms_captcha')) {
    function get_captcha($length = 6)
    {
        $result = '';
        for($i = 0; $i < $length; $i ++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}

/**
 * 手机号码部分文字屏蔽
 *
 * @return  string
 */
if(!function_exists('phone_replace')) {
    function phone_replace($mobile_phone)
    {
        return substr_replace($mobile_phone, '****', 3, 4);
    }
}

/**
 * GPS 转 百度经纬度
 *
 * @param   array   $params
 *
 * @return  array
 */
if (!function_exists('gps_to_baidu')) {
    function gps_to_baidu(array $params = array())
    {
        if (empty($params['lat']) || empty($params['lng'])) {
            return array(
                'lat' => 0,
                'lng' => 0,
            );
        }

        // 坐标转换服务无日请求次数限制。 坐标转换服务每次最多支持100个坐标点的转换且并发数为1000次/秒
        // *本接口支持回调。另外，同一个GPS坐标多次转为百度坐标时，每次转换结果都不完全一样，误差在2米范围内，属于正常误差，不影响正常使用
        // 接口文档 http://developer.baidu.com/map/changeposition.htm
        $baidu = json_decode(curl_get("http://api.map.baidu.com/geoconv/v1/?coords=" . $params['lng'] . "," . $params['lat'] . "&from=1&to=5&ak=" . BAIDU_MAP_AK), true);

        if ($baidu['status'] == 0) {
            $baidu_lat = $baidu['result'][0]['y'];
            $baidu_lng = $baidu['result'][0]['x'];
        } else {
            // 备用接口，虽然没有文档。
            $baidu = json_decode(curl_get("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=".$params['lng']."&y=".$params['lat']), true);
            $baidu_lat = base64_decode($baidu['y']);
            $baidu_lng = base64_decode($baidu['x']);
        }

        return array(
            'lat' => $baidu_lat,
            'lng' => $baidu_lng,
        );
    }
}

/**
 * 使用 CURL 发送一个 GET 请求
 */
if (!function_exists('curl_get')) {
    function curl_get($params)
    {
        if (is_array($params)) {
            $rest_config = $params;
            $rest_config['verb'] = 'GET';
        } else if (is_string($params)) {
            $rest_config = array(
                'verb' => 'GET',
                'url'  => $params,
            );
        }

        $CI = & get_instance();
        $CI->load->library("Rest");
        $CI->rest->config($rest_config);
        return $CI->rest->execute();
    }
}

/**
 * 使用 CURL 发送一个 POST 请求
 */
if (!function_exists('curl_post')) {
    function curl_post($params)
    {
        if (is_array($params)) {
            $rest_config = $params;
            $rest_config['verb'] = 'POST';
        } else if (is_string($params)) {
            $rest_config = array(
                'verb' => 'POST',
                'url'  => $params,
            );
        }

        $CI = & get_instance();
        $CI->load->library("Rest");
        $CI->rest->config($rest_config);
        return $CI->rest->execute();
    }
}

/**
 * 配合 calc_distance 函数使用的
 *
 * @return  float
 */
if (!function_exists('get_rad')) {
    function get_rad($d)
    {
        return $d * 3.1415926535898 / 180.0;
    }
}

/**
 * 计算两个经纬度之间的距离 单位（公里）
 *
 * @param   float   $start_lat  A点纬度
 * @param   float   $start_lng  A点经度
 * @param   float   $end_lat    B点纬度
 * @param   float   $end_lng    B点经度
 *
 * @return  float
 */
if (!function_exists('calc_distance')) {
    function calc_distance($start_lat, $start_lng, $end_lat, $end_lng)
    {
        if (empty($start_lat) || empty($start_lng) || empty($end_lat) || empty($end_lng)) {
            return 'NaN';
        }

        $EARTH_RADIUS = 6378.137;
        $radLat1 = get_rad($start_lat);
        $radLat2 = get_rad($end_lat);

        $a = $radLat1 - $radLat2;
        $b = get_rad($start_lng) - get_rad($end_lng);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) +
        cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s *$EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return number_format($s, 3, null, false);
    }
}


/**
 * 配合 calc_point 函数使用的
 * 
 * @return  float
 */
if (!function_exists('get_deg')) {
    function get_deg($r)
    {
        return $r * 180.0 / 3.1415926535898;
    }
}

/**
 * 根据一个坐标点及距离，计算出X轴及Y轴的经纬度
 *
 * @param   float   $origin_lat     原始坐标经度
 * @param   float   $origin_lng     原始坐标经度
 * @param   float   $distance       距离（单位 KM）
 *
 * @return  array
 */
if (!function_exists('calc_point')) {
    function calc_point($origin_lat, $origin_lng, $distance)
    {
        $S = $distance;
        $R = 6378.137;

        // $table_latitude 差值
        $sub_lat  = get_deg($S/$R);

        // $table_longitude 差值
        $radlat   = get_rad($origin_lat);
        $sub_long = get_deg(2 * asin( sin($S/(2*$R)) / cos($radlat)));

        return array(
            'lat_from' => $origin_lat - $sub_lat,
            'lat_to'   => $origin_lat + $sub_lat,
            'lng_from' => $origin_lng - $sub_long,
            'lng_to'   => $origin_lng + $sub_long,
        );
    }
}

/**
 * 穷举排列组合
 * in [1, 2, 3]
 * out [
 *  [1, 2, 3],
 *  [1, 3, 2],
 *  [2, 1, 3],
 *  [2, 3, 1],
 *  [3, 1, 2],
 *  [3, 2, 1],
 * ]
 *
 * @param   array   $in     输入
 * @param   array   $out    输出
 * @param   float   $tmp    用来存储每轮的结果
 *
 * @return  void
 */
if (!function_exists('exhaustive_combine')) {
    function exhaustive_combine(array $in, array &$out, array $tmp = []) {
        if (empty($in)) {
            $out[] = $tmp;
        } else {
            $len = count($in);
            for($i=0; $i< $len; $i++) {
                $tmp_out   = $tmp;
                $tmp_out[] = $in[$i];
                $tmp_in    = $in;
                unset($tmp_in[$i]);
                $tmp_in    = array_values($tmp_in);
                exhaustive_combine($tmp_in, $out, $tmp_out);
            }
        }
    }
}

/**
 * 计算多个GPS点的最短路径（以第一个点为起点）
 *
 *  $gpsPoints = [
 *      [121.398953, 31.240834], // 中江路
 *      [121.499014, 31.233111], // 豫园
 *      [121.41957,  31.240591], // 环球港
 *      [121.443493, 31.198327], // 徐家汇
 *    ];
 *
 * @param   array   $gpsPoints  GPS坐标点数组
 *
 * @return  array/null
 */
if (!function_exists('calc_shortest_distance')) {
    function calc_shortest_distance(array $gpsPoints)
    {
        $result = null;

        if (empty($gpsPoints) || !is_array($gpsPoints)) {
            return $result;
        }

        // 检查一下是否每个GPS点经纬度都存在
        foreach ($gpsPoints as $v) {
            if (empty($v['0']) || empty($v['1'])) {
                return $result;
            }
        }

        $startPoints = array_shift($gpsPoints);
        $gpsCombine = [];

        // 性能较差 时间复杂度为 O(n!)
        // 本机 5个点 0.052s    6个点 0.355s   7个点 2.093
        // 线上 5个点 0.00058s  6个点 0.0029s  7个点 0.02163  8个点 0.16232 9个点1.5549
        exhaustive_combine($gpsPoints, $gpsCombine);

        $distances = [];
        foreach ($gpsCombine as $k => $v) {
            $distance = 0;
            $last = $startPoints;
            foreach ($v as $kk=> $vv) {
                $distance += calc_distance($last[1], $last[0], $vv[1], $vv[0]);
                $last = $vv;
            }
            $distances[$k] = $distance;
        }

        // 保持索引
        asort($distances);

        // 最短路径及对应的路由
        $shortest = current($distances);
        $router   = $gpsCombine[key($distances)];
        array_unshift($router, $startPoints);

        $result = [
            'shortest'  => $shortest,
            'router'    => $router,
            'distances' => $distances,
        ];

        return $result;
    }
}

/**
 * 判断一个点是否在一个多边形里
 *
 *  $point = [121.402228, 31.232229]
 *  $coordinates = [
 *      [121.398953, 31.240834],
 *      [121.499014, 31.233111],
 *      [121.41957,  31.240591],
 *      [121.443493, 31.198327],
 *    ];
 *
 * @return  bool/null
 */
if (!function_exists('point_in_polygon')) {
    function point_in_polygon(array $point, array $coordinates)
    {
        $result = null;

        if (empty($point) || count($point) != 2 || empty($coordinates)) {
            return $result;
        }

        // 把经度和纬度互换一下位置，满足类的要求
        $coords = [];
        foreach ($coordinates as $v) {
            $coords[] = [$v[1], $v[0]];
        }
        $coordinates = $coords;
        unset($coords);

        $CI =& get_instance();
        $CI->load->library('polygon');

        $CI->polygon->setPolygon($coordinates);

        // 是否成功创建
        if (!$CI->polygon->isValid()) {
            return $result;
        }

        $result = false;
        // 判断一个点是否是在多边形内 参数是 latitude longitude
        if ($CI->polygon->pip($point[1], $point[0])) {
            $result = true;
        }

        return $result;
    }
}

/**
 * 获取时间
 *
 * @param   int   $time     时间戳
 *
 * @return  string
 */
if (!function_exists('get_date')) {
    function get_date($time = null)
    {
        if (is_null($time)) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', $time);
    }
}

/**
 * 记录文本日志到应用log目录下
 *
 * @param   string  $content    内容
 * @param   string  $filename   文件内容
 *
 * @return  bool
 */
if (!function_exists('add_txt_log')) {
    function add_txt_log($content, $filename = 'temp')
    {
        try {
            $dir      = APPPATH . '/logs/';
            $suffix   = '_' . date('Ymd') . '.txt';
            $filename = $dir . $filename . $suffix;

            $content = '[' . get_date() . ']' . ' [' . $content . ']' . PHP_EOL;
            error_log($content, 3, $filename);
        } catch (Exception $e) {
            
        }

        return true;
    }
}

/**
 * 计算相差时间
 *
 * @param  string  $start_date  开始时间
 * @param  string  $end_date    结束时间
 * @param  string  $flag        要计算的类型
 * 
 * @return string
 */
if (!function_exists('calc_date_diff')) {
    function calc_date_diff($start_date, $end_date, $flag = 'n') {
        $start     = strtotime($start_date);
        $end       = strtotime($end_date);
        $time_diff = $end - $start;

        switch ($flag) {
            case 'y':
                $retval = bcdiv($time_diff, (60 * 60 * 24 * 365));
                break;
            case 'm':
                $retval = bcdiv($time_diff, (60 * 60 * 24 * 30));
            case 'w':
                $retval = bcdiv($time_diff, (60 * 60 * 24 * 7));
            case 'd':
                $retval = bcdiv($time_diff, (60 * 60 * 24));
            case 'h':
                $retval = bcdiv($time_diff, (60 * 60));
            default: // n
                $retval = bcdiv($time_diff, 60);
                break;
        }

        return floor($retval);
    }
}

/**
 * 根据环境获取cdn域名
 *
 * @return string
 */
if (!function_exists('get_cdn_domain')) {
    function get_cdn_domain($env = 'development')
    {
        // 这里是有自己开发访问，因此不以环境去配置图片路径
        if ('development' == $env) {
            return IMG_DOMAIN;
        } else {
            return IMG_DOMAIN;
        }
    }
}

/**
 * 根据经纬度和距离获生成一个随机纬经度
 * 
 * @params $latitude   纬度
 * @params $longitude  经度
 * @params $distance   距离
 * 
 * @return array
 */
if (!function_exists('generate_lat_long')) {
    function generate_lat_long($latitude,$longitude,$distance)
    {
        // 设置默认返回值
        $ret = [
            'latitude'  => 0,
            'longitude' => 0,
        ];

        // 计算出指定范围的GPS矩形坐标，方便快速定位配送员位置
        $point = calc_point($latitude, $longitude, $distance);

        // 对应小数需要保留的最大位数值
        $int_max = 1000000000;

        // 保留8位纬度
        $lat_from = sprintf('%.8f', $point['lat_from']);
        $lat_to   = sprintf('%.8f', $point['lat_to']);

        // 保留8位经度
        $lng_from = sprintf('%.8f', $point['lng_from']);
        $lng_to   = sprintf('%.8f', $point['lng_to']);

        // 设置纬经度的整数位
        $lat_int = intval($lat_from);
        $lng_int = intval($lng_from);

        // 将纬度的小数位设置为整形
        $lat_from_float_to_int = intval(($lat_from - $lat_int) * $int_max);
        $lat_to_float_to_int   = intval(($lat_to   - $lat_int) * $int_max);

        // 将经度的小数位设置为整形
        $lng_from_float_to_int = intval(($lng_from - $lng_int) * $int_max);
        $lng_to_float_to_int   = intval(($lng_to   - $lng_int) * $int_max);

        // 基于转换成整数的经纬度小数位生成随机整数
        $longitude = rand($lng_from_float_to_int, $lng_to_float_to_int);
        $latitude  = rand($lat_from_float_to_int, $lat_to_float_to_int);

        // 判断一下纬度的起始点坐标小数位前几位是否为0，如果是需要替换生成的纬度
        $lat_from_arr = explode('.', $lat_from);
        $lat_to_arr   = explode('.',   $lat_to);

        // 纬度的起始点
        $lat_from_float_len = strlen(ltrim($lat_from_arr[1],'0'));
        $lat_to_float_len   = strlen(ltrim($lat_to_arr[1],'0'));

        $is_latitude_change = 2;
        if ($lat_from_float_len < 8) {
            $bit = 8 - $lat_from_float_len;

            $latitude = substr($latitude, 0, $lat_from_float_len);
            $latitude = sprintf("%08d", $latitude);

            $is_latitude_change = 1;
        }

        if ($lat_to_float_len < 8 && 2 == $is_latitude_change) {
            $bit = 8 - $lat_to_float_len;

            $latitude = substr($latitude, 0, $lat_to_float_len);
            $latitude = sprintf("%08d", $latitude);
        }

        // 判断一下经度的起始点坐标小数位前几位是否为0，如果是需要替换生成的经度
        $lng_from_arr = explode('.', $lng_from);
        $lng_to_arr   = explode('.',   $lng_to);

        // 经度的起始点
        $lng_from_float_len = strlen(ltrim($lng_from_arr[1],'0'));
        $lng_to_float_len   = strlen(ltrim($lng_to_arr[1],'0'));

        $is_longitude_change = 2;
        if ($lng_from_float_len < 8) {
            $bit = 8 - $lng_from_float_len;

            $longitude = substr($longitude, 0, $lng_from_float_len);
            $longitude = sprintf("%08d", $longitude);

            $is_longitude_change = 1;
        }

        if ($lng_to_float_len < 8 && 2 == $is_longitude_change) {
            $bit = 8 - $lng_to_float_len;

            $longitude = substr($longitude, 0, $lng_to_float_len);
            $longitude = sprintf("%08d", $longitude);
        }

        // 拼接经纬度
        $ret['longitude'] = $lng_int  . '.' . $longitude;
        $ret['latitude']  = $lat_int  . '.' . $latitude;

        return $ret;
    }
}

/**
 * 判断是否存在 isset && !empty (??:)
 *
 * @param   string    $field    字段名称
 * @param   array     $params   参数
 *
 * @return  string|null    返回结果
 */
if(!function_exists('filter_empty')){
    function filter_empty($field, $params)
    {
        return (isset($params[$field]) && !empty($params[$field])) ? $params[$field] : null;
    }
}

/**
 * 获取配置内容
 *
 * @params   stdClass   $instance     类
 * @params   string     $config_name  配置名称
 * @params   string     $replace      替换值
 */
if(!function_exists('get_custom_config')){
    function get_custom_config($instance, $config_name, $replace = '')
    {
        if(empty($config_name)){
            return false;
        }

        $service_info = [
            'service_name'   => 'base.configure.get_template_by_configure_name',
            'service_params' => [
                'configure_name' => $config_name,
                'replace'        => $replace,
            ],
        ];

        $configure_result = $instance->requester->request($service_info);
        if (false == $configure_result['success']) {
            $instance->set_error($configure_result['errcode']);
            return false;
        }

        return $configure_result['data'];
    }
}


/**
 * 截取字符串（中英文）
 * 
 * @params  string  $source     原字符串
 * @params  int     $start      开始位置
 * @params  int     $length     截取长度(数字)
 * @params  string  $charset    字符编码
 * @params  string  $suffix     特殊标示
 * 
 * @return  string
 */
if (!function_exists('xs_substr')) {
    function xs_substr($source, $start = 0, $length, $charset = "utf-8", $suffix = "")
    {
        // 采用PHP自带的mb_substr截取字符串
        if (function_exists("mb_substr")) {
            $string = mb_substr($source, $start, $length, $charset) . $suffix;

        //采用PHP自带的iconv_substr截取字符串
        } elseif(function_exists('iconv_substr')) {
            $string = iconv_substr($source, $start, $length, $charset) . $suffix;

        } else {
            $pattern['utf-8']  = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
            $pattern['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
            $pattern['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
            $pattern['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
            preg_match_all($pattern[$charset], $source, $match);
            $slice  = join("", array_slice($match[0], $start, $length));
            $string = $slice . $suffix;
        }

        return $string;
    }
}

/**
 * 判断字符串是否为json
 *
 * @param   string $string  字符串
 *
 * @return  bool
 */
if(!function_exists('is_json')){
    function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

/**
 * 对二维数组按指定键值进行排序
 *
 * @param    string    $array    要排序的二维数组
 * @param    string    $field    按此字段进行排序
 * @param    string    $sort     排序类型(asc:升序 desc:降序)
 *
 * @return   bool
 */
if(!function_exists('arr_sort')){
    function arr_sort($array, $field, $sort = 'asc')
    {
        $arr_sort = $arr = [];

        // 获取数组 key=>指定键值 的索引数组，后面用来排序
        foreach ($array as $key => $val) {
            $arr_sort[$key] = $val[$field];
        }

        // 对指定索引数组进行排序
        if ($sort == 'asc') {
            asort($arr_sort);
        } else {
            arsort($arr_sort);
        }

        // 按排序好的索引数组，来重新拼接数组数据
        foreach ($arr_sort as $key => $val) {
            $arr[$key] = $array[$key];
        }

        return $arr;
    }
}

/**
 * 删除小数点后的0
 *
 * @param   string  $s                          数字
 * @param   bool    $contains_currency_symbol   是否包含货币单位
 *
 * @return  string
 */
if(!function_exists('trim_zero')) {
    function trim_zero($s, $contains_currency_symbol = false)
    {
        $s = explode('.', $s);
        if (count($s) == 2 && ($s[1] = rtrim($s[1], '0'))) return implode('.', $s);
        return (floatval($s[0]) > 0 ? '' : '-') . ($contains_currency_symbol ? '￥' : '') . abs($s[0]);
    }

/**
 * 通过IP地址获取归属地(淘宝接口)
 *
 * @param   string  $ip    IP地址
 *
 * @return  string
 */
if(!function_exists('getTaobaoAddress')) {
    function getTaobaoAddress($ip)
    {
        $ipContent = file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip);
        $result    = json_decode($ipContent, true);
        return $result;
    }
}
}
