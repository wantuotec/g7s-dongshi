<?php
/**
 * 大汉三通短信发送（语音）
 *
 * @author      willy
 * @date        2016-07-22
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class CI_Sms_3 extends CI_Sms {
    // 同一条短信最多可以发给多少个手机号
    public $max_number = 500;

    // 提交地址
    private $__request_url = 'http://voice.3tong.net/json/voiceSms/SubmitVoc';
    private $__report_url  = 'http://voice.3tong.net/json/voiceSms/GetReport';
    // 用户名
    private $__username    = 'dh57168';
    // 密码
    private $__password    = 'ui5@J4Ap';

    // 子码
    private $__subcode     = '';

    // 连接超时秒数
    private $__timeout     = 30;

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {        
        $this->__password = md5($this->__password); // 只初始化一次
    }

    /**
     * curl 请求
     *
     * @return  void
     */
    public function __curl($message, $url = '')
    {        
        // 消息体首尾不能有空格
        $message = trim($message);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, empty($url) ? $this->__request_url : $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        // 设置最大连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->__timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->__timeout);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_POST      , 1);
        $result = curl_exec($ch);

        if(curl_errno($ch)) {
            $this->set_error(curl_error($ch));
            return false;
        }
        curl_close($ch);

        return $result;
    }

    /**
     * 发送短信 （队列里调用）
     *
     * @access  public
     *
     * @param   string  $mobile     手机号
     * @param   string  $content    短信内容
     * @param   string  $sign       签名
     *
     * @return  void
     */
    public function _queue_send($mobile, $content, $sign = '')
    {
        // add by mark 目前测试下来 content不能是中文，只能为8位以下的数字 2016-07-22 15:35:05
        // 目前语音验证码 $mobile 只能是单个手机号，不能多个
        // $mobile  = implode(',', $mobile);
        $mobile = array_shift($mobile);

        $message = [
            'account'  => $this->__username,
            'password' => $this->__password,
            'data'     => [
                [
                    'callee'    => $mobile,
                    'text'      => $content,
                    'medianame' => '',
                    'calltype'  => 1, // 0-普通文本呼叫；1-验证码呼叫；2-语音文件呼叫；3-混合呼叫  默认为0
                    'playmode'  => 0,
                ],
            ],
        ];

        $result = $this->__curl(json_encode($message));
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // result 示例
        // 失败 {"result":"DH:0000","desc":"成功","data":[{"status":"DH:1094","desc":"内容格式不正确:中车","msgid":"859a896c3a9d4ddea687c02a8b77ae80"}]}
        // 成功 {"result":"DH:0000","desc":"成功"} 无 msgid 暂不能获取报告

        $response = json_decode($result, true);
        if (empty($result) && !is_array($result)) {
            $this->set_error('result must be json:' . $result);
            return false;
        } else {
            $this->set_error("response:" . $result);
            return true;
        }
    }
}