<?php
/**
 * 一米线平台对接
 *
 * @author      刘念
 * @date        2015-07-17
 * @copyright   Copyright(c) 2015
 *
 * @version     $Id$
 */
class CI_Platform_6 extends CI_Platform
{
    // 接口地址
    private $__request_url = null;

    // 认证 key
    private $__auth_key    = null;

    // 加密密钥
    private $__secret_key  = null;

    // 网络超时
    private $__timeout     = 30;

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        if ('production' === ENVIRONMENT) {
            $this->__request_url = 'http://api.1mxian.com';
            $this->__auth_key    = '';  // user_key 账号
            $this->__secret_key  = '';  // token    密码
        } else {
            // testing 或者 development
            $this->__request_url = 'http://api.test.1mxian.com';
            $this->__auth_key    = '';
            $this->__secret_key  = '';
        }
    }

    /**
     * 网络请求数据加密
     *
     * @access  public
     *
     * @param   string  $method     请求方式
     * @param   string  $path       请求路径
     * @param   array   $params     输入参数
     *
     * @return  string
     */
    public function __sign($method, $path, $params = array())
    {
        $sorted = array();
        foreach($params as $key => $value)
        {
            array_push($sorted, "$key=$value");
        }
        sort($sorted);

        $str = $method . $path . implode('', $sorted) . $this->__secret_key;

        return md5($str);
    }

    /**
     * 使用 curl 发起网络请求
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string/false
     */
    private function __curl($url, $params = array(), $method = 'GET', $timeout = 30)
    {
        $data = '';
        if (!empty($params) && is_array($params)) {
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

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 数据未完整读取, 网络请求超时
        if(0 === $http_code) {
            $this->set_error('请求失败');
            return false;
        }

        // 请求成功，返回正确，没有返回JSON 结构体
        if ('' === $response && 200 == $http_code) {
            return true;
        }

        // 请求成功，或者请求失败后，有返回的json 的结构体
        $response = json_decode($response, true);
        if (empty($response) || !is_array($response)) {
            $this->set_error('返回结果不是一个有效的JSON结构体');
            return false;
        }

        // 返回的状态码不为200 的有返回数据提示的
        if (200 != $http_code) {
            $this->set_error($response['message']);
            return false;
        }

        return $response;
    }

    /**
     * 网络请求
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string
     */
    private function __request($uri, $params = array(), $method = 'POST', $timeout = null)
    {
        // 合并固定参数
        $params = array_merge(array(
            'authKey'     => $this->__auth_key,
            'requestTime' => time(),
        ), $params);

        $params['requestSignature'] = $this->__sign($method, $uri, $params);

        $result = $this->__curl($this->__request_url . $uri, $params, $method, is_null($timeout) ? $this->__timeout : intval($timeout));

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } elseif (true === $result) {
            return true;
        }

        return $result;
    }

    /**
     * 根据平台ID跟平台订单号获取具体订单
     *
     * @param   string  $platform_sn    平台订单号
     *
     * @return  bool|array
     */
    private function get_order_by_platform($platform_sn = '')
    {
        if (empty($platform_sn)) {
            $this->set_error('平台单号为空');
            return false;
        }

        $this->load->model('Order_model');
        $order_result = $this->Order_model->get_order_by_platform_sn(intval($this->platform_id), $platform_sn);

        // 取站点数据
        $this->load->model('Branch_model');
        $branch_result = $this->Branch_model->get_by_id($order_result['branch_id'], 'branch_id, branch_name');
        $order_result['branch_name'] = $branch_result['branch_name'];

        $order_status_list = $this->Order_model->order_status;
        $order_result['order_status_title'] = $order_status_list[$order_result['order_status']];
 
        return $order_result;
    }

    /**
     * 订单格式转换
     *
     * @param   array   $params     请求参数
     *
     * @return  array
     */
    private function __transform_order($params = array())
    {
        $location = []; // 收货人经纬度
        $this->load->library('baidumap');

        $order = array(
            'platform_id'          => intval($this->platform_id),   // 系统过来的
            'platform_name'        => $this->platform_name,
            'platform_sn'          => $params['platform_sn'],
            'platform_create_time' => date('Y-m-d H:i:s', strtotime($params['platform_create_time']['date'])),
            'actual_amount'        => $params['actual_amount'],
            'discount_amount'      => $params['discount_amount'],
            'is_collect'           => isset($params['is_collect']) && 1 == intval($params['is_collect']) ? 1  : 2,
            'is_reserve'           => isset($params['is_reserve']) && 1 == intval($params['is_reserve']) ? 1  : 2,
            'reserve_time'         => $params['reserve_time'],
            'receive_name'         => $params['receive_name']       ? $params['receive_name']       : '',
            'receive_phone'        => $params['receive_phone']      ? $params['receive_phone']      : '',
            'province_name'        => $params['province_name']      ? $params['province_name']      : '',
            'city_name'            => $params['city_name']          ? $params['city_name']          : '',
            'district_name'        => $params['district_name']      ? $params['district_name']      : '',
            'address'              => $params['address']            ? $params['address']            : '',
            'latitude'             => $params['latitude']           ? $params['latitude']           : 0,
            'longitude'            => $params['longitude']          ? $params['longitude']          : 0,
            'from_province_name'   => $params['from_province_name'] ? $params['from_province_name'] : '',
            'from_city_name'       => $params['from_city_name']     ? $params['from_city_name']     : '',
            'from_district_name'   => $params['from_district_name'] ? $params['from_district_name'] : '',
            'from_name'            => $params['send_name']          ? $params['send_name']          : '',
            'from_phone'           => $params['send_mobile']        ? $params['send_mobile']        : '',
            'from_address'         => $params['from_address']       ? $params['from_address']       : '',
            'from_latitude'        => $params['from_latitude']      ? $params['from_latitude']      : 0,
            'from_longitude'       => $params['from_longitude']     ? $params['from_longitude']     : 0,
            'remark'               => $params['remark']             ? $params['remark']             : '',
            'add_type'             => 2,    // 标示是平台推送
        );

        // 自动分配配送的站点
        $this->load->model('Order_assign_model');
        $branch = $this->Order_assign_model->assign_branch($order);

        // 如果 $branch === true  表示原始 $params里有 shop_id 或者 branch_id
        // 如果 $branch === false 表示无法完成分配站点的动作
        // 如果 $branch 为 array  表示已经正确的分配了站点

        // 无法分配站点
        if (false === $branch) {
            $this->set_error($this->Order_assign_model->get_error());
            return false;
        }

        if (!empty($branch) && is_array($branch)) {
            $order['from_city_name'] = $branch['from_city_name'];
            $order['from_address']   = $branch['from_address'];
            $order['from_latitude']  = $branch['from_latitude'];  // 可能原始数据里没有经纬度，分配站点的时候会计算出来，并到这里
            $order['from_longitude'] = $branch['from_longitude'];
            $order['branch_id']      = $branch['branch_id'];
        }
        unset($branch);

        // 如果收货人的经纬度为空，并且有收货地址，去算收货人的经纬度
        if ((empty($order['latitude']) || empty($order['longitude'])) && $order['city_name'] && $order['address']) {
            $location = $this->baidumap->address_to_location($order['address'], $order['city_name']);
            $order['latitude']  = $location['latitude'];
            $order['longitude'] = $location['longitude'];
            unset($location);
        }

        return $order;
    }

    // ===================== 以下函数一般来说是一定要实现的 ====================
    /**
     * 接收平台推送过来的订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function receive_order($params = array())
    {
        // 一定是 POST 方式
        if (!$this->input->is_post_request()) {
            $this->set_error(20029);
        }

        $order = $this->__transform_order($params);
        if (false === $order) {
            $this->set_error($this->get_error());
            return false;
        }

        // 根据平台跟平台的订单号查找我们系统是否有同步这个订单的信息
        $this->load->model('Order_model');
        $platform_result = $this->Order_model->get_order_by_platform_sn($order['platform_id'], $order['platform_sn'], 'order_id, order_status');

        if (false === $platform_result) {
            $this->set_error($this->Order_model->get_error());
            return false;
        } else if ($platform_result) {
            $order_status = intval($platform_result['order_status']);

            if (10 == $order_status) {
                $this->set_error('此订单已推送，正在派单');
                return false;
            } else if (20 == $order_status) {
                $this->set_error('此订单已推送，已分配到配送员');
                return false;
            } else if (30 == $order_status) {
                $this->set_error('此订单已推送，配送员正在配送中');
                return false;
            } else if (40 == $order_status) {
                $this->set_error('此订单已推送，配送员配送完成，已送到客户手上');
                return false;
            } else if (99 == $order_status) {
                $this->set_error('此订单已推送，处于取消状态');
                return false;
            }
        }

        // 把新的订单添加到我们系统中，进去派单系统进行派单
        $result = $this->Order_model->add_order_single($order);

        if (false === $result) {
            $this->set_error($this->Order_model->get_error());
            return false;
        }

        return true;
    }

    /**
     * 平台取消订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function cancel_order_by_platform($params = array())
    {
        if (empty($params['platform_sn'])) {
            $this->set_error('平台单号为空');
            return false;
        }

        $this->load->model('Order_model');
        $result = $this->Order_model->cancel_order_by_platform(array(
            'platform_id' => intval($this->platform_id),
            'platform_sn' => $params['platform_sn'],
        ));

        if (false == $result) {
            $this->set_error($this->Order_model->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 我方取消订单()
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function cancel_order($params = array())
    {
        // todo liunian 没有接口
        return true;

        // cancel
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
            'cancel_reason' => $params['cancel_reason'], // 取消原因
        );

        $data = array(
            'reason' => $params['cancel_reason'],
        );

        $uri = '/order/' . $params['platform_sn'] . '/cancel';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 确认配送订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function confirm_order($params = array())
    {
        // accept
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn' => $params['platform_sn'],         // 平台单号
        );

        // 查订单数据
        $order = $this->get_order_by_platform($params['platform_sn']);

        $data  = array(
            'user_key'     => $this->__auth_key,
            'token'        => $this->__secret_key,
            'order_id'     => $params['platform_sn'],
            'state'        => $order['order_status_title'],
            'transport'    => $order['branch_name'],
            'operate_time' => $order['comfirm_time'],
            'info'         => '配送员已接单',
        );

        $uri = '/order/' . $params['platform_sn'] . '/update';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 确认开始配送
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function delivery_order($params = array())
    {
        // confirm start
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
            'courier_name'  => $params['courier_name'],  // 配送员名称
            'courier_phone' => $params['courier_phone'], // 配送员账号
        );

        // 查订单数据
        $order = $this->get_order_by_platform($params['platform_sn']);

        $data = array(
            'user_key'       => $this->__auth_key,
            'token'          => $this->__secret_key,
            'order_id'       => $params['platform_sn'],
            'courier'        => $params['courier_name'],
            'courier_mobile' => $params['courier_phone'],
            'transport'      => $order['branch_name'],
            'operate_time'   => $order['delivery_time'],
            'state'          => $order['order_status_title'],
            'info'           => '配送员已开始配送',
        );

        $uri = '/order/' . $params['platform_sn'] . '/update';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 配送完成
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function finish_order($params = array())
    {
        // finsh
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],        // 平台单号
            'courier_name'  => $params['courier_name'],       // 配送员名称
            'courier_phone' => $params['courier_phone'],      // 配送员账号
        );

        // 查订单数据
        $order = $this->get_order_by_platform($params['platform_sn']);

        $data = array(
            'user_key'       => $this->__auth_key,
            'token'          => $this->__secret_key,
            'order_id'       => $params['platform_sn'],
            'courier'        => $params['courier_name'],
            'courier_mobile' => $params['courier_phone'],
            'transport'      => $order['branch_name'],
            'operate_time'   => $order['arrive_time'],
            'state'          => $order['order_status_title'],
            'info'           => '配送员配送完成',
        );

        $uri = '/order/' . $params['platform_sn'] . '/update';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
    // ===================== 以上函数一般来说是一定要实现的 ====================

    // ===================== 以下函数一般来说是每个平台单独拥有的 ====================
    /**
     * 获取订单信息
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function get_order($params = array())
    {
        // todo liunian 没有接口
        return true;

        if (empty($params['platform_sn'])) {
            $this->set_error('平台单号不能为空');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn' => $params['platform_sn'], // 商家在平台的唯一编号
        );

        $data = array();

        $uri = '/order/' . $params['platform_sn'] . '/get';

        $result = $this->__request($uri, $data, 'GET');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        $result = $this->__transform_order($result);

        return $result;
    }
    // ===================== 以上函数一般来说是每个平台单独拥有的 ====================
}