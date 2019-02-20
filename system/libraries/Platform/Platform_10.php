<?php
/**
 * 来客平台
 *
 * @author      杨海波
 * @date        2015-08-10
 * @copyright   Copyright(c) 2015
 *
 * @version     $Id$
 */
class CI_Platform_10 extends CI_Platform
{
    // 调式模式
    protected $_is_debug   = false;

    // 平台ID
    public $platform_id   = null;

    // 平台名称
    public $platform_name = null;

    // 接口地址
    private $__request_url = null;

    // 认证 key
    private $__auth_key    = null; // 对应来客的uuid

    // 加密密钥
    private $__secret_key  = null;

    // 网络超时
    private $__timeout     = 30;

    private $__error_list  = [
        1 => '接单失败（订单已被处理）',
        2 => '订单不存在',
        3 => '信息字段缺失或格式错误',
        4 => '配送动作非法',
        5 => '其他请求尚未处理完毕，请稍后重试',
    ];

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        if ('production' === ENVIRONMENT) {
            $this->__request_url = 'https://api.paadoo.com';
            $this->__auth_key    = '3nXSmCTH';
            $this->__secret_key  = 'ec92ab7f3a74852c3b25bbc4ed03f5ec';
        } else {
            // http://xxx.xxxx.xxx/callDeliveryOrder?channel="合作伙伴提供来客渠道编号"&sign="数字加密报文"
            // testing 或者 development
            $this->__request_url = 'https://101.226.164.140';
            $this->__auth_key    = 'dcBugp1z';
            $this->__secret_key  = 'dc8155d28ee665311239cd0d032f4e2a';
        }
    }

    /**
     * 网络请求数据加密
     *
     * @access  public
     *
     * @param   string  $method     待加密数据
     *
     * @return  string
     */
    public function __sign($data)
    {
        // $this->_add_log('sha256', $data .$this->__secret_key);
        // $this->_add_log('sha256', hash('sha256', $data .$this->__secret_key));
        return hash('sha256', $data . $this->__secret_key);
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
     * @return  string/bool
     */
    private function __curl($url, $params = array(), $method = 'GET', $timeout = 30, $is_ca = false)
    {
        $response = $this->_curl($url, $params, $method, $timeout, $is_ca);
        $this->_add_log('request', "method:{$method} url:{$url} params:" . var_export($params, true));

        if (false === $response) {
            $this->_add_log('response error', $this->get_error());
            $this->set_error($this->get_error());
            return false;
        }

        $this->_add_log('response origin', $response);

        // 请求成功，或者请求失败后，有返回的json 的结构体
        $response = json_decode($response, true);
        if (empty($response) || !is_array($response)) {
            $this->set_error('返回结果不是一个有效的JSON结构体');
            return false;
        }

        // 返回的状态码不为200 的有返回数据提示的
        if (0 !== $response['stat']) {
            $message = isset($this->__error_list[$response['stat']]) ? $this->__error_list[$response['stat']] : '';
            $this->set_error($message . $response['data']['mesg']);
            $this->_add_log('response error', $message . $response['data']['mesg']);
            return false;
        }

        $this->_add_log('response', $response);
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
        // 来客单独分成 __request_update_order 及 __request_get_order 两个方法
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
    private function __request_update_order($params = array(), $method = 'POST', $timeout = null)
    {
        // 发送的订单ID应当是32字节UUID，其中前16字节是店铺ID， 后16字节是来客订单ID，中间没有任何分隔符。
        // https://api.paadoo.com/callback.order-delivery/?client_id=1&orderid_id=e30Q5glmXAgGwDCcbd2YwlPUXJwaK78D&action=2&dm_phone=13552981024

        $this->load->model('Order_model');
        $order = $this->Order_model->get_order_by_platform_sn(intval($this->platform_id), $params['platform_sn'], 'order_id, confirm_phone, confirm_name, courier_phone, courier_name');

        if (false === $order) {
            $this->set_error($this->Order_model->get_error());
            return false;
        }

        $params['courier_id']    = ''; // 不回传给来客
        $params['courier_name']  = $order['confirm_name'];
        $params['courier_phone'] = $order['confirm_phone'];

        if (!empty($order['courier_phone'])) {
            $params['courier_id']    = ''; // 不回传给来客
            $params['courier_name']  = $order['courier_name'];
            $params['courier_phone'] = $order['courier_phone'];
        }

        $params = array(
            'client_id' => 'eOlySlbv',               // 配送平台ID 固定值
            'order_id'  => $params['platform_sn'],   // 订单ID（带店铺ID，32字节）
            'action'    => $params['action'],        // 配送动作（1=接单；2=提单；3=送达；4=取消）
            'memo'      => $params['memo'],          // 附加信息（例如订单取消的原因，可选）
            'dm_id'     => $params['courier_id'],    // 配送员ID（可选）
            'dm_name'   => $params['courier_name'],  // 配送员姓名（可选）
            'dm_phone'  => $params['courier_phone'], // 配送员手机号
            'time'      => time(),                   // 客户端时间戳（可选，忽略则使用服务器端时间）
        );

        // 把为null 为空的配置过滤完
        invalid_data_filter_recursive($params);

        $uri = '/callback.order-delivery/';
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
     * 网络请求
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string
     */
    private function __request_get_order($params = array(), $method = 'GET', $timeout = null)
    {
        $params = array(
            '_u' => $this->__auth_key,      // 客户端设备标识，由来客分配给合作伙伴的UUID。长度为6～16个字符。
            '_v' => '1.0.0',                // 客户端版本号。由来客合作伙伴设定的其产品的版本号，需遵循major.minor.revision**命名规范，例如1.0.2345。
            '_c' => 'laikexianjisong',      // 客户端UUID 需要8-40位
            'id' => $params['platform_sn'],
        );

        $uri = '/ext.order.get2/';
        $result = $this->__curl($this->__request_url . $uri, $params, $method, is_null($timeout) ? $this->__timeout : intval($timeout));

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } elseif (true === $result) {
            return true;
        }

        // 转换原始订单信息
        $result = $this->__transform_origin_order($result['data']);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 原始订单格式转换
     *
     * @param   string  $params     请求参数
     *
     * @return  array
     */
    private function __transform_origin_order($params = array())
    {
        // 校验原始订单数据的合法性
        if (empty($params['shop']['id'])) {
            $this->set_error('店铺ID为空');
            return false;
        }

        if (empty($params['order']['id'])) {
            $this->set_error('订单ID为空');
            return false;
        }

        if (empty($params['cust']['phn'])) {
            $this->set_error('收货人电话为空');
            return false;
        }

        if (empty($params['cust']['add'])) {
            $this->set_error('收货人地址为空');
            return false;
        }

        // 计算订单优惠 优惠是放在商品条目里的
        $discount_amount = 0;
        $items = array();
        if (!empty($params['items']) && is_array($params['items'])) {
            foreach ($params['items'] as $val) {
                // "item": "商品ID",
                // "rel": "相关订单条目ID（type=1:商品；type=2：套餐）",
                // "name": "商品名称",
                // "spec": "商品规格",
                // "amt": <商品金额>,
                // "count": <商品数量>,
                // "memo": "商品备注信息"//商品选项等信息
                $items[] = array(
                    'shop_outer_sn'        => empty($params['shop']['id'])    ? '' : $params['shop']['id'],
                    'shop_outer_name'      => empty($params['shop']['name'])  ? '' : $params['shop']['name'],
                    'item_outer_sn'        => empty($val['item'])  ? '' : $val['item'],
                    'item_outer_name'      => empty($val['name'])  ? '' : $val['name'],
                    'item_outer_number'    => empty($val['count']) ? 0  : intval($val['count']),
                    'item_outer_price'     => empty($val['amt'])   ? 0  : floatval($val['amt']),
                    'item_outer_discount'  => 0,
                    'item_outer_type'      => empty($val['rel'])   ? '' : $val['rel'],
                    'item_outer_type_name' => empty($val['spec'])  ? '' : $val['spec'],
                    'item_outer_memo'      => empty($val['memo'])  ? '' : $val['memo'],
                );

                if ($val['amt'] < 0) { // 优惠金额
                    $discount_amount = bcadd($discount_amount, bcmul(abs($val['amt']), $val['count'], 3), 3);
                }
            }
        }

        $serial_number = '';
        if (!empty($params['order']['no'])) {
            list(, $serial_number) = explode('#', $params['order']['no'], 2);
        }

        // 订单格式转换
        $params = array(
            'platform_id'          => intval($this->platform_id),
            'platform_name'        => '',
            'platform_sn'          => $params['shop']['id'] . $params['order']['id'],
            'serial_number'        => $serial_number,
            'platform_create_time' => $params['order']['created'],
            'actual_amount'        => $params['order']['total'],
            'discount_amount'      => $discount_amount,
            'is_collect'           => $params['order']['recamt'] > 0 ? '1' : '2',
            'is_reserve'           => $params['order']['exp'] > 0 ? 1 : 2,
            'reserve_time'         => empty($params['order']['exp']) ? '0000-00-00 00:00:00' : date('Y-m-d H:i:s', strtotime(substr($params['order']['exp'], 0, 19))),
            'receive_name'         => $params['cust']['sal'],
            'receive_phone'        => $params['cust']['phn'],
            'address'              => $params['cust']['add'],
            'latitude'             => 0,
            'longitude'            => 0,
            'shop_id'              => 0,
            'shop_name'            => '',
            'remark'               => $params['order']['memo'],
            'courier_id'           => 0,
            'shop_outer_sn'        => $params['shop']['id'],
            // 'src'        => $params['order']['src'], // todomark 先放在这时，看什么时候需要 00-pos;01-美团外卖;02-饿了么;03-百度;04-淘点点;05-零号线;06-京东
            'items'                => !empty($items) && is_array($items) ? $items : array(),
        );

        return $params;
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

        // 获取我们合作的商家信息,如果没有找到商家我们提示没有合作
        $this->load->model('Shop_platform_model');
        $shop = $this->Shop_platform_model->get_by_outer_sn($params['shop_outer_sn'], $params['platform_id'],'shop_id, shop_name, platform_name');

        if (false === $shop) {
            $this->set_error($this->Shop_platform_model->get_error());
            return false;
        }

        if (empty($shop)) {
            $this->set_error('此商家没有跟我们合作，不能接收此商家的订单');
            return false;
        }

        // 合并商家数据
        $this->load->model('Shop_model');
        $exist_shop = $this->Shop_model->get_by_id(intval($shop['shop_id']), 'shop_id, shop_name, branch_id, city_name');
        $shop = array_merge($shop, $exist_shop);
        unset($exist_shop);

        // (扫码模式) 如果配送员和商家不是一个站点，提示请调配
        if (!empty($shop['shop_id']) && !empty($params['courier_id'])) {
            $this->load->model('Courier_model');
            $courier = $this->Courier_model->get_by_id(intval($params['courier_id']), 'branch_id, branch_name');

            if (empty($courier)) {
                $this->set_error('此配送员没有在我们的系统里注册');
                return false;
            }

            if ($shop['branch_id'] != $courier['branch_id']) {
                $this->set_error("配送员在{$courier['branch_name']}和商家不在一个站点请调配");
                return false;
            }
        }

        // 默认经纬度 来客的订单里是没有经纬度的
        $location = array(
            'latitude'  => 0,
            'longitude' => 0,
        );

        // 如果有经纬度，我们这边转换 todomark
        if (empty($params['latitude']) || empty($params['longitude'])) {
            // 取商家城市做为订单的城市
            if (!empty($shop['city_name']) && !empty($params['address'])) {
                $this->load->library('baidumap');
                $location = $this->baidumap->address_to_location($params['address'], $shop['city_name']);
            }
        }

        // 订单格式变化
        $order = array(
            'platform_id'          => intval($params['platform_id']),
            'platform_name'        => empty($shop['platform_name'])          ? '' : $shop['platform_name'],
            'platform_sn'          => empty($params['platform_sn'])          ? '' : $params['platform_sn'],
            'serial_number'        => empty($params['serial_number'])        ? '' : $params['serial_number'],
            'platform_create_time' => empty($params['platform_create_time']) ? '0000-00-00 00:00:00' : $params['platform_create_time'],
            'actual_amount'        => empty($params['actual_amount'])        ?  0 : floatval($params['actual_amount']),
            'discount_amount'      => empty($params['discount_amount'])      ?  0 : floatval($params['discount_amount']),
            'is_collect'           => empty($params['is_collect'])           ?  2 : intval($params['is_collect']),
            'is_reserve'           => empty($params['is_reserve'])           ?  2 : intval($params['is_reserve']),
            'reserve_time'         => empty($params['reserve_time'])         ? '0000-00-00 00:00:00' : $params['reserve_time'],
            'receive_name'         => empty($params['receive_name'])         ? '' : $params['receive_name'],
            'receive_phone'        => empty($params['receive_phone'])        ? '' : $params['receive_phone'],
            'address'              => empty($params['address'])              ? '' : $params['address'],
            'latitude'             => empty($params['latitude'])             ? $location['latitude']  : floatval($params['latitude']),
            'longitude'            => empty($params['longitude'])            ? $location['longitude'] : floatval($params['longitude']),
            'shop_id'              => empty($shop['shop_id'])                ?  0 : intval($shop['shop_id']),
            'shop_name'            => empty($shop['shop_name'])              ? '' : $shop['shop_name'],
            'remark'               => empty($params['remark'])               ? '' : $params['remark'],
            'courier_id'           => empty($params['courier_id'])           ? '' : intval($params['courier_id']), // 扫码时并入
            'add_type'             => 2,
            'items'                => !empty($params['items']) && is_array($params['items']) ? $params['items'] : array(),
        );

        // todomark 确认一下是否需要这个字段
        if (empty($params['reserve_time']) || '0000-00-00 00:00:00' == $params['reserve_time']) {
            unset($order['reserve_time']);
        }

        return $order;
    }

    /**
     * 添加订单
     *
     * @param   string  $params     请求参数
     *
     * @return  array
     */
    private function __add_order($params = array())
    {
        // 检验数据合法性
        if (empty($params['platform_id']) || $params['platform_id'] < 1) {
            $this->set_error('平台ID为空');
            return false;
        }

        if (empty($params['platform_sn'])) {
            $this->set_error('平台订单号为空');
            return false;
        }

        if (empty($params['receive_phone'])) {
            $this->set_error('收货人电话为空');
            return false;
        }

        if (empty($params['address'])) {
            $this->set_error('收货人地址为空');
            return false;
        }

        if (empty($params['shop_outer_sn'])) {
            $this->set_error('店铺ID为空');
            return false;
        }

        $order = $this->__transform_order($params);
        if (false === $order) {
            $this->set_error($this->get_error());
            return false;
        }

        // 根据平台跟平台的订单号查找我们系统是否有同步这个订单的信息
        $this->load->model('Order_model');
        $platform_result = $this->Order_model->get_order_by_platform_sn($order['platform_id'], $order['platform_sn'], 'order_id, order_status, branch_id, create_time, assign_id');

        if (false === $platform_result) {
            $this->set_error($this->Order_model->get_error());
            return false;
        } else if (!empty($platform_result) && is_array($platform_result)) {
            $order_status = intval($platform_result['order_status']);

            if (10 == $order_status && '' == $platform_result['show_message']) {
                // 过了5分还没有人处理的订单，重新派单（非市区不可以重新派单，在assign方法里处理）
                if (time() > strtotime($platform_result['create_time']) + 300) {
                    $this->load->model('Order_assign_model');
                    $result = $this->Order_assign_model->assign($platform_result['order_id'], true);
                }

                $this->set_error('此订单已推送，正在派单');
                return false;
            } else if (20 == $order_status && true != $platform_result['is_scan_code']) {
                $this->set_error('此订单已推送，已分配到配送员');
                return false;
            } else if (30 == $order_status) {
                $this->set_error('此订单已推送，配送员正在配送中');
                return false;
            } else if (40 == $order_status) {
                $this->set_error('此订单已推送，配送员配送完成，已送到客户手上');
                return false;

            // todo liunian 后台取消 + 配送员拒接 + 订单状态在待取单并且assign_id = 0,都能再次绑定订单
            } else if ((99 == $order_status && true == $platform_result['is_scan_code'])
                || (10 == $order_status && true == $platform_result['is_scan_code'] && empty($platform_result['assign_id']))
                || (20 == $order_status && true == $platform_result['is_scan_code'] && 10 == $platform_result['branch_type'])) {
                // 订单重新绑定配送员
                $result = $this->Order_model->re_scan_order($order);

            } else if (99 == $order_status && false == $platform_result['is_scan_code']) {
                $this->set_error('此订单已经取消,不能操作');
                return false;
            } else if (99 == $order_status) {
                $this->set_error('此订单已推送，处于取消状态');
                return false;
            } else {
                $this->set_error('此订单已推送');
                return false;
            }
        } else {
            // 把新的订单添加到我们系统中，进去派单系统进行派单
            $result = $this->Order_model->add_order_single($order);
        }

        if (false === $result) {
            // todomark 记录下原始信息，解决
            $this->set_error($this->Order_model->get_error());
            return false;
        }

        return true;
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
        // API的版本，如果为空，那么是老版本，如果为1.0.0或者更新，那么是新版本
        $this->__api_version = '';

        // 来客接收订单、更新订单、扫码订单的响应都不一样
        $response = function ($message = '成功', $is_success = false) {
            $result = array(
                'stat' => intval($is_success), // 1成功, 0失败
                'msg'  => $message,
            );

            $this->_add_log('response receive_order', $result);

            $res = json_encode($result, JSON_UNESCAPED_UNICODE);

            // 老版本要加 res= 前缀
            if (empty($this->__api_version)) {
                $res = 'res=' . $res;
            }

            exit($res);
        };

        // 方便调试，记录日志
        $request = '';
        $body    = '';
        if ($this->input->is_post_request()) {
            $api_version = $this->input->post('version');
            if (empty($api_version)) {
                $raw_post = file_get_contents("php://input");
                $this->_add_log('request receive_order post', $raw_post);

                // 通过 post 传递过来的参数，如果有+号等，经过post
                preg_match('/&body=([\s\S]+)&sign=/', $raw_post, $match);
                $body = empty($match[1]) ? '' : $match[1];
                unset($raw_post, $match);
            } else {
                $this->__api_version = $api_version;
                $this->_add_log('request receive_order post', $_POST);
            }
        } else {
            $this->_add_log('request receive_order get', $_GET);
        }

        // 一定是 POST 方式
        if (!$this->input->is_post_request()) {
             $response('请使用POST方式传递数据');
        }

        $post = $this->input->post();
        !empty($body) && $post['body'] = $body;
/*
$post['body'] = <<< EOT
{"appkey":"dc8155d28ee665311239cd0d032f4e2a","callback":"https://api.paadoo.com/callback.order-delivery/","cust":{"add":"华鑫证券有限责任公司文诚路xxx号x楼","phn":"18217321898","sal":"朱先森"},"items":[{"amt":15,"spec":"大份","item":"6iBaHRewS4RvSjfH","memo":"","name":"梅菜扣肉饭","rel":"1","count":2},{"amt":14,"spec":"","item":"1adeGglUti3W64ub","memo":"","name":"黄金猪排咖喱饭","rel":"","count":1}],"order":{"adva":"0","updated":"2015-08-11 15:44:38","created":"2015-08-11 15:44:38","est":"2015-08-11 15:44:38 681","exp":"2015-08-11 15:44:38 681","fee":5,"id":"0MT6AooMbw4dvjAj","incamt":42,"memo":"","recamt":42,"total":42,"type":"1","count":2},"shop":{"addr":"上海市杨浦区国定路323号","city":"上海","id":"bd2YwlPUXJwaK78D","phn":"13888888888","name":"001","lon":0.0,"lat":0.0},"timestamp":"2015-08-11 15:44:38 681"}
EOT;
$post['channel'] = 10;
$post['sign'] = 'd6c81b8e6ed96420bec18237a742211071bed3f32e9a7e24f6d3804c24b1363d';
*/
        if (empty($post['channel'])) {
            $response('channel为空');
            return false;
        }

        if (empty($post['body'])) {
            $response('body为空');
            return false;
        }

        if (empty($post['sign'])) {
            $response('sign为空');
            return false;
        }

        // 固定为 10 为我们系统里的 ID
        if (10 != $post['channel']) {
            $response('channel不正确');
            return false;
        }

        if ($this->__sign($post['body']) != $post['sign']) {
            $response('签名校验失败');
            return false;
        }

        $params = json_decode($post['body'], true);

        // 检查是不是一个有效的 JSON 字符串
        if (empty($params) || !is_array($params)) {
            $response('body不是一个有效的json格式');
            return false;
        }

        // 转换原始订单信息
        $params = $this->__transform_origin_order($params);
        if (false === $params) {
            $response($this->get_error());
            return false;
        }

        // 添加订单
        $result = $this->__add_order($params);
        if (false === $result) {
            $response($this->get_error());
            return false;
        }

        $response('成功', true);
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
        // todomark 来客没有用到
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
            'platform_sn'   => $params['platform_sn'],
            'memo'          => $params['cancel_reason'],
            'action'        => 4, // 配送动作（1=接单；2=提单；3=送达；4=取消）
        );

        $result = $this->__request_update_order($data, 'GET');

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
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
        );

        $data = array(
            'platform_sn'   => $params['platform_sn'],
            'action'        => 1, // 配送动作（1=接单；2=提单；3=送达；4=取消）
        );

        $result = $this->__request_update_order($data, 'GET');

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
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
        );

        $data = array(
            'platform_sn'   => $params['platform_sn'],
            'action'        => 2, // 配送动作（1=接单；2=提单；3=送达；4=取消）
        );

        $result = $this->__request_update_order($data, 'GET');

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
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
        );

        $data = array(
            'platform_sn'   => $params['platform_sn'],
            'action'        => 3, // 配送动作（1=接单；2=提单；3=送达；4=取消）
        );

        $result = $this->__request_update_order($data, 'GET');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
    // ===================== 以上函数一般来说是一定要实现的 ====================

    // ===================== 以下函数一般来说是每个平台单独拥有的 ====================
    /**
     * 客户拒收(配送员操作)
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function reject($params = array())
    {
        // 来客无对应接口，默认返回成功
        return true;
    }

    /**
     * 派单超时，无人配送
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function timeout($params = array())
    {
        return true;

        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn'   => $params['platform_sn'],   // 平台单号
            'to_message'    => $params['to_message'], // 取消原因
        );

        $data = array(
            'platform_sn'   => $params['platform_sn'],
            'memo'          => $params['to_message'],
            'action'        => 4, // 配送动作（1=接单；2=提单；3=送达；4=取消）
        );

        $result = $this->__request_update_order($data, 'GET');

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 获取平台订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function get_order($params = array())
    {
        // get_order
        // https://api.paadoo.com/ext.order.get/?_u=d5daerwe&_v=1.0.0&_c=11112222&sid=1022ZnufHAezNgqX&time=20150525:31
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = [
            'courier_id'  => $params['courier_id'],
            'platform_sn' => $params['platform_sn'],
            'logic_type'  => $params['logic_type'],
        ];

        $logic_type  = $params['logic_type'];    // 业务类型
        $courier_id  = $params['courier_id'] ? intval($params['courier_id']) : null;
        $platform_sn = $params['platform_sn']; // 1-2位为版本 3-18位为订单ID 19-34位为商户ID 老版新版35到36()为平台代码 后面为收货人电话
        // 如 A12LSWAgdCVtekTPWG1t4kj80lqUyvANMi10001215001818303
        // 版本 A1
        // 订单ID 2LSWAgdCVtekTPWG
        // 商户ID 1t4kj80lqUyvANMi 
        // 平台代码 100012 这是老版（100001饿了么，100005美团，100010百度外卖，100012淘点点，100050零号线，100060京东）
        // 新版平台代码只有两位 00-pos 01-美团外卖 02-饿了么 03-百度 04-淘点点 05-零号线 06-京东
        // 收货人联系电话 15001818303

        // 第一次是需要组合，add_order时，app直接用的是get时返回的platform_sn不需要再组合
        if ('get' == $logic_type) {
            $params = array(
                'platform_sn' => substr($platform_sn, 18, 16) . substr($platform_sn,  2, 16), // 平台单号
            );
        } else {
            $params = array(
                'platform_sn' => $platform_sn,
            ); 
        }

        $order = $this->__request_get_order($params, 'GET');

        if (false === $order) {
            $this->set_error($this->get_error());
            return false;
        } else {
            $order['courier_id'] = $courier_id; // 并入配送员ID，这样就能指定配送员了
        }

        // 根据业务逻辑操作订单
        if ('get' == $logic_type) {
            $order = $this->__transform_order($order);

            if (false === $order) {
                $this->set_error($this->get_error());
                return false;
            }

            $order['show_message'] = ''; // 提示信息

            // 取订单状态
            $this->load->model('Order_model');
            $platform_result = $this->Order_model->get_order_by_platform_sn($this->platform_id, $params['platform_sn'], 'order_id, order_status, branch_id, create_time, assign_id');

            // 处理根据订单状态返给APP（是否要接单）
            // 已取消的订单能直接再次接单，只是换个配送员
            if (in_array($platform_result['order_status'], array(10, 20, 99)) && true == $platform_result['is_scan_code']) {
                $order['order_status_title'] = '';
                $order['show_message']       = $platform_result['show_message']; // 订单给出提示
            } else {
                $order['order_status_title'] = $platform_result['order_status_title'];
            }

            return $order;

        // 把数据查到后，指定配送员，插入到订单中
        } else if ('add_order' == $logic_type) {
            $result = $this->__add_order($order);
            if (false === $result) {
                $this->set_error($this->get_error());
                return false;
            }

            // 返回订单 order_id
            $order_search = array(
                'platform_id' => $this->platform_id,
                'platform_sn' => $params['platform_sn'],
            );

            $result = $this->Order_model->get($order_search, 'order_id');

            return $result;

        } else {
            $this->set_error('没有指定获取平台订单的业务类型');
            return false;
        }
    }
    // ===================== 以上函数一般来说是每个平台单独拥有的 ====================
}