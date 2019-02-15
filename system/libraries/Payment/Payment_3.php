<?php
/**
 * 微信
 *
 * @author      杨海波
 * @date        2016-01-09
 * @copyright   Copyright(c) 2016
 *
 * @version     $Id$
 */
class CI_Payment_3 extends CI_Payment
{
    // 此支付平台允许对外访问的
    private $__white_list = [
        'notify_repsonse', 'start', 'pay', 'callback', 'notify', 'notify_repsonse',
    ];

    // 平台ID
    public $payment_id = null;

    // 平台名称
    public $payment_name = null;

    // 网络超时
    private $__timeout = 30;

    // 支付地址
    private $__pay_url = '';

    // 回调地址
    private $__callback_url = '';

    // 通知地址
    private $__notify_url = '';

    // sdk 根目录
    private $__sdk = '';

    // 微信公众号 APP ID 列表
    private $__JSAPI = [25];

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        // 白名单校验
        $this->check_white_list($this->__white_list);

        $this->__sdk = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'payment/wechat') . DIRECTORY_SEPARATOR;

        // 统一加载
        require_once $this->__sdk . 'lib/WxPay.Api.php';
        require_once $this->__sdk . 'lib/WxPay.Data.php';
        require_once $this->__sdk . 'lib/WxPay.Exception.php';
        require_once $this->__sdk . 'example/WxPay.JsApiPay.php';
        require_once $this->__sdk . 'example/WxPay.AppPay.php';

        $this->__pay_url = API_DOMAIN . 'payment/wechat/pay';
        $this->__callback_url = API_DOMAIN . 'payment/wechat/callback';
        $this->__notify_url = API_DOMAIN . 'payment/wechat/notify';
        // $this->__notify_url   = 'http://api.xian168.com/payment/wechat/notify'; // 固定要线上环境
    }

    // ===================== 以下函数一般来说是每个支付平台单独拥有的 ====================
    /**
     * 微信支付不同的应用需要加载不同的配置
     *
     * @access  public
     *
     * @param   int     $config_id  配置ID
     *
     * @return  bool
     */
    protected function load_config($config_id = null)
    {
        // 对于微信来说 config_id 一定要存在
        if (empty($config_id)) {
            $this->set_env_error(47211, 70005);
            return false;
        }

        // 查看配置文件是否存在，不同的客户端加载的配置不一样
        $file = $this->__sdk . 'lib/WxPay.Config.' . $config_id . '.php';
        if (!file_exists($file)) {
            $this->set_env_error(47212, 70006);
            return false;
        }

        require_once $file;

        return true;
    }

    /**
     * APP支付信息加密
     *
     * @access  public
     *
     * @param   array $params 输入参数
     *
     * @return  bool
     */
    public function app_sign($params = [])
    {
        // 根据app_type去加载不同的配置
        if (!$this->load_config($params['app_type'])) {
            return false;
        }

        // $params = array(
        //     'pay_sn'  => '092918245913829',
        //     'subject' => '测试的商品',
        //     'body'    => '该测试商品的详细描述',
        //     'amount'  => '0.01',
        // );

        // 支付流水号不能为空
        if (empty($params['pay_sn'])) {
            $this->set_env_error(47209, 70003);
            return false;
        }

        // 充值金额不能为空
        if (empty($params['amount']) || !is_numeric($params['amount']) || $params['amount'] <= 0) {
            $this->set_env_error(47208, 70002);
            return false;
        }

        $data = [];
        if (!in_array($params['app_type'], $this->__JSAPI)) {
            $params = array(
                'pay_sn' => empty($params['pay_sn']) ? '' : $params['pay_sn'],
                'subject' => empty($params['subject']) ? '充值' : $params['subject'],
                'body' => empty($params['body']) ? '充值' . round($params['amount'], 2) : $params['body'],
                'amount' => empty($params['amount']) ? 0 : round($params['amount'], 2),
            );

            $input = new WxPayUnifiedOrder();
            $input->SetBody($params['subject']); // 商品或支付单简要描述
            $input->SetDetail($params['body']); // 商品名称明细列表
            // $input->SetAttach("test"); // 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetOut_trade_no($params['pay_sn']); // 商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
            $input->SetTotal_fee(intval($params['amount'] * 100)); // 订单总金额 单位为分
            $input->SetTime_start(date("YmdHis")); // 订单生成时间
            $input->SetTime_expire(date("YmdHis", time() + 600)); // 订单失效时间 注意：最短失效时间间隔必须大于5分钟
            // $input->SetGoods_tag("test"); // 商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
            $input->SetNotify_url($this->__notify_url); // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数
            $input->SetTrade_type("APP"); // JSAPI--公众号支付、NATIVE--原生扫码支付、APP--app支付，统一下单接口trade_type的传参可参考这里 MICROPAY--刷卡支付，刷卡支付有单独的支付接口，不调用统一下单接口
            // $input->SetOpenid($openId);

            try {
                $order = WxPayApi::unifiedOrder($input);
            } catch (Exception $e) {
                $this->set_env_error(47210, 70004);
                return false;
            }

            $tools = new AppPay();
            $data = $tools->GetAppParameters($order);

            // 因为安卓里package 是关键词会影响解析 add by mark 2016-01-14
            $data['packagevalue'] = $data['package'];
        }

        return $data;
    }

    /**
     * 支付通知响应
     *
     * @access  public
     *
     * @param   array $params 输入参数
     *
     * @return  bool
     */
    public function notify_repsonse($is_success = false, $message = '')
    {
        // dump($is_success, $message);exit;

        $return_code = (bool)$is_success ? 'SUCCESS' : 'FAIL';
        $return_msg = (bool)$is_success && empty($message) ? 'OK' : $message;
        $xml = <<<EOT
<xml><return_code><![CDATA[{$return_code}]]></return_code><return_msg><![CDATA[{$return_msg}]]></return_msg></xml>
EOT;
        exit($xml);
    }

    /**
     * 微信公众号生成
     *
     * @access  public
     *
     * @param   array $params 输入参数
     *
     * @return  bool
     */
    public function test()
    {
        $_SESSION['pay_session'] = array(
            'pay_sn'  => date('YmdHis'),
            'subject' => '测试的商品',
            'body'    => '该测试商品的详细描述',
            'amount'  => '0.01',
            'app_type' => 25,
            'back_url' => 'http://www.baidu.com/',
            'error_url' => 'http://www.qq.com/',
        );

        url_redirect(M_DOMAIN . 'payment/wechat/start');
    }

    /**
     * 微信公众号支付页面
     *
     * @access  public
     *
     * @param   array $params 输入参数
     *
     * @return  bool
     */
    private function __output($params = [])
    {
        extract($params);

        require_once $this->__sdk . 'lib/m.feidaomen.com.tpl';
        exit;
    }

    /**
     * 支付发起页面
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function start($params = [])
    {
        $params = $_SESSION['pay_session'];

        $result = [
            'amount'    => $params['amount'],
            'back_url'  => $params['back_url'],
            'error_url' => $params['error_url'],
            'jsapi'     => '',
            'error'     => '',
        ];

        // 必要参数
        if (empty($params['app_type'])
         || empty($params['subject'])
         || empty($params['body'])
         || empty($params['pay_sn'])
         || empty($params['amount'])
         || empty($params['back_url'])
         || empty($params['error_url'])
        ) {
            $result['error'] = '必要参数缺失';
        }

        // 加载支付配置
        if (!$this->load_config($params['app_type'])) {
            $result['error'] = '支付配置不存在';
        }

        if (!empty($result['error'])) {
            $this->__output($result);
        }

        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($params['subject']); // 商品或支付单简要描述
        $input->SetDetail($params['body']); // 商品名称明细列表
        // $input->SetAttach("test"); // 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($params['pay_sn']); // 商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        $input->SetTotal_fee(intval($params['amount'] * 100)); // 订单总金额 单位为分
        $input->SetTime_start(date("YmdHis")); // 订单生成时间
        $input->SetTime_expire(date("YmdHis", time() + 600)); // 订单失效时间 注意：最短失效时间间隔必须大于5分钟
        // $input->SetGoods_tag("test"); // 商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
        $input->SetNotify_url($this->__notify_url); // 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数
        $input->SetTrade_type("JSAPI"); // JSAPI--公众号支付、NATIVE--原生扫码支付、APP--app支付，统一下单接口trade_type的传参可参考这里 MICROPAY--刷卡支付，刷卡支付有单独的支付接口，不调用统一下单接口
        $input->SetOpenid($openId);

        try {
            $order = WxPayApi::unifiedOrder($input);
            if ($order['return_code'] != 'SUCCESS' || $order['result_code'] != 'SUCCESS') {
                $result['error'] = $order['return_msg'] == 'OK' ? $order['err_code_des'] . $order['err_code'] : $order['return_msg'];
                // exit('<center><font color="#f00"><b>统一下单异常：' . $msg . '</b></font></center>');
            }
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            // exit('<center><font color="#f00"><b>统一下单异常：' . $e->getMessage() . '</b></font></center>');
        }

        if (!empty($result['error'])) {
            $this->__output($result);
        }

        // echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';

//        //打印输出数组信息
//        $printf_info = function($data)
//        {
//            foreach($data as $key=>$value){
//                echo "<font color='#00ff55;'>$key</font> : $value <br/>";
//            }
//        };
//        $printf_info($order);
        $result['jsapi'] = $tools->GetJsApiParameters($order);

        $this->__output($result);

        //获取共享收货地址js函数参数
        // $editAddress = $tools->GetEditAddressParameters();

        //③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
        /**
         * 注意：
         * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
         * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
         * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
         */

    }
    // ===================== 以上函数一般来说是每个支付平台单独拥有的 ====================

    // ===================== 以下函数一般来说是一定要实现的 ====================
    /**
     * 支付发起页面
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function pay($params = [])
    {
    }

    /**
     * 页面回调
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function callback($params = [])
    {
    }

    /**
     * 转换通知
     *
     * @param   array   $params 支付信息
     *
     * @return  array
     */
    public function transform_notify(array $params = [])
    {
        $params = [
            // 以下为必须有的字段
            'pay_sn'        => empty($params['out_trade_no'])   ? '' : $params['out_trade_no'],   // 商户订单号（我方订单号）
            'pay_outer_sn'  => empty($params['transaction_id']) ? '' : $params['transaction_id'], // 支付平台的订单号
            'outer_user_id' => empty($params['openid'])         ? '' : $params['openid'],  // 支付平台的支付人账号
            'pay_status'    => empty($params['result_code'])    ? '' : $params['result_code'],    // 交易状态
            'notify_amount' => empty($params['total_fee'])      ? '' : bcdiv($params['total_fee'], 100, 2), // 微信以分为单位
            'request'       => $params,
            // 以下为可选字段各业务自行配置
        ];

        return $params;
    }

    /**
     * 异步通知
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function notify($params = [])
    {
        // error_log(date('YmdHis') . "\n". file_get_contents('php://input') . "\n\n\n", 3, '/tmp/wechat.txt');

        // 一定要是 POST 请求
        if (!$this->input->is_post_request()) {
            $this->notify_repsonse(false, '请全用POST方式');
        }

        $xml = file_get_contents('php://input');

        if (empty($xml)) {
            $this->notify_repsonse(false, 'POST内容为空');
        }

//        $xml = <<<EOT
//<xml>
//  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
//  <attach><![CDATA[支付测试]]></attach>
//  <bank_type><![CDATA[CFT]]></bank_type>
//  <fee_type><![CDATA[CNY]]></fee_type>
//  <is_subscribe><![CDATA[Y]]></is_subscribe>
//  <mch_id><![CDATA[10000100]]></mch_id>
//  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
//  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
//  <out_trade_no><![CDATA[1409811653]]></out_trade_no>
//  <result_code><![CDATA[SUCCESS]]></result_code>
//  <return_code><![CDATA[SUCCESS]]></return_code>
//  <sign><![CDATA[93917F22EC49779E9433C047ECFAD5B6]]></sign>
//  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
//  <time_end><![CDATA[20140903131540]]></time_end>
//  <total_fee>1</total_fee>
//  <trade_type><![CDATA[JSAPI]]></trade_type>
//  <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
//</xml>
//EOT;
        // // 数据示例 (微信公众账号) // @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7
        //$result = [
        //    'appid' => 'wx2421b1c4370ec43b', // 微信分配的公众账号ID（企业号corpid即为此appId）
        //    'attach' => '支付测试', // 商家数据包，原样返回
        //    'bank_type' => 'CFT', // 银行类型，采用字符串类型的银行标识，银行类型见银行列表
        //    'fee_type' => 'CNY', // 货币类型，符合ISO4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
        //    'is_subscribe' => 'Y', // 用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
        //    'mch_id' => '10000100', // 微信支付分配的商户号
        //    'nonce_str' => '5d2b6c2a8db53831f7eda20af46e531c', // 随机字符串，不长于32位
        //    'openid' => 'oUpF8uMEb4qRXf22hE3X68TekukE', // 用户在商户appid下的唯一标识
        //    'out_trade_no' => '1409811653', // 商户订单号
        //    'result_code' => 'SUCCESS', // 业务结果 SUCCESS/FAIL
        //    'return_code' => 'SUCCESS', // 返回状态码 SUCCESS/FAIL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
        //    'sign' => 'B552ED6B279343CB493C5DD0D78AB241',
        //    'sub_mch_id' => '10000100',
        //    'time_end' => '20140903131540', // 支付完成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
        //    'total_fee' => '1', // 订单总金额，单位为分
        //    'trade_type' => 'JSAPI', // 交易类型 JSAPI、NATIVE、APP
        //    'transaction_id' => '1004400740201409030005092168', // 微信支付订单号
        //];

        // 微信SDK封装太死板，弃之
        $result = $this->xml2array($xml);

        // 转换成我方需要的格式
        $transform = $this->transform_notify($result);
        // 插入原始日志
        $pay_log_id = $this->add_notify_log($transform);

        $exist = $this->get_by_pay_sn($transform['pay_sn']);
        if (empty($exist)) {
            $this->notify_repsonse(false, '支付订单不存在');
        }

        // 加载不同的支付配置
        if (!$this->load_config($exist['app_type'])) {
            return false;
        }

        // 如果边sign都没有，表示原始数据有问题
        if (empty($result['sign'])) {
            $this->notify_repsonse(false, '原始数据格式不正确');
        }

        // 签名校验
        try {
            $wxPayObj = new WxPayResults();
            $wxPayObj->FromArray($result);
            $wxPayObj->CheckSign();
        } catch (WxPayException $e){
            // $msg = $e->errorMessage();
            $this->notify_repsonse(false, '签名校验失败');
        }

        if(!array_key_exists("return_code", $result) || !array_key_exists("result_code", $result)
            || $result["return_code"] != "SUCCESS" || $result["result_code"] != "SUCCESS")
        {
            $this->notify_repsonse(false, '订单通知失败');
        }

        if(!array_key_exists("transaction_id", $result)) {
            $this->notify_repsonse(false, 'transaction_id不存在');
        }

        // 查询订单
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($result['transaction_id']);
        $query = WxPayApi::orderQuery($input);

        if(!array_key_exists("return_code", $query) || !array_key_exists("result_code", $query)
             || $query["return_code"] != "SUCCESS" || $query["result_code"] != "SUCCESS")
        {
            $this->notify_repsonse(false, '订单查询失败');
        }
        unset($input, $query);

        // 走到这里，所以的验证已经完成
        // 支付是否成功
        $is_success = false;

        $verify_result = true;

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 请在这里加上商户的业务逻辑程序代

            if(!empty($transform) && is_array($transform) && !empty($transform['pay_sn'])) {
                if(in_array($transform['pay_status'], array('SUCCESS'))) {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                    // 比对一下金额，要和支付里的信息一样才能更新。

                    // 如果成功执行
                    $this->notify_success($transform);

                    // error_log(date('Y-m-d H:i:s') . "\t\n 2:\n" . "trade status is correct\n", 3, '/data/apps/logs/wechat.txt');
                } else {
                    // error_log(date('Y-m-d H:i:s') . "\t\n 3:\n" . "trade status is invalid\n", 3, '/data/apps/logs/wechat.txt');
                }

                // error_log(date('Y-m-d H:i:s') . "\t\n 222:\n" . "trade status is correct\n", 3, '/data/apps/logs/wechat.txt');
                $is_success = true;
                // exit('success');     //请不要修改或删除
            } else {
                // error_log(date('Y-m-d H:i:s') . "\t\n 4:\n" . "result is invalid\n", 3, '/data/apps/logs/wechat.txt');
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            // error_log(date('Y-m-d H:i:s') . "\t\n 5:\n" . "fail\n", 3, '/data/apps/logs/wechat.txt');
            $is_success = false;
            // exit('fail');
        }
        // error_log(date('Y-m-d H:i:s') . "\t\n 6:\n" . "over\n", 3, '/data/apps/logs/wechat.txt');

        if (true === $is_success) {
            // 更新处理结果
            $this->update_notify_log_by_id($pay_log_id);

            $this->notify_repsonse(true);
        } else {
            $this->notify_repsonse(false, '其它错误');
        }
    }
    // ===================== 以上函数一般来说是一定要实现的 ====================

    /**
     * 申请退款
     *
     * @param   array   $params     输入参数
     *
     * @return  void
     */
    public function refund($params = [])
    {
        /* 返回结果
        Array
        (
            [appid] = wx5c648d7f93b37813
            [cash_fee] = 66
            [cash_refund_fee] = 66
            [coupon_refund_count] = 0
            [coupon_refund_fee] = 0
            [mch_id] = 1306030601
            [nonce_str] = LxUgtRyaOw6R3Vgt
            [out_refund_no] = 130603060120160317134314
            [out_trade_no] = pay458130112761
            [refund_channel] = Array
                (
                )

            [refund_fee] = 66
            [refund_id] = 2003830086201603170181521507
            [result_code] = SUCCESS
            [return_code] = SUCCESS
            [return_msg] = OK
            [sign] = 9530BB3AEEEB8151E4FF327504D942C4
            [total_fee] = 66
            [transaction_id] = 1003830086201603164036045334
        )
        */

        /* 测试用数据
        // 根据app_type去加载不同的配置
        if (!$this->load_config(21)) {
            $ret['msg'] = '配置文件不存在或加载失败';
            $this->__jsonencode($ret);
        }

        $transaction_id = '1003830086201603164036045334';
        $total_fee  = 0.66 * 100;
        $refund_fee = 0.66 * 100;
        $input = new WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);
        print_r(WxPayApi::refund($input));
        exit;
        */

        // 初始化返回值
        $ret = [];

        // 必须提供的参数
        if (!isset($params['app_type']) || empty($params['app_type'])
            || !isset($params['transaction_id']) || empty($params['transaction_id']) 
            || !isset($params['total_fee']) || empty($params['total_fee']) 
            || !isset($params['refund_fee']) || empty($params['refund_fee']) 
            || !isset($params['out_refund_no']) || empty($params['out_refund_no'])) {

            $ret['msg'] = '必须填写指定参数：app_type、out_trade_no、total_fee、refund_fee、out_refund_no';
            $this->__jsonencode($ret);
        }

        // 根据app_type去加载不同的配置
        if (!$this->load_config($params['app_type'])) {
            $ret['msg'] = '配置文件不存在或加载失败';
            $this->__jsonencode($ret);
        }

        $transaction_id = $params["transaction_id"];
        $total_fee      = $params["total_fee"] * 100;
        $refund_fee     = $params["refund_fee"] * 100;
        $out_refund_no  = $params["out_refund_no"];

        $input = new WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no($out_refund_no);
        $input->SetOp_user_id(WxPayConfig::MCHID);

        $ret = WxPayApi::refund($input);
        return $ret;
    }

    /**
     * 查询退款结果
     *
     * @param   array   $params     输入参数
     *
     * @return  void
     */
    public function refundquery($params = [])
    {
        /* 返回样例
        Array
        (
            [appid] => wx5c648d7f93b37813
            [cash_fee] => 66
            [mch_id] => 1306030601
            [nonce_str] => mmySYvlBm3unbZvf
            [out_refund_no_0] => 130603060120160317134314
            [out_trade_no] => pay458130112761
            [refund_channel_0] => ORIGINAL
            [refund_count] => 1
            [refund_fee] => 66
            [refund_fee_0] => 66
            [refund_id_0] => 2003830086201603170181521507
            [refund_recv_accout_0] => 支付用户的零钱
            [refund_status_0] => SUCCESS
            [result_code] => SUCCESS
            [return_code] => SUCCESS
            [return_msg] => OK
            [sign] => 77CA0BC1B75984B8D907E392A99E363A
            [total_fee] => 66
            [transaction_id] => 1003830086201603164036045334
        )
        */

        // 测试数据
        // $params = [
        //     'app_type'      => 21,
        //     'transaction_id'  => '1003830086201603164036045334',
        // ];

        $transaction_id = $params["transaction_id"];

        // 根据app_type去加载不同的配置
        if (!$this->load_config($params['app_type'])) {
            $ret['msg'] = '配置文件不存在或加载失败';
            $this->__jsonencode($ret);
        }

        $input = new WxPayRefundQuery();
        $input->SetTransaction_id($transaction_id);
        $ret = WxPayApi::refundQuery($input);

        return $ret;
    }

    /**
     * 输出json格式数据
     * 
     * @param  array $ret 待转换数据
     * 
     * @return void
     */
    private function __jsonencode($ret = [])
    {
        echo json_encode($ret);
        exit;
    }
}