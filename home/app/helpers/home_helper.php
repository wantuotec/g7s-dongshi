<?php
/**
 * validate user privilege
 * @param   string  $action     'access'|'edit'|'addnew'|'del'|'check'|'managerCheck'
 * @param   string  $basepath   权限挂在某个action上时，需要指定basepath。如product/img_list
 * @access	public
 * @return	mixed
 */
if (!function_exists('validate_priv')) {
    function validate_priv($action, $act_name = '', $is_json = false)
    {
        if (empty($action)) {
            $is_json ? json_exit('没有指定此模块的权限') : show_error('没有指定此模块的权限');
        }

        // 未登录用户请重新登录
        if (!isset($_SESSION['admin']['privilege'])) {
            $is_json ? json_exit('请先登录') : header("location: " . HOME_DOMAIN . 'auth');
        }

        if ('all' != $action) {
            if(!empty($act_name))
                $act_name ='/'.$act_name;

            global $RTR;
            $uri = $RTR->class . $act_name;

            if (!isset($_SESSION['admin']['privilege'][$uri]) || $_SESSION['admin']['privilege'][$uri][$action] !== 'allow')
                $is_json ? json_exit('您没有访问此模块的权限') : show_error('您没有访问此模块的权限');
        }
    }
}

function filter_data(array $params, $function)
{
    foreach ($params as $key => $param) {
        if (is_array($param)) {
            $params[$key] = filter_data($param, $function);
        } else {
            $params[$key] = $function($param);
        }
    }
    return $params;
}

/**
 * 过滤无效的键
 *
 * @return   array
 **/
function filter_invalid_value(array $params)
{
    foreach ($params as $key => $val) {
        if($val == '-1') {
            unset($params[$key]);
        }
    }
    return $params;
}

/*
public function build_url($url, $params)
{
    $query_string = '';
    if (count($params) > 0) {
        $query_string = http_build_query($params);
        $url .= '?' . $query_string;
    }
    return $url;
}
*/

/*
* 过滤指定key的单元
*/
function filter_key($params, $key)
{
    if (is_array($key)) {
        foreach ($key as $k) {
            if(isset($params[$k])) {
                unset($params[$k]);
            }
        }
    } else {
        unset($params[$key]);
    }
    return $params;
}

/*
* 对二维数组，按指定key进行分组(key_name操作)
*/
function array_group(array $array1, $param)
{
    $array2 = array();
    foreach ($array1 as $val) {
        isset($val[$param]) && $array2[$val[$param]][] = $val;
    }
    return $array2;
}

function single_group(array $array1, $param)
{
    $array2 = array();
    foreach ($array1 as $val) {
        isset($val[$param]) && $array2[$val[$param]] = $val;
    }
    return $array2;
}


function check_username($username)
{
    if (!preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $username) || strlen($username) < 3 || strlen($username) > 15) {
        return false;
    }
    return true;
}

function check_email($email)
{
    if (!preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/', $email)) {
        return false;
    }
    return true;
}

function check_mobile($mobile)
{
    if (!preg_match('/^1[3|4|5|8]{1}\d{9}$/', $mobile)) {
        return false;
    }
    return true;
}

function check_price($price)
{
    if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
        return false;
    }

    return true;
}
function check_point($point)
{
    if (!preg_match('/^[0-9]+$/', $point)) {
        return false;
    }

    return true;
}

function get_single_key(array $array1, $key)
{
    $array2 = array();
    foreach ($array1 as $val) {
        !empty($val[$key]) && $array2[] = $val[$key];
    }

    return $array2;
}

/**
 * 检测密码是否是弱密码
 *
 * @access  public
 *
 * @param   string  $password   密码
 *
 * @return  bool
 */
function is_weak_password($password = null)
{
    if (empty($password) || strlen($password) < 8 || 1 === preg_match('/^\d+$/', $password)) {
        return true;
    }

    return false;
}

/**
 * date diff
 * @param  string day, string type, int diff
 * example:
 * 		next year day_diff(day, 'year', 1)
 * 		last month day_diff(day, 'month', -1)
 * 		3 days ago day_diff(day, 'day', -3)
 * @return string
 */
function day_diff($day, $type, $diff){
    list($year, $month, $day) = explode('-', $day);
    $$type = $$type + ($diff);
    return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
}

/**
 * [is_sn 编码是否正确]
 * @param  [string]  $str
 * @param  [string]  $type
 * @return [bool]
 */
if (!function_exists('is_sn'))
{
    function is_sn($str, $type)
    {
        switch (strtolower($type))
        {
            case 'product_sn':
                    return preg_match( "/^[\d]{3}[\d|\w]{2}[\d]{4}$/i",$str );
                break;
            case 'item_sn':
                    return preg_match( "/^[\d]{3}[\d|\w]{2}[\d]{4}[\d|\w]{2}$/i",$str );
                break;
            case 'sku_sn':
                    return preg_match( "/^[\d]{3}[\d|\w]{2}[\d]{4}[\d|\w]{5}$/i",$str );
                break;
            case 'barcode':
                    return preg_match( "/^[\d]{3}[\d|\w]{2}[\d]{4}[\d|\w]{5}[\d]{4}$/i",$str );
                break;
            case 'size':
                    return preg_match( "/^[\d]{2}$/i",$str );
                break;
            case 'brand_sn':
                    return preg_match( "/^[\d|\w]{2}$/i",$str );
                break;
            case 'order_sn':
                    return preg_match( "/^[a-zA-Z]{2}\d{11}$/i",$str );
                break;
            default:
                    return false;
                break;
        }
    }
}

/**
 * [to_sn 转换成其他编码]
 * @param  [string]  $str
 * @param  [string]  $type
 * @return [bool]
 */
if (!function_exists('to_sn'))
{
    function to_sn($str, $type)
    {
        $str = trim($str);
        switch (strtolower($type))
        {
            case 'product_sn':
                    $str = substr(strtoupper($str), 0, 9);
                break;
            case 'item_sn':
                    $str = substr(strtoupper($str), 0, 11);
                break;
            case 'sku_sn':
                    $str = substr(strtoupper($str), 0, 14);
                break;
            case 'barcode':
                    $str = substr(strtoupper($str), 0, 18);
                break;
            case 'size':
                    $str = substr($str, 11, 2);
                break;
            case 'brand_sn':
                    $str = substr(strtoupper($str), 3, 2);
                break;
            case 'order_sn':
                    $str = substr(strtoupper($str),0,13);
                break;
            default:
                    return false;
                break;
        }
        if(!is_sn($str, $type))
            return false;

        return $str;
    }
}

/**
 * [money 格式化为货币单位]
 * @param  [string]  $str
 * @return [string]
 */
if (!function_exists('moneyval'))
{
    function moneyval($str)
    {
        if( (float)$str<=0 )
            return 0;

        return (float)sprintf("%.2f",$str);
    }
}

/**
 * [object_to_array 对象转为数组]
 * @param  [object]  $obj
 * @return [array]
 */
if (!function_exists('object_to_array'))
{
    function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val)
        {
            $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
}

/**
 * [_array_to_object 数组转为对象]
 * @param  [object]  $obj
 * @return [array]
 */
if (!function_exists('array_to_object'))
{
    function array_to_object($array)
    {
        foreach($array as $key => $value)
        {
            if(is_array($value))
            {
                $array[$key] = array_to_object($value);
            }
        }
        return (object)$array;
    }
}

/**
 * [is_xml 判断是否为XML]
 * @param  [string]  $str
 * @return [bool]
 */
if (!function_exists('is_xml'))
{
    function is_xml($str)
    {
        return preg_match('/^\<.*?\>.*\<.*?\>$/', $str);
    }
}

/**
 * [xmlstr2arr 输入一个xml string 转出为array]
 * @param  [string]     $str
 * @param  [bool]       $cdata_filter      是否需要过滤<![CDATA[]]>中的内容
 * @return [array]
 */
if (!function_exists('xml2arr'))
{
    function xml2arr($str, $cdata_filter = true)
    {
        $str     = trim($str);
        if(empty($str)) return array();

        // 编码和检测一致
        $charset = mb_detect_encoding($str);
        $str     = preg_replace("/encoding[\s|\S]?=[\s|\S]?[\'\"][^\'\"]+[\'\"]/i", "encoding=\"$charset\" ", $str);

        if(false === $cdata_filter){
            $xml = simplexml_load_string($str,null,LIBXML_NOCDATA);
        }
        else{
            $xml = simplexml_load_string($str);
        }
        return object_to_array($xml);
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
        if ((php_sapi_name() === 'cli' OR defined('STDIN'))) {
            // 如果是命令行模式
            return array(
                'user_id'    => 1,
                'login_name' => 'system',
                'userName'   => 'system',
                'deptId'     => '',
                'groupId'    => '',
                'groupName'  => '',
                'deptName'   => '',
            );

        } else if (empty($_SESSION['admin']['user_id'])) {
            return array();

        } else {
            return array(
                'user_id'        => $_SESSION['admin']['user_id'],
                'login_name'     => $_SESSION['admin']['login_name'],
                'userName'       => $_SESSION['admin']['userName'],
                'deptId'         => $_SESSION['admin']['deptId'],
                'groupId'        => $_SESSION['admin']['groupId'],
                'groupName'      => $_SESSION['admin']['groupName'],
                'deptName'       => $_SESSION['admin']['deptName'],
                'bind_warehouse' => $_SESSION['admin']['bind_warehouse'],
                'userBranchId'   => $_SESSION['admin']['userBranchId'],
            );
        }
    }
}

/**
 * 追加用户信息
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
        $user = get_operate_user();
        $params[$key . '_time']      = date('Y-m-d H:i:s');
        $params[$key . '_user_id']   = $user['user_id'];
        $params[$key . '_user_name'] = $user['userName'];
    }
}

/**
 * 追加用户创建信息
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
 * 追加用户更新信息
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
 * 追加用户审核信息
 *
 * @access  public
 *
 * @params  array   $params     追加审核信息到此数组中
 *
 * @return  void
 */
if (!function_exists('append_audit_info')) {
    function append_audit_info(&$params)
    {
        append_user_info($params, 'audit');
    }
}

/**
 * 追加用户更新信息
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
 * 获取两个时间段之间相差的月份数
 *
 * @access  public
 *
 * @params  array   $params     追加用户信息到此数组中
 *
 * @return  void
 */
if (!function_exists('get_num_by_twomonth')) {
    function get_num_by_twomonth($start_time, $end_time)
    {
        if ($start_time > $end_time) {
            $month      = $start_time;
            $start_time = $end_time;
            $end_time   = $month;
        }

        $start_time = explode('-', $start_time);
        $end_time   = explode('-', $end_time);

        $start_year  = intval($start_time[0]);
        $start_month = intval($start_time[1]);

        $end_year  = intval($end_time[0]);
        $end_month = intval($end_time[1]);

        // 同一年的 不同月份
        if ($start_year == $end_year) {
            $result = intval($end_month - $start_month) + 1;
        // 不同年
        } else if ($start_year < $end_year) {
            $result = intval($end_year - $start_year) * 12 + ($end_month - $start_month) + 1;
        }

        return $result;
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
            $output .= '<script src="' . HOME_DOMAIN . 'public/js/' . $item . '?v=' . JS_VERSION . '" type="text/javascript"></script>';
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
            $output .= '<link href="' . HOME_DOMAIN . 'public/css/' . $item . '?v=' . CSS_VERSION . '" rel="stylesheet" type="text/css" />';
        }
        return $output;
    }
}


/**
 * 根据角色组绑定的仓库区别数据
 *
 * @param   array   $where_in
 *
 * @return  string
 */
if (!function_exists('warehouse_where_in')) {
    function warehouse_where_in($where_in = array())
    {
        $user = get_operate_user();

        $warehouse_where_in = array(
            'warehouse_id' => ($user['groupId'] == SYSTEM_GROUP_ID) ? null : $user['bind_warehouse'],
        );

        if (isset($where_in) && is_array($where_in) && !empty($where_in)) {
            $where_in = array_merge($warehouse_where_in, $where_in);
        } else {
            $where_in = $warehouse_where_in;
        }

        return $where_in;
    }
}

/**
 * 两个数据相除
 *
 * @param   float   $total      除数
 * @param   float   $number     被除数
 * @param   float   $scale      截取小数点位数
 *
 * @return  float
 */
if (!function_exists('data_bcdiv')) {
    function data_bcdiv($total = null, $number = null, $scale = 4)
    {
        data_filter($total);
        data_filter($number);

        if (0 == intval($number)) {
            return 0;
        }

        if (intval($scale) > 0) {
            return bcdiv($total, $number, $scale);
        } else {
            return bcdiv($total, $number, 4);
        }
    }
}