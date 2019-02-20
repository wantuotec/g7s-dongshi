<?php
/**
 * 标准平台接入（我方的平台接口走这里）
 *
 * @author      杨海波
 * @date        2015-05-18
 * @copyright   Copyright(c) 2015
 *
 * @version     $Id$
 */
class CI_Platform_standard extends CI_Platform
{
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

    // 网络超时
    private $__timeout    = 30;

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {

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
    private function __request($method, array $params = array(), $is_return_origin = false)
    {
        // request_url 不能为空
        if (empty($this->request_url) || false === filter_var($this->request_url, FILTER_VALIDATE_URL)) {
            // 如果 url 地址为空，返回true 表示不需要尝试，但返回一个信息告知可能需要设置request_url
            $this->set_error('请设置平台的request_url');
            return true;
        }

        $this->load->library('api');
        $this->api->config(array(
            'api_uri'     => $this->request_url,
            'app_key'     => $this->app_key,
            'app_session' => $this->app_session,
            'app_secret'  => $this->app_secret,
            'app_version' => '1.0.0',
            'app_type'    => 'p',
            'uuid'        => 'platform',

            'timestamp'   => date('Y-m-d H:i:s'),
            'format'      => 'json',
            'version'     => '1.0',
            'charset'     => 'UTF-8',
            'sign_method' => 'md5',
        ));

        $result = $this->api->request($method, $params, (bool) $is_return_origin);

        // 记录日志
        $this->_add_log("response {$method} post", $result);

        if (false === $result) {
            $this->set_error($this->api->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 检验订单数据
     *
     * @access  public
     *
     * @param   array   $params     订单数据
     *
     * @return  bool
     */
    public function is_check_order($params = array())
    {
        // 检验数据，屏蔽的检验 一米线目前没有
        if (empty($params['platform_sn'])) {
            $this->set_error(56002);
            return false;
        }

        if (empty($params['platform_create_time'])) {
            $this->set_error(56004);
            return false;
        }

        if (!is_date($params['platform_create_time'])) {
            $this->set_error(56005);
            return false;
        }

        if (!isset($params['actual_amount']) || !is_numeric($params['actual_amount'])) {
            $this->set_error(56006);
            return false;
        }

        if (!isset($params['discount_amount']) || !is_numeric($params['discount_amount'])) {
            $this->set_error(56007);
            return false;
        }

        if (empty($params['is_collect']) || !in_array($params['is_collect'], array(1, 2))) {
            $this->set_error(56008);
            return false;
        }

        // 去掉验证预约时间格式不正确
        unset($params['reserve_time']);

        if (!empty($params['reserve_time']) && !is_date($params['reserve_time'])) {
            $this->set_error(56009);
            return false;
        }

        if (empty($params['receive_name'])) {
            $this->set_error(56010);
            return false;
        }

        // 获取是否要判断收货人的电话
        $this->load->model("Configure_model");
        $is_check_receive_phone = $this->Configure_model->get_template_by_configure_name('is_check_receive_phone');

        // 需要验证收货人电话
        if (1 == intval($is_check_receive_phone)) {
            if (empty($params['receive_phone']) || !is_phone($params['receive_phone'])) {
                $this->set_error(56011);
                return false;
            }
        }

        // if (empty($params['province_name'])) {
        //     $this->set_error(56012);
        //     return false;
        // }

        if (empty($params['city_name'])) {
            $this->set_error(56013);
            return false;
        }

        // if (empty($params['district_name'])) {
        //     $this->set_error(56014);
        //     return false;
        // }

        if (empty($params['address'])) {
            $this->set_error(56015);
            return false;
        }

        if (empty($params['from_name'])) {
            $this->set_error(56017);
            return false;
        }

        // if (empty($params['from_phone']) || !is_phone($params['from_phone'])) {
        //     $this->set_error(56018);
        //     return false;
        // }

        // if (empty($params['from_province_name'])) {
        //     $this->set_error(56019);
        //     return false;
        // }

        if (empty($params['from_city_name'])) {
            $this->set_error(56020);
            return false;
        }

        // if (empty($params['from_district_name'])) {
        //     $this->set_error(56021);
        //     return false;
        // }

        if (empty($params['from_address'])) {
            $this->set_error(56022);
            return false;
        }

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
        data_filter($params);

        // GPS 坐标转换 (下面的分配站点会反解析GPS坐标，所以放在assign_branch之前)
        // 1：GPS设备获取的角度坐标;
        // 2：GPS获取的米制坐标、sogou地图所用坐标;
        // 3：google地图、soso地图、aliyun地图、mapabc地图和amap地图所用坐标
        // 4：3中列表地图坐标对应的米制坐标
        // 5：百度地图采用的经纬度坐标
        // 6：百度地图采用的米制坐标
        // 7：mapbar地图坐标;
        // 8：51地图坐标
        if (!empty($params['gps_type']) && 5 != $params['gps_type']) {
            if (!in_array($params['gps_type'], $this->gps_type)) {
                $this->set_error(56029);
                return false;
            }

            // 必须同时存在
            if (!empty($params['from_latitude']) && !empty($params['from_longitude'])) {
                $this->load->library('baidumap');
                $location = $this->baidumap->geoconv($params['from_latitude'], $params['from_longitude'], $params['gps_type']);
                $params['from_latitude']  = $location['latitude'];
                $params['from_longitude'] = $location['longitude'];
            } else {
                unset($params['from_latitude'], $params['from_longitude']);
            }

            // 必须同时存在
            if (!empty($params['latitude']) && !empty($params['longitude'])) {
                $this->load->library('baidumap');
                $location = $this->baidumap->geoconv($params['latitude'], $params['longitude'], $params['gps_type']);
                $params['latitude']  = $location['latitude'];
                $params['longitude'] = $location['longitude'];
            } else {
                unset($params['latitude'], $params['longitude']);
            }

            unset($location);
        }

        // 自动分配配送的站点
        $this->load->model('Order_assign_model');
        $branch = $this->Order_assign_model->assign_branch($params);

        // 如果 $branch === true  表示原始 $params里有 shop_id 或者 branch_id
        // 如果 $branch === false 表示无法完成分配站点的动作
        // 如果 $branch 为 array  表示已经正确的分配了站点

        // 无法分配站点
        if (false === $branch) {
            $this->set_error($this->Order_assign_model->get_error());
            return false;
        }

        if (!empty($branch) && is_array($branch)) {
            $params['from_city_name'] = $branch['from_city_name'];
            $params['from_address']   = $branch['from_address'];
            $params['from_latitude']  = $branch['from_latitude'];  // 可能原始数据里没有经纬度，分配站点的时候会计算出来，并到这里
            $params['from_longitude'] = $branch['from_longitude'];
            $params['branch_id']      = $branch['branch_id'];
        }
        unset($branch);

        $order = array(
            'platform_id'          => intval($this->platform_id),
            'platform_name'        => $this->platform_name,
            'platform_sn'          => empty($params['platform_sn'])          ? '' : $params['platform_sn'],
            'platform_create_time' => empty($params['platform_create_time']) ? '' : $params['platform_create_time'],
            'actual_amount'        => empty($params['actual_amount'])        ? '' : $params['actual_amount'],
            'discount_amount'      => empty($params['discount_amount'])      ? '' : $params['discount_amount'],
            'is_collect'           => empty($params['is_collect'])           ? '' : (1 == intval($params['is_collect']) ? 1 : 2),
            'is_reserve'           => empty($params['is_reserve'])           ? '' : (1 == intval($params['is_reserve']) ? 1 : 2),
            'reserve_time'         => empty($params['reserve_time'])         ? '' : $params['reserve_time'],
            'receive_name'         => empty($params['receive_name'])         ? '' : $params['receive_name'],
            'receive_phone'        => empty($params['receive_phone'])        ? '' : $params['receive_phone'],
            'province_name'        => empty($params['province_name'])        ? '' : $params['province_name'],
            'city_name'            => empty($params['city_name'])            ? '' : $params['city_name'],
            'district_name'        => empty($params['district_name'])        ? '' : $params['district_name'],
            'address'              => empty($params['address'])              ? '' : $params['address'],
            'latitude'             => empty($params['latitude'])             ? '' : $params['latitude'],
            'longitude'            => empty($params['longitude'])            ? '' : $params['longitude'],
            'from_name'            => empty($params['from_name'])            ? '' : $params['from_name'],
            'from_phone'           => empty($params['from_phone'])           ? '' : $params['from_phone'],
            'from_province_name'   => empty($params['from_province_name'])   ? '' : $params['from_province_name'],
            'from_city_name'       => empty($params['from_city_name'])       ? '' : $params['from_city_name'],
            'from_district_name'   => empty($params['from_district_name'])   ? '' : $params['from_district_name'],
            'from_address'         => empty($params['from_address'])         ? '' : $params['from_address'],
            'from_latitude'        => empty($params['from_latitude'])        ? '' : $params['from_latitude'],
            'from_longitude'       => empty($params['from_longitude'])       ? '' : $params['from_longitude'],
            'remark'               => empty($params['remark'])               ? '' : $params['remark'],
            'branch_id'            => empty($params['branch_id'])            ? '' : $params['branch_id'],
            'shop_id'              => empty($params['shop_id'])              ? '' : $params['shop_id'],
            'courier_id'           => empty($params['courier_id'])           ? '' : $params['courier_id'],
            'order_type'           => empty($params['order_type'])           ? '' : $params['order_type'],
            'add_type'             => 2,
            'items'                => !empty($params['items']) && is_array($params['items']) ? $params['items'] : array(),
        );

        // 如果收货人的经纬度为空，并且有收货地址，去算收货人的经纬度
        if ((empty($order['latitude']) || empty($order['longitude'])) && $order['city_name'] && $order['address']) {
            $this->load->library('baidumap');
            $location = $this->baidumap->address_to_location($order['address'], $order['city_name']);
            $order['latitude']  = $location['latitude'];
            $order['longitude'] = $location['longitude'];
            unset($location);
        }

        return $order;
    }

    /**
     * 根据平台ID跟平台订单号获取具体订单
     *
     * @param   string  $platform_sn    平台订单号
     *
     * @return  bool|array
     */
    public function get_order_by_platform($platform_sn = '')
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

    /*
    // 一定要有的
    receive_order
    cancel_order(我方取消订单)
    cancel_order_by_platform(平台取消订单)
    accept_order
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
        // 记录日志
        $this->_add_log('request receive_order post', file_get_contents("php://input"));

        $params = $this->is_check_order($params);
        if (false === $params) {
            $this->set_error($this->get_error());
            return false;
        }

        // 订单转换
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
        } else if ($platform_result) {
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
                $this->set_error('此订单已经取消，不能操作');
                return false;
            } else if (99 == $order_status) {
                $this->set_error('此订单已经取消，不能接单');
                return false;
            }
        } else {
            // 正常的添加订单
            $result = $this->Order_model->add_order_single($order);
        }

        if (false === $result) {
            $this->set_error($this->Order_model->get_error());
            return false;
        } else if (isset($result['row_num'])) {
            $this->set_replace($result);
            $this->set_error($this->Order_model->get_error());
            return false;
        } else {
            // @平台推送@ 这样做是为了统一走队列
            if (!empty($order['platform_id']) && !empty($order['platform_sn'])) {
                $this->load->library('platform');
                $platform = $this->platform->factory($order['platform_id']);

                if (false !== $platform && is_object($platform)) {
                    $result = $platform->send(array(
                        'callback'    => 'create_order',
                        'platform_sn' => $order['platform_sn'],
                    ));

                    if (false === $result) {
                        // dump($platform->get_error());
                    } else {
                        // dump('2', $result);
                    }
                }
            }

            return true;
        }
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
            $this->set_error(56002);
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
     * 取消订单(我方取消订单)
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

        $result = $this->__request('notification.cancel', $params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 创建订单
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function create_order($params = array())
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

        $result = $this->__request('notification.create', $params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 确认收货
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

        $result = $this->__request('notification.confirm', $params);

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

        $result = $this->__request('notification.delivery', $params);

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

        $result = $this->__request('notification.finish', $params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

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
            'to_message'    => $params['to_message'],    // 拒接理由
        );

        $result = $this->__request('notification.reject', $params);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
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

        // complete
        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn' => $params['platform_sn'], // 平台单号
            'to_message'  => $params['to_message'],  // 派单超时理由
        );

        $result = $this->__request('notification.timeout', $params);

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
        $logic_type = $params['logic_type'];    // 业务类型
        $courier_id = $params['courier_id'] ? intval($params['courier_id']) : null;
        $order      = array();

        if (empty($params['platform_sn'])) {
            $this->set_error('请填写平台单号');
            return false;
        }

        $params['platform_id'] = $this->platform_id;

        // 检验订单号操作
        $this->load->model('Platform_model');
        $result = $this->Platform_model->is_check_platform($params);
        if (false === $result) {
            $this->set_error($this->Platform_model->get_error());
            return false;
        }

        // 输入参数
        $params = array(
            'platform_sn' => $params['platform_sn'],   // 平台单号
        );

        $order = $this->__request('notification.get_order', $params);
        if (false === $order) {
            $this->set_error($this->get_error());
            return false;
        }

        if (true == $order && !is_array($order)) {
            $this->set_error('此平台没有此订单的数据信息');
            return false;
        }

        $this->load->model('Order_model');
        $order['courier_id'] = $courier_id; // 并入配送员ID，这样就能指定配送员了

        // 根据业务逻辑操作订单
        if ('get' == $logic_type) {
            $order = $this->__transform_order($order);
            if (false === $order) {
                $this->set_error($this->get_error());
                return false;
            }

            $order['show_message']       = '';  // 提示信息
            $order['order_status_title'] = '';  // 订单状态说明

            $platform_result = $this->Order_model->get_order_by_platform_sn($this->platform_id, $params['platform_sn'], 'order_id, order_status, branch_id, create_time, assign_id');

            // 处理根据订单状态返给APP（是否要接单）
            // 已取消的订单能直接再次接单，只是换个配送员
            if (in_array($platform_result['order_status'], array(10, 20, 99)) && true == $platform_result['is_scan_code']) {
                $order['show_message']       = $platform_result['show_message']; // 订单给出提示
            } else {
                $order['order_status_title'] = $platform_result['order_status_title'];
            }

            return $order;

        // 把数据查到后，指定配送员，插入到订单中
        } else if ('add_order' == $logic_type) {
            $result = $this->receive_order($order);
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

    // ===================== 以上函数一般来说是一定要实现的 ====================

    // ===================== 以下函数一般来说是每个平台单独拥有的 ====================
    // ===================== 以上函数一般来说是每个平台单独拥有的 ====================
}