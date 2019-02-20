<?php
/**
 * 鲜急送
 *
 * @author      liunian
 * @date        2017-01-04
 * @copyright   Copyright(c) 2017
 *
 * @version     $Id$
 */
class CI_Delivery_1 extends CI_Delivery
{
    /**
     * 分发函数
     *
     * @param   array   $request    配送方传到平台接受处理
     *
     * @return  array|bool
     */
    public function allocation($request)
    {
        if (empty($request)) {
            $this->set_error('请求数据为空');
            return false;
        }

        // 签名认证
        $this->load->library('api');
        $sign = $this->api->sign($request, $this->app_secret); // 生成签名
        if ($request['sign'] != $sign['sign']) {
            $this->set_error('签名不正确');
            return false;
        }

        if (empty($request['method'])) {
            $this->set_error('没有相关业务处理');
            return false;
        }

        $method = $request['method'];
        $params = json_decode($request['params'], true);
        $params = is_array($params) ? $params : [];

        switch ($method) {
            case 'notification.create':   // 创建订单
                $result = $this->notification_create($params);
                break;
            case 'notification.cancel':   // 取消订单
                $result = $this->notification_cancel($params);
                break;
            case 'notification.confirm':  // 分配订单
                $result = $this->notification_confirm($params);
                break;
            case 'notification.delivery': // 开始配送
                $result = $this->notification_delivery($params);
                break;
            case 'notification.finish':   // 配送完成
                $result = $this->notification_finish($params);
                break;
            default:
                $run_method = '';
                break;
        }

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }

    /**
     * 转换订单数据
     *
     * @param   array   $params 参数
     *
     * @return  array
     */
    private function transform_order($params)
    {
        $order = [
            'platform_sn'           => $params['order_sn'],                     // 平台单号
            'platform_create_time'  => $params['create_time'],                  // 平台的订单创建时间 格式为 0000-00-00 00:00:00
            'actual_amount'         => $params['amount'],                       // 实收金额
            'discount_amount'       => $params['amount_coupon'],                // 优惠金额
            'is_collect'            => 2,                                       // 是否代收 1 是 2 否
            'reserve_time'          => $params['take_items_end_time'],          // 预定送达时间 格式为 0000-00-00 00:00:00 (take_items_start_time)
            'receive_name'          => $params['receiver'],                     // 收货人姓名
            'receive_phone'         => $params['receiver_phone'],               // 收货人电话
            'province_name'         => $params['receiver_province_name'],       // 收货省份
            'city_name'             => $params['receiver_city_name'],           // 收货城市
            'district_name'         => $params['receiver_district_name'],       // 收货城区
            'address'               => $params['receiver_addr'] . $params['receiver_addr_detail'],      // 详细地址
            'latitude'              => $params['receiver_addr_latitude'],       // 收货地址纬度
            'longitude'             => $params['receiver_addr_longitude'],      // 收货地址经度
            'from_name'             => $params['market_name'],                  // 发货人姓名
            'from_phone'            => $params['phone'],                        // 发货人电话
            'from_province_name'    => $params['province_name'],                // 发货人省份
            'from_city_name'        => $params['city_name'],                    // 发货人城市
            'from_district_name'    => $params['district_name'],                // 发货人城区
            'from_address'          => $params['address'],                      // 发货人详细地址
            'from_latitude'         => $params['latitude'],                     // 发货人地址纬度
            'from_longitude'        => $params['longitude'],                    // 发货人地址经度
            'remark'                => $params['member_remark'],                // 订单备注
            'gps_type'              => 5,                                       // 坐标类型 (百度地图采用的经纬度坐标)
        ];

        $order['items'] = $params['items'];

        /*
         * 用店铺作为商品信息显示
        foreach ($params['items'] as $item) {
            $order['items'][] = [
                'shop_outer_sn'         => $item['shop_id'],            // 合作伙伴商家唯一ID 可选（如果是有商家或者店面信息要传递）
                'shop_outer_name'       => $item['shop_name'],          // 合作伙伴商家名称 可选（如果是有商家或者店面信息要传递）
                'item_outer_sn'         => $item['product_id'],         // 合作伙伴商品唯一ID 必填
                'item_outer_name'       => $item['product_name'],       // 合作伙伴商品对应的名称 必填
                'item_outer_number'     => $item['num'],                // 合作伙伴本商品的销售数量 必填
                'item_outer_price'      => $item['price'],              // 合作伙伴本商品的销售价格 必填
                'item_outer_discount'   => bcsub($item['original_price'], $item['price'], 2), // 合作伙伴本商品的销售折扣 选填
                'item_outer_type'       => $item['product_variety_id'], // 合作伙伴本商品的类型代码 选填 如 1 代表红酒 2 代表电器 或者 category_123 这里只需要填写 1 2 或者 cateogry_123
                'item_outer_type_name'  => '',                          // 合作伙伴本商品的类型名称 选填 （与item_outer_type一一对应） 如 红酒、电器、PC
                'item_outer_memo'       => '',                          // 合作伙伴本商品的备注 选填
            ];
        }
        */

        return $order;
    }

    /**
     * 推送订单到鲜急送
     *
     * @param   array   $params 参数
     *
     * @return  array|bool
     */
    public function receive_order($params = [])
    {
        // 转换订单数据
        $order = $this->transform_order($params);

        // 配置信息
        $config = [
            'api_uri'     => $this->request_url,
            'app_secret'  => $this->app_secret,
            'app_key'     => $this->app_key,
            'app_session' => $this->app_session,
        ];

        // 设置配置信息
        $this->load->library('api');
        $this->api->config($config);

        // 发送请求
        $result = $this->api->request('platform.receive_order', $order, false);

        // 做相应的处理
        if (false === $result) {
            dump($this->api->get_error());
        } else {
            dump($result);
        }
    }

    /**
     * 配送平台通知创建订单成功
     *
     * @access  public
     *
     * @param   array   $params     推送过来的数据
     *
     * @return  bool|array
     */
    public function notification_create($params)
    {
        return [
            'title' => '配送平台通知创建订单成功',
            'name'  => '已接受',
        ];
    }

    /**
     * 配送平台通知订单已经取消
     *
     * @access  public
     *
     * @param   array   $params     推送过来的数据
     *
     * @return  bool|array
     */
    public function notification_cancel($params)
    {
        return [
            'title' => '配送平台通知订单已经取消',
            'name'  => '已接受',
        ];
    }

    /**
     * 配送平台通知订单被配送员接单
     *
     * @access  public
     *
     * @param   array   $params     推送过来的数据
     *
     * @return  bool|array
     */
    public function notification_confirm($params)
    {
        return [
            'title' => '配送平台通知订单被配送员接单',
            'name'  => '已接受',
        ];
    }

    /**
     * 配送平台通知订单开始配送
     *
     * @access  public
     *
     * @param   array   $params     推送过来的数据
     *
     * @return  bool|array
     */
    public function notification_delivery($params)
    {
        // 平台单号
        if (!filter_empty('platform_sn', $params)) {
            $this->set_error('平台单号为空');
            return false;
        }

        // 配送员名称
        if (!filter_empty('courier_name', $params)) {
            $this->set_error('配送员名称为空');
            return false;
        }

        // 配送员账号
        if (!filter_empty('courier_phone', $params)) {
            $this->set_error('配送员账号为空');
            return false;
        }

        $this->load->library('requester');

        // 判断订单是否有效
        $service_info = [
            'service_name'   => 'order.order_show.get_by_params',
            'service_params' => [
                'order_sn'  => $params['platform_sn'],
                'fields'    => 'order_id, status',
            ],
        ];
        $order_result = $this->requester->request($service_info);
        if (false === $order_result['success']) {
            $this->set_error($order_result['message']);
            return false;
        } else if (!filter_empty('data', $order_result)) {
            $this->set_error('订单不存在');
            return false;
        } else if (isset($order_result['is_sending']) && $order_result['is_sending']) { // 订单已经在派送中
            $this->set_error('订单已经在派送中');
            return false;
        }

        // 修改订单状态
        $service_info = [
            'service_name'   => 'order.order_operation.delivery',
            'service_params' => array_merge($params, ['order_sn' => $params['platform_sn']])
        ];
        $update_order_result = $this->requester->request($service_info);
        if (false === $update_order_result['success']) {
            $this->set_error($update_order_result['message']);
            return false;
        }

        // 添加订单进度
        $service_info = [
            'service_name'   => 'order.order_speed.add_sending_record',
            'service_params' => [
                'order_id' => $order_result['data']['order_id'],
                'order_sn' => $params['platform_sn']
            ]
        ];
        $order_speed_result = $this->requester->request($service_info);
        if (false === $order_speed_result['success']) {
            $this->set_error($order_speed_result['message']);
            return false;
        }

        return [
            'platform_sn' => $params['platform_sn'],
        ];
    }

    /**
     * 配送平台通知订单配送完成
     *
     * @access  public
     *
     * @param   array   $params     推送过来的数据
     *
     * @return  bool|array
     */
    public function notification_finish($params)
    {
        // 平台单号
        if (!filter_empty('platform_sn', $params)) {
            $this->set_error('平台单号为空');
            return false;
        }

        // 配送员名称
        if (!filter_empty('courier_name', $params)) {
            $this->set_error('配送员名称为空');
            return false;
        }

        // 配送员账号
        if (!filter_empty('courier_phone', $params)) {
            $this->set_error('配送员账号为空');
            return false;
        }

        $this->load->library('requester');

        // 判断订单是否有效
        $service_info = [
            'service_name'   => 'order.order_show.get_by_params',
            'service_params' => [
                'order_sn'  => $params['platform_sn'],
                'fields'    => 'order_id, status',
            ],
        ];
        $order_result = $this->requester->request($service_info);
        if (false === $order_result['success']) {
            $this->set_error($order_result['message']);
            return false;
        } else if (!filter_empty('data', $order_result)) {
            $this->set_error('订单不存在');
            return false;
        } else if (isset($order_result['is_completed']) && $order_result['is_completed']) { // 订单已经完成
            $this->set_error('订单已经配送完成');
            return false;
        }

        // 修改订单状态
        $service_info = [
            'service_name'   => 'order.order_operation.finish',
            'service_params' => array_merge($params, ['order_sn' => $params['platform_sn']])
        ];
        $update_order_result = $this->requester->request($service_info);
        if (false === $update_order_result['success']) {
            $this->set_error($update_order_result['message']);
            return false;
        }

        // 添加订单进度
        $service_info = [
            'service_name'   => 'order.order_speed.add_complete_record',
            'service_params' => [
                'order_id' => $order_result['data']['order_id'],
                'order_sn' => $params['platform_sn']
            ]
        ];
        $order_speed_result = $this->requester->request($service_info);
        if (false === $order_speed_result['success']) {
            $this->set_error($order_speed_result['message']);
            return false;
        }

        return [
            'platform_sn' => $params['platform_sn'],
        ];
    }

}