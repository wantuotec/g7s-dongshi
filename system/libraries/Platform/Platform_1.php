<?php
/**
 * 饿了么平台
 *
 * @author      杨海波
 * @date        2015-05-09
 * @copyright   Copyright(c) 2015
 *
 * @version     $Id$
 */
class CI_Platform_1 extends CI_Platform
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
            $this->__request_url = 'http://dist-api.ele.me';
            $this->__auth_key    = '';
            $this->__secret_key  = '';
        } else {
            // testing 或者 development
            $this->__request_url = 'http://dist-api.eledev.me';
            $this->__auth_key    = 'd1fa7276c17e104fe9b848bf0ea34f7b179ca4cf58864a43549cac7542e6573a';
            $this->__secret_key  = '510d0f712b4337abdd2d4febc095b79c';
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
// dump($response, $http_code);exit('--------+++++++++++++++++++++++++-------');
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
     * 订单格式转换
     *
     * @param   string  $params     请求参数
     *
     * @return  array
     */
    private function __transform_order($params = array())
    {
        $location = array();    // 订单经纬度

        // todo liunian 2015-06-16 获取我们合作的商家信息,如果没有找到商家我们提示没有合作
        $this->load->model('Shop_platform_model');
        $shop = $this->Shop_platform_model->get_by_outer_sn($params['restaurant']['id'], $params['platform_id'],'shop_id, shop_name, platform_name');

        if (false === $shop) {
            $this->set_error($this->Shop_platform_model->get_error());
            return false;
        }

        if (empty($shop)) {
            $this->set_error('此商家没有跟我们合作，不能接收此商家的订单');
            return false;
        }

        // 如果有经纬度，我们这边转换
        if ($params['consignee']['location']['latitude'] || $params['consignee']['location']['longitude']) {
            $this->load->library('baidumap');
            $location = $this->baidumap->GCJ02_to_BD09($params['consignee']['location']['latitude'], $params['consignee']['location']['longitude']);
        }

        // todo liunian 经纬度是 GPS 坐标，要转成百度坐标
        $order = array(
            'serial_number'        => $params['general']['daySn'],
            'platform_id'          => intval($this->platform_id),
            'platform_name'        => $shop['platform_name'],
            'platform_sn'          => $params['id'],
            'platform_create_time' => date('Y-m-d H:i:s', $params['general']['createdTime']),
            'actual_amount'        => $params['detail']['total'],
            'discount_amount'      => 0,
            'is_collect'           => 'online' == $params['general']['payment'] ? '2' : '1',
            'is_reserve'           => $params['general']['isBooked'] ? 1 : 2,
            'reserve_time'         => empty($params['general']['bookedTime']) ? '0000-00-00 00:00:00' : date('Y-m-d H:i:s', $params['general']['bookedTime']),
            'receive_name'         => $params['consignee']['name'] ? '' : $params['consignee']['name'],
            'receive_phone'        => $params['consignee']['phones'][0],
            'address'              => $params['consignee']['address'],
            'latitude'             => false != $location ? $location['latitude']  : 0,
            'longitude'            => false != $location ? $location['longitude'] : 0,
            'shop_id'              => empty($shop['shop_id']) ? 0 : intval($shop['shop_id']),
            'shop_name'            => $shop ? $shop['shop_name'] : '',
            'remark'               => $params['general']['remark'],
            'add_type'             => 2,
        );

        if (empty($params['general']['bookedTime'])) {
            unset($order['reserve_time']);
        }

        return $order;
    }

    /*
    // 一定要有的
    receive_order
    cancel_order(我方取消订单)
    cancel_order_by_platform(平台取消订单)
    confirm_order
    delivery_order
    finish_order

    // 每个平台单独的
    get_order
    get_shop
    comfirm_shop
    update_shop
    */

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

        // todo liunian 两种方式直接post 过来数据，或者走总接口平台
        /*
        $request = trim(file_get_contents("php://input"));

        if (empty($request)) {
            $this->set_error(20001);
        }

        $request = json_decode($request, true);

        // 检查是不是一个有效的 JSON 字符串
        if (empty($request) || !is_array($request)) {
            $this->set_error(20002);
        }
        */

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
        } else {
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
            // todomark 记录下原始信息，解决
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
        // cancel
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
     * 我方取消订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function cancel_order($params = array())
    {
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

        $uri = '/v1/eleme/orders/' . $params['platform_sn'] . '/delivery/cancel';

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
            'platform_sn' => $params['platform_sn'], // 平台单号
        );

        $data = array();

        $uri = '/v1/eleme/orders/' . $params['platform_sn'] . '/delivery/accept';

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

        $data = array(
            'deliveryOrderId'   => $params['platform_sn'],
            'status'            => 'confirmed',
            'deliverymanName'   => $params['courier_name'],
            'deliverymanMobile' => $params['courier_phone'],
            'description'       => '配送员已接单',
        );

        $uri = '/v1/eleme/orders/' . $params['platform_sn'] . '/delivery/update';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        $data = array(
            'deliveryOrderId'   => $params['platform_sn'],
            'status'            => 'started',
            'deliverymanName'   => $params['courier_name'],
            'deliverymanMobile' => $params['courier_phone'],
            'description'       => '开始配送',
        );

        $uri = '/v1/eleme/orders/' . $params['platform_sn'] . '/delivery/update';

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
        // complete
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

        $data = array(
            'deliveryOrderId'   => $params['platform_sn'],
            'status'            => 'complete',
            'deliverymanName'   => $params['courier_name'],
            'deliverymanMobile' => $params['courier_phone'],
            'description'       => '配送完成',
        );

        $uri = '/v1/eleme/orders/' . $params['platform_sn'] . '/delivery/update';

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
        if (empty($params['platform_sn'])) {
            $this->set_error('平台单号不能为空');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn' => $params['platform_sn'], // 商家在平台的唯一编号
        );

        $data = array();

        $uri = '/v1/eleme/orders/' . $params['platform_sn'];

        $result = $this->__request($uri, $data, 'GET');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        $result = $this->__transform_order($result);

        return $result;
    }

    /**
     * 获取餐厅信息
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function get_shop($params = array())
    {
        if (empty($params['shop_outer_sn'])) {
            $this->set_error('请填写平台商家SN');
            return false;
        }

        // 输入参数
        $params = array(
            'shop_outer_sn' => $params['shop_outer_sn'], // 商家在平台的唯一编号
        );

        $data = array();

        $uri = '/v1/eleme/restaurants/' . $params['shop_outer_sn'];

        $result = $this->__request($uri, $data, 'GET');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // 转换成我方
        $result = array(
            'shop_outer_sn'        => $result['id'],
            'shop_outer_name'      => $result['name'],
            'shop_outer_address'   => $result['address'],
            'shop_outer_phone'     => implode(',', $result['phones']),
            'shop_outer_city_code' => $result['location']['cityCode'], // 餐厅所在城市的区号（如 010 / 021 等）
            'shop_outer_city_name' => $result['location']['cityName'], // 餐厅所在城市名称（如 北京 / 上海 等）
            'latitude'             => $result['location']['latitude'],
            'longitude'            => $result['location']['longitude'],
        );

        return $result;
    }

    /**
     * 确认开通餐厅配送服务
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function comfirm_shop($params = array())
    {
        if (empty($params['shop_outer_sn'])) {
            $this->set_error('请填写平台商家SN');
            return false;
        }

        // 输入参数
        $params = array(
            'shop_outer_sn' => $params['shop_outer_sn'], // 商家在平台的唯一编号
        );

        $data = array();

        $uri = '/v1/eleme/restaurants/' . $params['shop_outer_sn'] . '/distribution/confirm';

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 第三方配送服务的费用描述
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function update_shop($params = array())
    {
        if (empty($params['shop_outer_sn'])) {
            $this->set_error('请填写平台商家SN');
            return false;
        }

        // 输入参数
        $params = array(
            'shop_outer_sn' => $params['shop_outer_sn'], // 商家在平台的唯一编号
            'description'   => $params['description'],   // 运费描述
        );

        $uri = '/v1/eleme/restaurants/' . $params['shop_outer_sn'] . '/distribution/update';

        $data = array(
            'chargingDescription' => $params['description'],
        );

        $result = $this->__request($uri, $data, 'POST');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
    // ===================== 以上函数一般来说是每个平台单独拥有的 ====================
}