<?php
 /**
 * 微信
 *
 * @author      熊飞龙
 * @date        2015-01-29
 * @category    Weixin.php
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class MY_Weixin
{
    // 是否调试模式
    public $__is_debug = false;
    // 微信用户账号
    public $user       = null;
    // token
    private $__token   = null;
    // AppID
    private $__appid   = null;
    // AppSecret
    private $__secret  = null;
    // 微信 access_token
    private $__access_token          = null;
    // 连接超时秒数
    private $__request_timeout       = 5;
    // access_token 在 memcache 中的名称
    private $__memcache_access_token = 'weixin_access_token';
    // js_ticket 在 memcache 中的名称
    private $__memcache_js_ticket    = 'weixin_js_ticket';
    // memcache 中的缓存时间 (目前 微信的时间为 7200, 但是微信说可能会变)
    private $__memcache_expire_time  = 7000;
    // 渠道类型（必填项）
    private $__channel_type = 40;  // 40 表示微
    // 记录错误消息
    private $__error        = "";
    // 日志存放路径
    public $__log_path      = '/tmp/weixin.txt';
    // 模板列表 （模板列表放在配置文件中）
    private $__template_list = array();

    // 接口错误列表
    private $__error_list = array(
        '-1'    => '系统繁忙，此时请开发者稍候再试',
        '0'     => '请求成功',
        '40001' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID',
        '40004' => '不合法的媒体文件类型',
        '40005' => '不合法的文件类型',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写',
        '40014' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口',
        '40015' => '不合法的菜单类型',
        '40016' => '不合法的按钮个数',
        '40017' => '不合法的按钮个数',
        '40018' => '不合法的按钮名字长度',
        '40019' => '不合法的按钮KEY长度',
        '40020' => '不合法的按钮URL长度',
        '40021' => '不合法的菜单版本号',
        '40022' => '不合法的子菜单级数',
        '40023' => '不合法的子菜单按钮个数',
        '40024' => '不合法的子菜单按钮类型',
        '40025' => '不合法的子菜单按钮名字长度',
        '40026' => '不合法的子菜单按钮KEY长度',
        '40027' => '不合法的子菜单按钮URL长度',
        '40028' => '不合法的自定义菜单使用用户',
        '40029' => '不合法的oauth_code',
        '40030' => '不合法的refresh_token',
        '40031' => '不合法的openid列表',
        '40032' => '不合法的openid列表长度',
        '40033' => '不合法的请求字符，不能包含uxxxx格式的字符',
        '40035' => '不合法的参数',
        '40038' => '不合法的请求格式',
        '40039' => '不合法的URL长度',
        '40050' => '不合法的分组id',
        '40051' => '分组名字不合法',
        '41001' => '缺少access_token参数',
        '41002' => '缺少appid参数',
        '41003' => '缺少refresh_token参数',
        '41004' => '缺少secret参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => '缺少media_id参数',
        '41007' => '缺少子菜单数据',
        '41008' => '缺少oauthcode',
        '41009' => '缺少openid',
        '42001' => 'access_token超时，请检查access_token的有效期，请参考基础支持获取access_token中，对access_token的详细机制说明',
        '42002' => 'refresh_token超时',
        '42003' => 'oauth_code超时',
        '43001' => '需要GET请求',
        '43002' => '需要POST请求',
        '43003' => '需要HTTPS请求',
        '43004' => '需要接收者关注',
        '43005' => '需要好友关系',
        '44001' => '多媒体文件为空',
        '44002' => 'POST的数据包为空',
        '44003' => '图文消息内容为空',
        '44004' => '文本消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '45010' => '创建菜单个数超过限制',
        '45015' => '回复时间超过限制',
        '45016' => '系统分组，不允许修改',
        '45017' => '分组名字过长',
        '45018' => '分组数量超过上限',
        '46001' => '不存在媒体数据',
        '46002' => '不存在的菜单版本',
        '46003' => '不存在的菜单数据',
        '46004' => '不存在的用户',
        '47001' => '解析JSONXML内容错误',
        '48001' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网开发者中心页中查看接口权限',
        '50001' => '用户未授权该api',
        '61451' => '参数错误(invalid paramete)',
        '61452' => '无效客服账号(invalid kf_accoun)',
        '61453' => '客服帐号已存在(kf_account exsite)',
        '61454' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount lengt)',
        '61455' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_accoun)',
        '61456' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceede)',
        '61457' => '无效头像文件类型(invalid file typ)',
        '61450' => '系统错误(system erro)',
        '61500' => '日期格式错误',
        '61501' => '日期范围错误',
    );

    public function __construct($params = array())
    {
        // ci 自动加载配置文件
        $err_msg = '';

        if (empty($params)) {
            $err_msg .= "加载微信配置文件失败！\n";
        }

        if (empty($params['weixin_token'])) {
            $err_msg .= "未配置 weixin_token\n";
        }

        if (empty($params['weixin_appid'])) {
            $err_msg .= "未配置 weixin_appid\n";
        }

        if (empty($params['weixin_appsecret'])) {
            $err_msg .= "未配置 weixin_appsecret\n";
        }

        if (empty($params['weixin_user'])) {
            $err_msg .= "未配置 weixin_user\n";
        }

        if (empty($params['template_list'])) {
            $err_msg .= "未配置 template_list\n";
        }

        if (!empty($err_msg)) {
            show_error($err_msg);
        }

        $this->__token  = $params['weixin_token'];
        $this->__appid  = $params['weixin_appid'];
        $this->__secret = $params['weixin_appsecret'];
        $this->user     = $params['weixin_user'];
        $this->__log_path = empty($params['weixin_log_path']) ? $this->__log_path : $params['weixin_log_path'];
        $this->__template_list = $params['template_list'];
    }

    /**
     * 自动加载 CI 的属性
     */
    public function __get($key)
    {
        $CI = &get_instance();
        return $CI->$key;
    }

    /**
     * 设置错误信息
     */
    private function set_error($msg = '')
    {
        $this->__error = $msg;
    }

    /**
     * 获取错误信息
     */
    public function get_error()
    {
        return $this->__error;
    }

    /**
    * 日志记录
    */
    private function __log($action, $data = array())
    {
        if (true === $this->__is_debug) {
            if (is_array($data)) {
                file_put_contents($this->__log_path, date('Y-m-d H:i:s') .' ' . $action . ' -> '. json_encode($data) . ' -|Result|-> '.$this->get_error() . "\n", FILE_APPEND);
            } else if (is_string($data)) {
                file_put_contents($this->__log_path, date('Y-m-d H:i:s') .' ' . $action . ' -> '. $data . "\n", FILE_APPEND);
            }
        }
    }

    /**
    * 处理返回结果集
    */
    private function __result($params = '')
    {
        $result = array();

        // 如果有错误则写日志
        if ($this->get_error()) {
            $this->__log('result', $params);
        }

        if (!empty($params)) {
            $result = json_decode($params, true);
        }

        if (isset($result['errmsg']) && $result['errmsg'] != 'ok') {
            $data = array(
                'app_id'                        =>  $this->__appid,
                'secret'                        =>  $this->__secret,
                'access_token'                  =>  $this->__access_token,
                'url'                           =>  $this->url,
                'content'                       =>  $this->content,
            );

            $this->set_error($result['errcode'] . ':' . $this->__error_list[$result['errcode']] . ' -- data:' . json_encode($data));
            return false;
        }

        return $result;
    }

    /**
    * 将数组的 value 进行 urlencode 编码
    */
    private function __urlencode(array $params)
    {
        $result = array();
        foreach($params as $key => $val){
            if (is_array($val)) {
                $result[$key] = $this->__urlencode($val);
            } else if (is_string($val)) {
                $result[$key] = urlencode($val);
            }
        }

        return $result;
    }

    /**
    * 将数组转换为 json
    */
    public function __array_to_json(array $params)
    {
        $result = $this->__urlencode($params);
        return urldecode(json_encode($result));
    }

    /**
    * 计算签名
    *
    * @param   array   $params
    *
    * @return  string|bool
    */
    public function sum_signature(array $params)
    {
        ksort($params, SORT_STRING);

        $to_sign = array();
        foreach ($params as $key => $val) {
            $to_sign[] = "$key=$val";
        }

        $to_sign = implode('&', $to_sign);

        return sha1($to_sign);
    }

    /**
    * 验证签名 (主要用于处理被动请求，例：由微信主动发起或跳转的请求, 如微信需要获得回执，使用本函数的返回值及可)
    *
    * @param   array   $params
    *
    * @return  string|bool
    */
    public function check_signature(array $params)
    {
        $nonce     = $params["nonce"];
        $timestamp = $params["timestamp"];
        $signature = $params["signature"];

        $token  = $this->__token;
        $tmpArr = array($timestamp, $nonce, $token);

        sort($tmpArr, SORT_STRING);

        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr != $signature){
            $this->__log('signature error', $params);
            $this->set_error('weixin signature error！');
            return false;
        }

        $data = $this->get_data();

        $result = array();

        // 处理事件
        if (isset($data['MsgType']) && $data['MsgType'] == 'event') {

            return $this->weixin_event($data);

        // 处理微信消息
        } else if (isset($data['MsgType']) && $data['MsgType'] != 'event') {

            return $this->wexin_msg($data);
        }

        return empty($params["echostr"]) ? '' : $params["echostr"];
    }

    /**
    * 验证消息真实性 (主要用于处理被动请求，例：由微信主动发起或跳转的请求, 如微信需要获得回执，使用本函数的返回值及可)
    *
    * @param   array   $params
    *
    * @return  string|bool
    */
    public function get_data()
    {
        $content = file_get_contents('php://input');
        if (empty($content)) {
            return array();
        }

        $data = xml2array($content);
        $data = empty($data) ? array() : $data;

        $this->__log('data', $data);

        return $data;
    }

    /**
    * 微信事件处理
    */
    private function weixin_event(array $params)
    {
        if (empty($params['Event'])) {
            return '';
        }

        $result = array();

        // 表明渠道类型
        $params['channel_type'] = $this->__channel_type;

        // 渠道会员唯一标识 open_id
        $params['open_id'] = $params['FromUserName'];

        switch($params['Event']) {

            // 用户关注微信号
            case 'subscribe':
                $result = $this->__event_subscribe($params);
                break;

            // 用户取消关注
            case 'unsubscribe':
                $result = $this->__event_unsubscribe($params);
                break;

            // 获得用户的地理位置
            case 'LOCATION':
                $result = $this->__event_location($params);
                break;

            // 自定义菜单事件
            case 'CLICK':
                break;

            // 微信服务器执行操作的反馈
            case 'TEMPLATESENDJOBFINISH':
                $result = '';
                break;

            default:
                $this->set_error("未定义的事件！");
                break;
        }

        if ($result === false) {
            $this->__log('event error', $this->get_error());
        } else {
            $this->__log($params['Event'], $params);
        }

        // 回复空串，微信服务器不会对此作任何处理
        return empty($result) ? '' : $result;
    }

    /**
    * 微信消息处理
    */
    private function wexin_msg(array $params)
    {
        // 不处理任何消息
        return '';

        if (empty($params['MsgType'])) {
            return '';
        }

        return $this->__msg_text($params['FromUserName'], '这是一条微信自动回复测试');
    }

    /**
    * 微信消息处理
    *
    * @param   string   $openid     用户唯一标识
    * @param   string   $content    文本内容
    *
    * @return  string|bool
    */
    private function __msg_text($openid, $content = '')
    {
        if (empty($openid)) {
            $this->set_error("未指定 openid");
            return false;
        }

        $template = '<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                    </xml>';
        return sprintf($template, $openid, $this->user, time(), $content);
    }

    /**
    * 事件 - 用户关注微信
    */
    private function __event_subscribe(array $params)
    {
        // 推荐人的 ticket
        $params['invite_code'] = !isset($params['Ticket']) ? '' : $params['Ticket'];

        // 获得关注人的基本资料
        $params['channel_user_info'] = $this->get_user_info($params['open_id']);

        // 处理用户关注事件
        $this->load->model("User_channel_model");
        $result = $this->User_channel_model->subscribe($params);
        if ($result === false) {
            $this->set_error($this->User_channel_model->get_error());
            return false;
        }

        // 如果没有绑定手机，则引导去绑定
        if (isset($result) && is_array($result) && !$result['is_exist_phone']) {

            $this->load->model("Configure_model");
            $content = $this->Configure_model->get_template_by_configure_name('weixin_event_subscribe');

            return $this->__msg_text($params['open_id'], $content);
        }

        return '';
    }

    /**
    * 事件 - 用户取消关注微信
    */
    private function __event_unsubscribe(array $params)
    {
        // 将用户标记为无效
        $this->load->model("User_channel_model");
        if (!$this->User_channel_model->unsubscribe($params)) {
            $this->set_error($this->User_channel_model->get_error());
            return false;
        }

        return '';
    }

    /**
    * 事件 - 获得微信用户地理位置
    */
    private function __event_location(array $params)
    {
        $this->load->model("User_channel_model");
        if(!$this->User_channel_model->update_channel_user_location($params)) {
            $this->set_error($this->User_channel_model->get_error());
            return false;
        }

        return '';
    }

    /**
    * 创建 自定义菜单
    */
    public function set_menu(array $params = array())
    {
        // 可在微信公众平台后台自定义菜单, 此处暂时停用

        $this->set_error('可在微信公众平台后台自定义菜单, 此处暂时停用');
        return false;

        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->get_access_token();
        return $this->request($url, $this->__array_to_json($params));
    }

    /**
    * 发送 模板消息（业务通知） [仅能单条发送]
    */
    public function send_template_message(array $params)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->get_access_token();
        return $this->request($url, json_encode($params));
    }

    /**
    * 获得用户详细信息
    *
    * @param    string    openid    用户 token
    *
    * @return   array|bool
    */
    public function get_user_info($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?openid={$openid}&access_token=" . $this->get_access_token();
        $user_info = $this->request($url);
        if ($user_info === false) {
            return false;
        }

        // 规范渠道会员基础信息数据格式
        return array(
            'channel_open_id'  => $openid,
            'channel_member'   => '',  // 该渠道未提供此项
            'channel_nickname' => $user_info['nickname'],
            'sex'              => ($user_info['sex'] != 1 || $user_info['sex'] != 2) ? 3 : $user_info['sex'],
            'country'          => $user_info['country'],
            'province'         => $user_info['province'],
            'city'             => $user_info['city'],
            'create_time'      => date('Y-m-d H:i:s', $user_info['subscribe_time']),
        );
    }

    /**
    * 获得创建二维码的 ticket
    *
    * @param    string    openid    用户 token
    *
    * @return   array|bool
    */
    public function get_qrcode_ticket(array $params)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->get_access_token();
        return $this->request($url, json_encode($params));
    }

    /**
    * 创建二维码（注意 $path 路径需要有写权限）
    *
    * @param    string    openid    用户 token
    *
    * @return   void
    */
    public function create_qrcode($ticket, $path = '')
    {
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;

        $img = file_get_contents($url);
        return file_put_contents($path, $img);
    }

    /**
     * 获得 JSSDK 的 ticket
     *
     * @return   string
     */
    public function get_js_ticket()
    {
        // 检查 js_ticket 是否存在
        $this->load->library('memcache');
        $memcache_js_ticket = $this->memcache->get($this->__memcache_js_ticket);

        // 先默认为空
        $this->__js_ticket = '';

        if (empty($memcache_js_ticket)) {

            // 获得 js_ticket
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $this->get_access_token();
            $result = $this->request($url);

            $this->__log('get_js_ticket', $result);

            if (!empty($result) && $result['ticket']) {
                $this->__js_ticket = $result['ticket'];
                // memcache 中的缓存时间 (目前 微信的时间为 7200, 但是微信说可能会变)
                $this->__memcache_expire_time = intval($result['expires_in']) > 0 ? $result['expires_in'] : $this->__memcache_expire_time;
                $this->memcache->set($this->__memcache_js_ticket, $this->__js_ticket, $this->__memcache_expire_time);
            } else {
                // 记录错误信息
                $this->__log('get_js_ticket_error', $result);
            }

        } else {
            // 使用未过期的 js_ticket
            $this->__js_ticket = $memcache_js_ticket;
        }

        return $this->__js_ticket;
    }

    /**
     * 获得 JSSDK 的配置信息
     *
     * @return  void
     */
    public function get_js_config($params = array())
    {
        $params = array(
            'noncestr'     => $params['noncestr'],
            'jsapi_ticket' => $this->get_js_ticket(),
            'timestamp'    => $params['timestamp'],
            'url'          => $params['url'],
        );

        $params['signature'] = $this->sum_signature($params);
        $params['appid'] = $this->__appid;

        return $params;
    }

    /**
    * 获得 access_token (该 access_token 与网页上的 access_token 不同)
    */
    private function get_access_token()
    {
        // 检查 access_token 是否存在
        $this->load->library('memcache');
        $memcache_access_token = $this->memcache->get($this->__memcache_access_token);

        // 先默认为空
        $this->__access_token = '';

        if (empty($memcache_access_token)) {

            // 获得 access_token
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->__appid . '&secret=' . $this->__secret;
            $result = $this->request($url);

            $this->__log('get_access_token', $result);

            if (!empty($result) && $result['access_token']) {
                $this->__access_token = $result['access_token'];
                // memcache 中的缓存时间 (目前 微信的时间为 7200, 但是微信说可能会变)
                $this->__memcache_expire_time = intval($result['expires_in']) > 0 ? $result['expires_in'] : $this->__memcache_expire_time;
                $this->memcache->set($this->__memcache_access_token, $this->__access_token, $this->__memcache_expire_time);
            } else {
                // 记录错误信息
                $this->__log('get_access_token_error', $result);
            }

        } else {

            // 使用未过期的 access_token
            $this->__access_token = $memcache_access_token;
        }

        return $this->__access_token;
    }

    /**
    * 开始派送消息模板
    */
    private function __start_delivery_template(array $params, $to_user)
    {
        $this->load->model("Configure_model");
        $first = $this->Configure_model->get_template_by_configure_name('weixin_start_delivery_first');
        if($first == false) {
            $this->set_error($this->Configure_model->get_error());
            return false;
        }

        $remark = $this->Configure_model->get_template_by_configure_name('weixin_start_delivery_remark');
        if($remark == false) {
            $this->set_error($this->Configure_model->get_error());
            return false;
        }

        $color = '#fa6f57';
        $template = array(
            'touser'      => $to_user,
            'template_id' => $this->__template_list['start_delivery']['template_id'],
            'url'         => empty($params['url']) ? '' : $params['url'],
            'topcolor'    => $color,
            'data' => array(
                'first' => array(
                    'value' => $first,
                    'color' => '#5e5b5b',
                ),
                'keyword1' => array(
                    'value' => $params['shop_order_id'],
                    'color' => $color,
                ),
                'keyword2' => array(
                    'value' => $params['shop_name'],
                    'color' => $color,
                ),
                'keyword3' => array(
                    'value' => $params['courier_name'],
                    'color' => $color,
                ),
                'keyword4' => array(
                    'value' => $params['courier_phone'],
                    'color' => $color,
                ),
                'remark' => array(
                    'value' => $remark,
                    'color' => '#5e5b5b',
                ),
            ),
        );

        return $template;
    }

    /**
    * 主动推送微信消息 （含业务）
    */
    public function push_message($order_key, $template_key)
    {
        if (!in_array($template_key, array_keys($this->__template_list))) {
            $this->set_error("指定的模板不存在或未配置！");
            return false;
        }

        // 获得订单信息用户信息
        $this->load->model('Shop_order_model');
        $order = $this->Shop_order_model->get_by_key($order_key, 'shop_order_id,shop_order_key,telephone,shop_name,courier_name,courier_phone');
        if(empty($order)) {
            $this->set_error("指定的订单不存在！");
            return false;
        }

        // 获得用户信息
        $channel_user_where = array(
            'fields'       => 'user_channel_id, channel_open_id',
            'user_phone'   => $order['telephone'],
            'channel_type' => $this->__channel_type,
        );
        $this->load->model("User_channel_model");
        $channel_user = $this->User_channel_model->get($channel_user_where);
        if(empty($channel_user)) {
            $this->set_error("用户不存在或取消关注微信！");
            return false;
        }

        // 模板格式
        $method_name  = '__' . $template_key . '_template';
        if (!method_exists($this, $method_name)) {
            $this->set_error("指定的模板不存在或未添加模板函数！");
            return false;
        }

        // 点击订单跳转的链接
        $this->load->model("Configure_model");
        $weixin_message_click_url = $this->Configure_model->get_template_by_configure_name('weixin_message_click_url');
        if($weixin_message_click_url != 'null'){
            $order['url'] = $weixin_message_click_url . $order['shop_order_key'];
        }

        $template = $this->$method_name($order, $channel_user['channel_open_id']);
        return $this->__add_queue('send_template_message', $template);
    }

    /**
     * 进入 oauth2 验证
     */
    public function oauth2($back_url)
    {
        // 由于用户关注过微信公众号，此处采用 静默授权(snsapi_base) 方式。

        /*  跳转后能获得的 get 参数格式为
            array(
                "code"  => "xx",
                "state" => "xx"
            )
        */

        $back_url = urlencode($back_url);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->__appid}&redirect_uri={$back_url}&response_type=code&scope=snsapi_base&state={$this->user}#wechat_redirect";

        header('Location: '. $url);
        exit;
    }

    /**
     * 根据 oauth2 code 换取网页授权 access_token
     */
    public function get_authorization_code($code)
    {
        /*  接口返回结果集如下
            {
               "access_token":"ACCESS_TOKEN",
               "expires_in":7200,
               "refresh_token":"REFRESH_TOKEN",
               "openid":"OPENID",
               "scope":"SCOPE"
            }
        */
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->__appid}&secret={$this->__secret}&code={$code}&grant_type=authorization_code";
        return $this->request($url);
    }

    /**
    * 发送请求
    */
    private function request($url, $content = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);               //要打开的URL地址
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);                 //设置为POST方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);    //参数
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);        //使用 curl_exec 函数得到返回值
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->__request_timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->__request_timeout);

        $result = curl_exec($curl);
        curl_close($curl);

        $this->url      = $url;
        $this->content  = $content;

        return $this->__result($result);
    }

    /**
    * 添加到队列
    */
    private function __add_queue($method_name, $data)
    {
        $queue_data = array(
            'method' => $method_name,
            'data'   => $data,
        );

        $this->load->library('queue');
        $result = $this->queue->send('weixin', $queue_data);

        if (false === $result) {
            $this->set_error($this->queue->get_error());
            return false;
        } else {
            return true;
        }
    }

    /**
    * 运行队列
    */
    public function run_queue(array $params)
    {
        // 清除错误消息
        $this->set_error('');

        if (!method_exists($this, $params['method'])) {
            $this->set_error("指定的模板不存在或未添加模板函数！");
            return false;
        }

        $result = $this->$params['method']($params['data']);
        if ($result == false) {
            return false;
        }

        return $result['errmsg'];
    }
}