<?php
/**
 * 大汉三通短信发送
 *
 * @author      willy
 * @date        2014-10-09
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class CI_Sms_1 extends CI_Sms {
    // 同一条短信最多可以发给多少个手机号
    public $max_number = 500;

    // 提交地址
    private $__request_url = 'http://3tong.net/http/sms/Submit';
    private $__report_url  = 'http://wt.3tong.net/http/sms/Report';
    // 用户名
    private $__username    = 'dh57168';
    // 密码
    private $__password    = 'ui5@J4Ap';

    // 子码
    private $__subcode     = '';

    // 短信前缀签名
    private $__sign        = '【鲜急送】';

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
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('message' => $message)));
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
    protected function _queue_send($mobile, $content, $sign = '')
    {
        $mobile  = implode(',', $mobile);
        $sign    = empty($sign) ? $this->__sign : $sign;
        $message = <<<EOT
            <?xml version="1.0" encoding="UTF-8"?>
            <message>
                <account>{$this->__username}</account>
                <password>{$this->__password}</password>
                <msgid></msgid>
                <phones>{$mobile}</phones>
                <content>{$content}</content>
                <sign>{$sign}</sign>
                <subcode>{$this->__subcode}</subcode>
                <sendtime></sendtime>
            </message>
EOT;

        $result = $this->__curl($message);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // 处理结果
        $response = $this->xml2array(trim($result));
        if (empty($response)) {
            $this->set_error('xml2array is error');
            return false;
        }

        $status = $response['result'];

        if ('0' === $status) {
            // 获取短信发送报告 如果没有结果应该休眠30秒（todo）
            $report = <<<EOT
                <?xml version="1.0" encoding="UTF-8"?>
                <message>
                    <account>{$this->__username}</account>
                    <password>{$this->__password}</password>
                    <msgid>{$response['msgid']}</msgid>
                    <phone></phone>
                </message>
EOT;

            $report = $this->__curl($report, $this->__report_url);
            $report = $this->xml2array(trim($report));

            $this->set_error("report:" . json_encode($report, JSON_UNESCAPED_UNICODE) . " submit:{$response['desc']} " . json_encode($response, JSON_UNESCAPED_UNICODE));
            return true;
        } else if (isset($response['desc'])) {
            $this->set_error("desc:{$response['desc']} " . json_encode($response, JSON_UNESCAPED_UNICODE));
            return false;
        } else {
            $this->set_error('发送失败：未知错误' . $result);
            return false;
        }
    }
}