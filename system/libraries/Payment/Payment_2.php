<?php
/**
 * 支付宝
 *
 * @author      杨海波
 * @date        2015-09-28
 * @copyright   Copyright(c) 2015
 *
 * @version     $Id$
 */
class CI_Payment_2 extends CI_Payment
{
    // 此支付平台允许对外访问的
    private $__white_list = [
        'start', 'pay', 'callback', 'notify', 'refund_batch_notify', 'batch_trans_by_pwd_notify', 'create_direct_return_url',
    ];

    // 平台ID
    public $payment_id   = null;

    // 平台名称
    public $payment_name = null;

    // 网络超时
    private $__timeout   = 30;

    // 支付地址
    private $__pay_url      = '';

    // 回调地址
    private $__callback_url = '';

    // 通知地址
    private $__notify_url   = '';

    // 即时到账批量退款异步通知地址
    private $__refund_batch_notify_url = '';

    // 批量付款到支付宝账户异步通知地址
    private $__batch_trans_notify_by_pwd_url = '';

    // sdk 根目录
    private $__sdk = '';

    // 我方账户
    private $__account = 'dev2018@163.com';

    // 付款账号名
    private $__account_name = '鲜米（上海）信息科技有限公司';

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        // 白名单校验
        $this->check_white_list($this->__white_list);

        $this->__sdk = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR . 'payment/alipay') . DIRECTORY_SEPARATOR;

        $this->__pay_url      = API_DOMAIN . 'payment/alipay/pay';
        $this->__callback_url = API_DOMAIN . 'payment/alipay/callback';
        $this->__notify_url   = API_DOMAIN . 'payment/alipay/notify';
        // $this->__notify_url   = 'http://api.52xianmi.com/payment/alipay/notify'; // 固定要线上环境

        // 即时到账批量退款异步通知
        $this->__refund_batch_notify_url = API_DOMAIN . 'payment/alipay/refund_batch_notify';

        // 批量付款到支付宝账户异步通知地址
        $this->__batch_trans_notify_by_pwd_url = API_DOMAIN . 'payment/alipay/batch_trans_by_pwd_notify';

        // 即时到账交易接口页面跳转url
        $this->__create_direct_pay_by_user_return_url = API_DOMAIN . 'payment/alipay/create_direct_return_url';

        $this->alipay_config = array();

        //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        //合作身份者id，以2088开头的16位纯数字
        $this->alipay_config['partner']       = '2088521466201344';

        //商户的私钥（后缀是.pen）文件相对路径
        $this->alipay_config['private_key_path']  = $this->__sdk . 'key/rsa_private_key.pem';

        //支付宝公钥（后缀是.pen）文件相对路径
        $this->alipay_config['ali_public_key_path']= $this->__sdk . 'key/alipay_public_key.pem';


        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


        //签名方式 不需修改
        $this->alipay_config['sign_type']    = strtoupper('RSA');

        //字符编码格式 目前支持 gbk 或 utf-8
        $this->alipay_config['input_charset']= strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $this->alipay_config['cacert']    = $this->__sdk . 'cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $this->alipay_config['transport']    = 'http';
    }

    // ===================== 以下函数一般来说是每个支付平台单独拥有的 ====================
    /**
     * APP支付信息加密
     *
     * @access  public
     *
     * @param   array   $params     输入参数
     *
     * @return  bool
     */
    public function app_sign($params = array())
    {
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

        $params = array(
            'pay_sn'  => empty($params['pay_sn'])  ? ''     : $params['pay_sn'],
            'subject' => empty($params['subject']) ? '充值' : $params['subject'],
            'body'    => empty($params['body'])    ? '充值' . round($params['amount'], 2) : $params['body'],
            'amount'  => empty($params['amount'])  ? 0      : round($params['amount'], 2),
        );

        // partner="2088611816704604"&seller_id="caiwu@xian168.com"&out_trade_no="092918245913829"&subject="测试的商品"&body="该测试商品的详细描述"&total_fee="0.01"&notify_url="http://api.xian168.com/payment/alipay/notify"&service="mobile.securitypay.pay"&payment_type="1"&_input_charset="utf-8"&it_b_pay="30m"&app_id="external"&appenv="system=android^version=3.0.1.2"&sign="eizICuVs%2B6u6qjhSV55RAUze1MLQ0r%2BPCRY%2BkDZ8UShJjKBK2Mj1hF4RDoWDcW46ra9Dz%2Fz9v2huL4br7I5FKH2%2BO22EVX49eDP9Pu2zPWybnmY%2FbEYnIUNlhCgXo8y4dCcqo3S6vJ1sqYTqQg%2BACXpoRXjZMDE%2FNwQgx8j79Qk%3D"&sign_type="RSA"

        // 以下顺序是安卓sdk里固定死的
        $data = "partner=\"{$this->alipay_config['partner']}\""
              . "&seller_id=\"{$this->__account}\""
              . "&out_trade_no=\"{$params['pay_sn']}\""
              . "&subject=\"{$params['subject']}\""
              . "&body=\"{$params['body']}\""
              . "&total_fee=\"{$params['amount']}\""
              . "&notify_url=\"{$this->__notify_url}\""
              . "&service=\"mobile.securitypay.pay\""
              . "&payment_type=\"1\""
              . "&_input_charset=\"utf-8\""
              . "&it_b_pay=\"30m\"";

        require_once($this->__sdk . 'lib/alipay_rsa.function.php');
        $sign = rsaSign($data, $this->alipay_config['private_key_path']);
        $sign = urlencode(($sign));

        $data .= "&sign=\"{$sign}\"&sign_type=\"RSA\"";

        return $data;
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
    public function start($params = array())
    {
        echo <<<EOT
        <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <title>支付宝手机网页支付</title>
            <meta name="viewport"content="width=device-width, initial-scale=1"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
        *{
            margin:0;
            padding:0;
        }
        ul,ol{
            list-style:none;
        }
        .title{
            color: #ADADAD;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 16px 5px 10px;
        }
        .hidden{
            display:none;
        }

        .new-btn-login-sp{
            border:1px solid #D74C00;
            padding:1px;
            display:inline-block;
        }

        .new-btn-login{
            background-color: transparent;
            background-image: url("images/new-btn-fixed.png");
            border: medium none;
        }
        .new-btn-login{
            background-position: 0 -198px;
            width: 82px;
            color: #FFFFFF;
            font-weight: bold;
            height: 28px;
            line-height: 28px;
            padding: 0 10px 3px;
        }
        .new-btn-login:hover{
            background-position: 0 -167px;
            width: 82px;
            color: #FFFFFF;
            font-weight: bold;
            height: 28px;
            line-height: 28px;
            padding: 0 10px 3px;
        }
        .bank-list{
            overflow:hidden;
            margin-top:5px;
        }
        .bank-list li{
            float:left;
            width:153px;
            margin-bottom:5px;
        }

        #main{
            width:455px;
            margin:0 auto;
            font-size:14px;
            font-family:'宋体';
        }
        #logo{
            background-color: transparent;
            background-image: url("images/new-btn-fixed.png");
            border: medium none;
            background-position:0 0;
            width:166px;
            height:35px;
            float:left;
        }
        .red-star{
            color:#f00;
            width:10px;
            display:inline-block;
        }
        .null-star{
            color:#fff;
        }
        .content{
            margin-top:5px;
        }

        .content dt{
            width:160px;
            display:inline-block;
            text-align:right;
            float:left;
            
        }
        .content dd{
            margin-left:100px;
            margin-bottom:5px;
        }
        #foot{
            margin-top:10px;
        }
        .foot-ul li {
            text-align:center;
        }
        .note-help {
            color: #999999;
            font-size: 12px;
            line-height: 130%;
            padding-left: 3px;
        }

        .cashier-nav {
            font-size: 14px;
            margin: 15px 0 10px;
            text-align: left;
            height:30px;
            border-bottom:solid 2px #CFD2D7;
        }
        .cashier-nav ol li {
            float: left;
        }
        .cashier-nav li.current {
            color: #AB4400;
            font-weight: bold;
        }
        .cashier-nav li.last {
            clear:right;
        }
        .alipay_link {
            text-align:right;
        }
        .alipay_link a:link{
            text-decoration:none;
            color:#8D8D8D;
        }
        .alipay_link a:visited{
            text-decoration:none;
            color:#8D8D8D;
        }
        </style>
        </head>
        <body text=#000000 bgColor=#ffffff leftMargin=0 topMargin=4>
            <div id="main">
                <div id="head">
                    <dl class="alipay_link">
                        <a target="_blank" href="http://www.alipay.com/"><span>支付宝首页</span></a>|
                        <a target="_blank" href="https://b.alipay.com/home.htm"><span>商家服务</span></a>|
                        <a target="_blank" href="http://help.alipay.com/support/index_sh.htm"><span>帮助中心</span></a>
                    </dl>
                    <span class="title">支付宝手机网页支付快速通道</span>
                </div>
                <div class="cashier-nav">
                    <ol>
                        <li class="current">1、确认信息 →</li>
                        <li>2、点击确认 →</li>
                        <li class="last">3、确认完成</li>
                    </ol>
                </div>
                <form name="alipayment" action="{$this->__pay_url}" method="POST" target="_blank">
                    <div id="body" style="clear:left">
                        <dl class="content">
                            <dt>卖家支付宝帐户：</dt>
                          <dd>
                                <span class="null-star">*</span>
                                <input size="30" name="WIDseller_email" />
                                <span></span>
                            </dd>
                            <dt>商户订单号：</dt>
                            <dd>
                                <span class="null-star">*</span>
                                <input size="30" name="WIDout_trade_no" />
                                <span></span>
                            </dd>
                            <dt>订单名称：</dt>
                            <dd>
                                <span class="null-star">*</span>
                                <input size="30" name="WIDsubject" />
                                <span></span>
                            </dd>
                            <dt>付款金额：</dt>
                            <dd>
                                <span class="null-star">*</span>
                                <input size="30" name="WIDtotal_fee" />
                                <span></span>
                            </dd>
                            <dt></dt>
                            <dd>
                                <span class="new-btn-login-sp">
                                    <button class="new-btn-login" type="submit" style="text-align:center;">确 认</button>
                                </span>
                            </dd>
                        </dl>
                    </div>
                </form>
                <div id="foot">
                    <ul class="foot-ul">
                        <li><font class="note-help">如果您点击“确认”按钮，即表示您同意该次的执行操作。 </font></li>
                        <li>
                            支付宝版权所有 2011-2015 ALIPAY.COM 
                        </li>
                    </ul>
                </div>
            </div>
        </body>
        </html>
EOT;
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
    public function pay($params = array())
    {
        if (!$this->input->is_post_request()) {
            exit('Access denied');
        }

        require_once($this->__sdk . 'lib/alipay_submit.class.php');

        //返回格式
        $format = "xml";
        //必填，不需要修改

        //返回格式
        $v = "2.0";
        //必填，不需要修改

        //请求号
        $req_id = date('Ymdhis');
        //必填，须保证每次请求都是唯一

        //**req_data详细信息**

        //服务器异步通知页面路径
        $notify_url = $this->__notify_url;
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $call_back_url = $this->__callback_url;
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //操作中断返回地址
        // $merchant_url = "http://127.0.0.1:8800/WS_WAP_PAYWAP-PHP-UTF-8/xxxx.php";
        //用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数

        //卖家支付宝帐户
        $seller_email = $_POST['WIDseller_email'];
        //必填

        //商户订单号
        $out_trade_no = $_POST['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $_POST['WIDsubject'];
        //必填

        //付款金额
        $total_fee = $_POST['WIDtotal_fee'];
        //必填

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
        //必填

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $para_token = array(
            'service'        => 'alipay.wap.trade.create.direct',
            'partner'        => trim($this->alipay_config['partner']),
            'sec_id'         => trim($this->alipay_config['sign_type']),
            'format'         => $format,
            'v'              => $v,
            'req_id'         => $req_id,
            'req_data'       => $req_data,
            '_input_charset' => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);

        //URLDECODE返回的信息
        $html_text = urldecode($html_text);

        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);

        //获取request_token
        $request_token = $para_html_text['request_token'];


        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填

        //构造要请求的参数数组，无需改动
        $parameter = array(
            'service'        => 'alipay.wap.auth.authAndExecute',
            'partner'        => trim($this->alipay_config['partner']),
            'sec_id'         => trim($this->alipay_config['sign_type']),
            'format'         => $format,
            'v'              => $v,
            'req_id'         => $req_id,
            'req_data'       => $req_data,
            '_input_charset' => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
        echo $html_text;
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
    public function callback($params = array())
    {
        require_once($this->__sdk . 'lib/alipay_notify.class.php');

        // http://devapi.xian168.com/payment/alipay/callback?out_trade_no=8&request_token=requestToken&result=success&trade_no=2015092800001000320069456556&sign=iO394DarhYnmDhVKT4%2BNo9Q80Z8wwJJdA5eRXKUsPyY4RUZHZfk5gnwua8wG2XOiEG9Dvlat4DHiFdXf9voUp55%2B8UfG5BPFAm8ZVvq2sH8vqyp16D7G6UwEirPSUq2rRb4B5ILDc8ETwoYouCj%2BVxD5Ee8XGjJSQJxG%2FdDX5c0%3D&sign_type=0001
        // // 数据示例
        // $_GET = array (
        //   'out_trade_no' => '13',
        //   'request_token' => 'requestToken',
        //   'result' => 'success',
        //   'trade_no' => '2015093000001000320069515098',
        //   'sign' => 'Idlc0sUDTct9J1Ug9fOFZl5k2gexlcChDJLCcIhHwJ9hCMYiiUhFt81toRZO1P/l+6RYLIiycNDCn4wyU1C+6+Jvp7GCsfaR6iJmCTzNVAbyYbTLn72qagt0WdSlOUHyPercEgWV/qyiFHiOndEKeqQFqzcywlMv9O5ZOP8ZCUo=',
        //   'sign_type' => '0001',
        // );

        // error_log(date('Y-m-d H:i:s') . "\t\n GET\n" . var_export($_GET, true) . "\n", 3, '/data/apps/logs/alipay.txt');

        $message = '';

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码
            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $result = $_GET['result'];


            //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                
            $message = '验证成功';

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            $message = '验证失败';
        }

        echo <<< EOT
        <!DOCTYPE HTML>
        <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <title>支付宝即时到账交易接口</title>
            </head>
            <body>
            {$message}
            </body>
        </html>
EOT;
    }

    /**
     * 转换通知
     *
     * @param   array   $params 支付信息
     *
     * @return  array 
     */
    public function transform_notify(array $params = array())
    {
        $params = [
            // 以下为必须有的字段
            'pay_sn'        => empty($params['out_trade_no']) ? '' : $params['out_trade_no'], // 商户订单号（我方订单号）
            'pay_outer_sn'  => empty($params['trade_no'])     ? '' : $params['trade_no'],     // 支付平台的订单号
            'outer_user_id' => empty($params['buyer_email'])  ? '' : $params['buyer_email'],  // 支付平台的支付人账号
            'pay_status'    => empty($params['trade_status']) ? '' : $params['trade_status'], // 交易状态
            'notify_amount' => empty($params['total_fee'])    ? '' : $params['total_fee'],    // 微信以分为单位
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
    public function notify($params = array())
    {
        // 一定要是 POST 请求
        if (!$this->input->is_post_request()) {
            exit('fail');
        }

        require_once($this->__sdk . 'lib/alipay_notify.class.php');

        // // 数据示例 (无线支付)
        // $_POST = array (
        //   'service' => 'alipay.wap.trade.create.direct',
        //   'sign' => 'IQkqWhfY5+i1uONxUVKiXBizxg45GLZt1Yx+oLuMCy5I6MRkPbflh4txwNCpdUrmGVS16GNHWVDMtw79NlMlUrezaHCl6M1dLDyShZc6Gevg0iZExXAfQgz1XhUvoDPq2/lQL2mAeoa2XXeqMrFJAu2HgTssxZY+dKuAIHhOECs=',
        //   'sec_id' => '0001',
        //   'v' => '1.0',
        //   'notify_data' => 'C0b1/XvrjtfPDSWt4j4SBWvvx8IugMDgc8p3LcQC6OFOLzmr8zZeSoxBE8960+vvmUIZ+6j9Yx9wZBcwcCQgFVkGJfWYP4F8yhWqzGVGwfPXTtMCv8+UoJ4oWm8rum1F5UIPAaVAMQJOWYjLn90ek1oyS0ZNzInhXu3Rd9PKYLRjD746DY9W9LDxWff8WHWoea7mwvf/gTaQkyEx9ovkptvQlyL1NKkGt4iStMMHjIsUNDyPnrD14/0JCLxuGBNVNfhHFBEZFgESBXc00HXS17HkCOGVl8RQe9txApwcDbDdpNWWh3ttJiI8NrZrb5DHaOlEDBODykRwDp0w8k1OykGL1sOzqLo5MBqDrDUk+t/2hOJyNjb+hGXaZK+fx8JLEawuuIzNTYcxtH1aVinP6h1JTtS4LII/cuWycIRXqLJGsjrIYgMyUAw1SDMIuVBMtoKtm7nOstJK9JzWx6ETHXocTaGpQ+8gkdI3Zy/mWIUaORfz/tL+fMHmL9Xgc9IFJ774rqloAKoo/O1f13W7+RoeOSsZPtCLLzbymoWj9zhfOtEVc5QKwB7eYjOdcfzTXfJ6VidgPT7HtfU3zcj14arKEBuGT5RwWygAUsyIwW/mh/zbS8t+UckGUX1u+lMORzZMmZnMOGziSQMoJeBWkwT+4Pw11sQmoSFbfLZMiyqYS4Zmkd8j/5tgC+3jZQ1OE8gml/XyqpnGn9Rdv6WiV0zBF9uXKUBZSaLx2VWqv1d1HUYDrqmTH4Ain3aYw3XMpQHBBBllxo+i4kPiGI4Oqnj4ZHl1nOkP6QzmlW8nrw6WMizO0qJM6Ujt5yF5yOPUGhAZzrcz96ahY8CXHl35fHL/0gkshnuB5njipBS0VoWmE5OU9Wsv0Z3SOySGnOW4Ya06pRHjLLqOMWScI+iSgQwLTgA/9sh32H9WAKTdUOzTJZlwlR4Ma/yrglAGqZG64bPv4BLryOHj6BuVuVX6uasjesYQ6TSpMncMW+yiD1rOtzhpFfDnkdtrKjX0nL/CeFOM72sgpj8sVemhzyhcNZKq+9kX5fEiMS0Aov/IMvTf0+p3Ge2PSmKyPC4k6qAHoBVRZBUN8gRlw8nPD+UtdeUNfyRiABlNVVwvzmE0/VW+v83NZuuAGtp+9fJqWocHRlVkPgwjDbyhiWB6M1hGdrOZkGR0Bo/EjoiIBatdQ99tx5ZqDrK4bA07NNMFv3giqhO07KEOYEsaLCvGMtXySPRbFlbnSeO5LFiS9y9je3XkC/Vq838PlNmTtkk2WMn4Qrypw17aPNRx5/8l/hDcC07QF65NOPSnDtdAtL6RzuKczzS5riZ9tx85Qr36oHloNgm6ceA78nXaLJm9j6Va9g==',
        // );

        // mobile.securitypay.pay （手机APP应用支付）
        // $_POST = array (
        //   'discount' => '0.00',
        //   'payment_type' => '1',
        //   'subject' => '测试的商品',
        //   'trade_no' => '2015093000001000660064194031',
        //   'buyer_email' => '18621086594',
        //   'gmt_create' => '2015-09-30 14:18:48',
        //   'notify_type' => 'trade_status_sync',
        //   'quantity' => '1',
        //   'out_trade_no' => '093014184115688',
        //   'seller_id' => '2088611816704604',
        //   'notify_time' => '2015-09-30 14:18:49',
        //   'body' => '该测试商品的详细描述',
        //   'trade_status' => 'TRADE_SUCCESS',
        //   'is_total_fee_adjust' => 'N',
        //   'total_fee' => '0.01',
        //   'gmt_payment' => '2015-09-30 14:18:49',
        //   'seller_email' => 'caiwu@xian168.com',
        //   'price' => '0.01',
        //   'buyer_id' => '2088802527689665',
        //   'notify_id' => '033cb23559054b35a4f06909efd837005o',
        //   'use_coupon' => 'N',
        //   'sign_type' => 'RSA',
        //   'sign' => 'ozVyAmtxfN1xXnDYyAeXFX/6OmyHelBLi5RFuH68ufL4dNE9wyNQAMC/Au7xIAVTxROYVP0wY7oK9Yb35jEC2+0fLHgvXQsLr0U8Kc0qyRNyru/YUQn18QKw8x2e3i/Jf26fDCHrleQRqHKzJdpUYu19hb9F13e4S6Up1VDw7X4=',
        // );

        // 转换成我方需要的格式
        $transform = $this->transform_notify($_POST);
        // 插入原始日志
        $pay_log_id = $this->add_notify_log($transform);

        // 支付是否成功
        $is_success = false;

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        // error_log(date('Y-m-d H:i:s') . "\t\n POST\n" . var_export($_POST, true) . "\n", 3, '/data/apps/logs/alipay.txt');
        // error_log(date('Y-m-d H:i:s') . "\t\n POST decrypt\n" . var_export($alipayNotify->decrypt($_POST['notify_data']), true) . "\n", 3, '/data/apps/logs/alipay.txt');


        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 请在这里加上商户的业务逻辑程序代

            
            // ——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            // 获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            // 解析notify_data(WAP)
            // 注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件

            // if ($this->alipay_config['sign_type'] == 'MD5') {
            //     $result = $this->xml2array($_POST['notify_data']);
            // }
            
            // if ($this->alipay_config['sign_type'] == '0001') {
            //     $result = $this->xml2array($alipayNotify->decrypt($_POST['notify_data']));
            // }

            // 支付宝钱包支付接口开发包2.0标准版

            // 示例
            // $result = array(
            //     'payment_type' => '1', // 支付类型。默认值为：1（商品购买）
            //     'subject' =>'测试8', // 商品名称
            //     'trade_no' =>'2015092800001000320069456556', // 支付宝交易号
            //     'buyer_email' =>'willy2855@gmail.com', // 买家支付宝账号
            //     'gmt_create' =>'2015-09-28 19:13:24', // 交易创建时间
            //     'notify_type' =>'trade_status_sync', // 通知类型
            //     'quantity' =>'1', // 购买数量
            //     'out_trade_no' =>'8', // 商户网站唯一订单号
            //     'notify_time' =>'2015-09-28 19:13:26', // 通知时间
            //     'seller_id' =>'2088611816704604', // 卖家支付宝用户号
            //     'trade_status' =>'TRADE_SUCCESS', // 交易状态
            //     'is_total_fee_adjust' =>'N', // 该交易是否调整过价格
            //     'total_fee' =>'0.01', // 交易金额
            //     'gmt_payment' =>'2015-09-28 19:13:26', // 交易付款时间
            //     'seller_email' =>'caiwu@xian168.com', // 卖家支付宝账号
            //     'price' =>'0.01', // 商品单价
            //     'buyer_id' =>'2088602283547328', // 买家支付宝用户号
            //     'notify_id' =>'f70fab703cdd7d2d5568a6e89866fab73s', // 通知校验 ID
            //     'use_coupon' =>'N', //是否在交易过程中使用了红包
            //     'discount' => '', // 折扣 支付宝系统会把discount的值加到交易金额上，如果有折扣，本参数为负数，单位为元
            //     'refund_status' => '', // 退款状态 REFUND_SUCCESS 全额退款情况：trade_status= TRADE_CLOSED，而refund_status=REFUND_SUCCESS 非全额退款情况：trade_status= TRADE_SUCCESS，而refund_status=REFUND_SUCCESS
            //                            // REFUND_CLOSED 退款关闭
            //     'gmt_refund' => '', // 卖家退款的时间，退款通知时会发送。
            // );

            if(!empty($transform) && is_array($transform) && !empty($transform['pay_sn'])) {
                // WAIT_BUYER_PAY 交易创建，等待买家付款
                // TRADE_CLOSED 在指定时间段内未支付时关闭的交易 在交易完成全额退款成功时关闭的交易

                // 注意：TRADE_FINISHED
                // 该种交易状态只在两种情况下出现
                // 1、开通了普通即时到账，买家付款成功后。
                // 2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。

                // 注意：TRADE_SUCCESS
                // 该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
                // error_log(date('Y-m-d H:i:s') . "\t\n 1:\n" . "标记1\n", 3, '/data/apps/logs/alipay.txt');
                if(in_array($transform['pay_status'], array('TRADE_FINISHED', 'TRADE_SUCCESS'))) {

                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                    // 比对一下金额，要和支付里的信息一样才能更新。

                    // 如果成功执行
                    $this->notify_success($transform);

                    // error_log(date('Y-m-d H:i:s') . "\t\n 2:\n" . "trade status is correct\n", 3, '/data/apps/logs/alipay.txt');
                } else {
                    // error_log(date('Y-m-d H:i:s') . "\t\n 3:\n" . "trade status is invalid\n", 3, '/data/apps/logs/alipay.txt');
                }

                // error_log(date('Y-m-d H:i:s') . "\t\n 222:\n" . "trade status is correct\n", 3, '/data/apps/logs/alipay.txt');
                $is_success = true;
                // exit('success');     //请不要修改或删除
            } else {
                // error_log(date('Y-m-d H:i:s') . "\t\n 4:\n" . "result is invalid\n", 3, '/data/apps/logs/alipay.txt');
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            // error_log(date('Y-m-d H:i:s') . "\t\n 5:\n" . "fail\n", 3, '/data/apps/logs/alipay.txt');
            $is_success = false;
            // exit('fail');

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
        // error_log(date('Y-m-d H:i:s') . "\t\n 6:\n" . "over\n", 3, '/data/apps/logs/alipay.txt');

        if (true === $is_success) {
            // 更新处理结果
            $this->update_notify_log_by_id($pay_log_id);

            exit('success');
        } else {
            exit('fail');
        }
    }
    // ===================== 以上函数一般来说是一定要实现的 ====================

    /**
     * 即时到账批量退款
     *
     * @param  array $params 输入参数
     *
     * @return void
     */
    public function refund_batch_by_pwd($params = [])
    {
        require_once($this->__sdk . 'lib/alipay_submit.class.php');

        //构造要请求的参数数组，无需改动
        $parameter = array(
            'service'        => 'refund_fastpay_by_platform_pwd',
            'partner'        => $this->alipay_config['partner'],
            'notify_url'     => isset($params['notify_url']) ? $params['notify_url'] : $this->__refund_batch_notify_url,
            'seller_email'   => $this->__account,
            'refund_date'    => get_date(),
            'batch_no'       => $params['batch_no'],     // 批次号 201603080001
            'batch_num'      => $params['batch_num'],    // 本次多少个订单 2
            'detail_data'    => $params['detail_data'],  // 订单描述 '2011011201037066^5.00^协商退款#2011011201037067^6.00^用户退款'
            '_input_charset' => $this->alipay_config['input_charset'],
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);

        $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '确认');
        return $html_text;
    }

    /**
     * 即时到账批量退款异步通知
     *
     * @param  array  $params 输入参数
     *
     * @return void
     */
    public function refund_batch_notify($params = [])
    {
        /* 请求参数
        Array
        (
            [sign] => BaT8qZ/MrRqgt2qskYFrbLn7P7CaYtHthsJQaNH8lBkSNYag9NMvjozpo6Iwftaic791vzeW3m5BPuNRKpUE0FWdAhhSfzwAdA9Wj8yJ2R/mLFKyQs/h1lLEq/s0JPXICgqzg2mA3PHDQ9Cwaeq/tSvhLKppfZZ0a5MtCCdnNG8=
            [result_details] => 2016031721001004670245932381^0.20^SUCCESS#2016031721001004670247063531^0.24^SUCCESS
            [notify_time] => 2016-03-17 13:45:09
            [sign_type] => RSA
            [notify_type] => batch_refund_notify
            [notify_id] => c08606fc38c232d0e9b21af966278e7gdx
            [batch_no] => 20160317458193492932
            [success_num] => 2
        )
        */

        // 记录请求日志
        add_txt_log(json_encode($_POST), 'alipay_refund_batch_notify');

        // 一定要是 POST 请求
        if (!$this->input->is_post_request()) {
            exit('fail');
        }

        require_once($this->__sdk . 'lib/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify  = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {
            //批次号
            $params['batch_no'] = $_POST['batch_no'];

            //批量退款数据中转账成功的笔数
            $params['success_num'] = $_POST['success_num'];

            //批量退款数据中的详细信息
            $params['result_details'] = $_POST['result_details'];

            // 支付方式
            $params['pay_type'] = 2;

            // 调用退款通知成功服务
            $this->load->library('requester');
            $service_info = [
                'service_name'   => 'fdm_order.order_operation.update_refund_status',
                'service_params' => $params,
            ];
            $result = $this->requester->request($service_info);
            if (false === $result['success']) {
                exit('fail');
            }
            exit('success');
        } else {
            exit('fail');
        }
    }

    /**
     * 批量付款到支付宝账户（有密接口）
     *
     * @param  array  $params 输入参数
     * @return void
     */
    public function batch_trans_notify_by_pwd($params)
    {
        require_once($this->__sdk . 'lib/alipay_submit.class.php');

        //构造要请求的参数数组，无需改动
        $parameter = array(
            'service'        => 'batch_trans_notify',
            'partner'        => $this->alipay_config['partner'],
            'notify_url'     => $this->__batch_trans_notify_by_pwd_url,
            'email'          => $this->__account,
            'account_name'   => $this->__account_name,
            'pay_date'       => date('Ymd'),
            'batch_no'       => $params['batch_no'],     // 批次号 201603080001
            'batch_num'      => $params['batch_num'],    // 本次多少个订单 2
            'detail_data'    => $params['detail_data'],  // 订单描述 '0315006^testture0002@126.com^常炜买家^20.00^hello'
            'batch_fee'      => $params['batch_fee'],
            '_input_charset' => $this->alipay_config['input_charset'],
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);

        $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '确认');
        return $html_text;
    }

    /**
     * 批量付款到支付宝账户异步通知
     *
     * @param  array  $params 输入参数
     *
     * @return void
     */
    public function batch_trans_by_pwd_notify($params = [])
    {
        /* 请求参数
        Array
        (
            [sign] => BaT8qZ/MrRqgt2qskYFrbLn7P7CaYtHthsJQaNH8lBkSNYag9NMvjozpo6Iwftaic791vzeW3m5BPuNRKpUE0FWdAhhSfzwAdA9Wj8yJ2R/mLFKyQs/h1lLEq/s0JPXICgqzg2mA3PHDQ9Cwaeq/tSvhLKppfZZ0a5MtCCdnNG8=
            [result_details] => 2016031721001004670245932381^0.20^SUCCESS#2016031721001004670247063531^0.24^SUCCESS
            [notify_time] => 2016-03-17 13:45:09
            [sign_type] => RSA
            [notify_type] => batch_refund_notify
            [notify_id] => c08606fc38c232d0e9b21af966278e7gdx
            [batch_no] => 20160317458193492932
            [success_num] => 2
        )
        */

        // 记录请求日志
        // add_txt_log(json_encode($_POST), 'alipay_batch_trans_by_pwd_notify');

        // 一定要是 POST 请求
        if (!$this->input->is_post_request()) {
            exit('fail');
        }

        require_once($this->__sdk . 'lib/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify  = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {
            //批次号
            $params['batch_no'] = $_POST['batch_no'];

            //批量退款数据中转账成功的笔数
            $params['success_num'] = $_POST['success_num'];

            //批量退款数据中的详细信息
            $params['result_details'] = $_POST['result_details'];

            // 支付方式
            $params['pay_type'] = 2;

            /*
            // 调用退款通知成功服务
            $this->load->library('requester');
            $service_info = [
                'service_name'   => 'fdm_order.order_operation.update_refund_status',
                'service_params' => $params,
            ];
            $result = $this->requester->request($service_info);
            */

            if (false === $result['success']) {
                exit('fail');
            }
            exit('success');
        } else {
            exit('fail');
        }
    }

    /**
     * 支付宝即时到账交易接口
     *
     * @param  array $params 输入参数
     *
     * @return void
     */
    public function create_direct_pay_by_user($params = [])
    {
        require_once($this->__sdk . 'lib/alipay_submit.class.php');

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"           => 'create_direct_pay_by_user',
            "partner"           => $this->alipay_config['partner'],
            "seller_id"          => $this->alipay_config['partner'],
            "payment_type"       => 1,
            "notify_url"         => $this->__notify_url,
            "return_url"         => $this->__create_direct_pay_by_user_return_url,
            "out_trade_no"       => $params['out_trade_no'],
            "subject"            => $params['subject'],
            "total_fee"          => $params['total_fee'],
            "extra_common_param" => $params['extra_common_param'],
            "_input_charset"     => $this->alipay_config['input_charset'],
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);

        $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '确认');

        return $html_text;
    }

    /**
     * 支付宝即时到账页面跳转同步页面
     *
     * @param  array $params 输入参数
     *
     * @return void
     */
    public function create_direct_return_url($params = [])
    {
        require_once($this->__sdk . 'lib/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify  = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        // 让签名通过（暂未找到签名不通过的原因，此回调只是展示页面，无安全影响）todo xucheng......
        $verify_result = true;

        if($verify_result) {//验证成功
            // 设置跳转url
            $error_url_redirect   = '';
            $success_url_redirect = '';
            if ('pc_order' == $_GET['extra_common_param']) {
                $error_url_redirect   = WWW_DOMAIN . 'order/pay_failure';
                $success_url_redirect = WWW_DOMAIN . 'order/pay_success';
            } else if ('pc_add_fee' == $_GET['extra_common_param']) {
                $error_url_redirect   = WWW_DOMAIN . 'pay/pay_failure';
                $success_url_redirect = WWW_DOMAIN . 'pay/pay_success';
            } else if ('center_add_fee' == $_GET['extra_common_param']) {   // 中心平台加盟商充值
                $error_url_redirect   = CENTER_DOMAIN . 'account/charge_failure';
                $success_url_redirect = CENTER_DOMAIN . 'account/charge_success';
            }

            // 商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            // 支付宝交易号
            $trade_no = $_GET['trade_no'];

            // 交易状态
            $trade_status = $_GET['trade_status'];

            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                // 支付成功

                // 记录是否存在
                $fields = 'pay_outer_sn,related_sn';
                $exist  = $this->get_by_pay_sn($out_trade_no, $fields);

                // 如果没有这条信息，那么说明是有问题的。
                if (empty($exist) || !is_array($exist)) {
                    echo '没有这条信息<br />';
                    exit;
                }

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                // echo "支付成功<br />";
                url_redirect($success_url_redirect . '?order_id=' . $exist['related_sn']);

            } else {
                // 支付失败
                // echo "trade_status=".$trade_status;
                url_redirect($error_url_redirect);
            }

            echo "验证成功<br />";
        } else {
            echo "验证失败";
        }
    }

}