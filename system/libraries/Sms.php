<?php
/**
 * 短信发送类
 *
 * @author      willy
 * @date        2014-10-09
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
class CI_Sms {
    // 是否走队列
    public $is_via_queue     = true;

    // 错误信息
    protected $_error        = null;

    // 是否是语音短信
    protected $_is_voice_sms = false;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI =& get_instance();
        return $CI->$key;
    }

    /**
     * 把xml转换成数组
     *
     * @param   object  $object 要转换的对象
     *
     * @return  void
     */
    protected function xml2array($xml)
    {
        return @json_decode(json_encode(simplexml_load_string($xml)), true);
    }

    /**
     * 是否是语音短信
     *
     * @param   object  $object 要转换的对象
     *
     * @return  void
     */
    public function is_voice_sms($is_voice_sms = true)
    {
        $this->_is_voice_sms = (bool) $is_voice_sms;
    }

    /**
     * 工厂方法
     *
     * @access  public
     * @param   string  $code   代号
     *
     * @return  bool
     */
    public function factory($code)
    {
        $code = 'Sms_' . $code;
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Sms' . DIRECTORY_SEPARATOR . $code . '.php';
        if (file_exists($class)) {
            // 修复 CLI 里一个进程下自动 factory 多个文件后，因 $this->load->library 有同名缓存而导致的后续文件直接使用前文件的类及变量的 BUG add by willy 2014-03-18
            $this->load->library('Sms/' . $code, null, $code);
            $this->sms_factory = $this->$code;
            unset($this->$code);

            return $this->sms_factory;
        }

        $this->set_error('Sms configure is not exists');
        return false;
    }

    /**
     * 发送短信 （队列里调用）
     *
     * @access  public
     *
     * @param   mixed   $mobile     手机号
     * @param   string  $content    短信内容
     * @param   string  $sign       签名
     *
     * @return  void
     */
    public function queue_send($mobile, $content, $sign = '')
    {
        $mobile = is_array($mobile) ? $mobile : array($mobile);
        $number = count($mobile);

        if ($number < 1) {
            $this->set_error('至少应该有一个号码');
            return false;
        }

        if ($number > $this->max_number) {
            $this->set_error('一次最多' . $this->max_number . '个手机号码');
            return false;
        }

        if (!is_callable(array($this, '_queue_send'))) {
            $this->set_error('_queue_send() function is not defined');
            return false;
        }

        return $this->_queue_send($mobile, $content, $sign);
    }

    /**
     * 发送短信 （先发送到队列）
     *
     * @access  public
     *
     * @param   mixed   $mobile         手机号(可以是字符串或者数组)
     * @param   string  $content        短信内容
     * @param   bool    $is_marketing   是否是营销短信
     * @param   string  $sign           签名
     *
     * @return  void
     */
    public function send($mobile, $content, $is_marketing = false, $sign = '')
    {
        $mobile = is_array($mobile) ? $mobile : array($mobile);

        $i = 0;
        $j = 0;

        $mobiles = array();
        $length  = count($mobile);

        // 如果是营销短信
        if ($is_marketing) {
            $provider = 1; // 等亿美上线，改成其它
        } else {
            $provider = 1; // 验证码、行业类短信
            // 如果是语音短信
            if (true === $this->_is_voice_sms) {
                $provider = 3;
            }
        }

        // 默认短信供应商为第一家
        empty($provider) && $provider = 1;

        // factory 到指定的供应商
        $this->load->library('sms');
        $obj = $this->sms->factory($provider);
        if (false === $obj) {
            $this->set_error('配置文件丢失');
            return false;
        } else {
            $max_number = $obj->max_number;
            if (empty($max_number)) {
                $this->set_error('请设置批量发送时的最大数量');
                return false;
            }
        }



        // 处理大批量的手机号
        foreach ($mobile as $v) {
            $mobiles[] = $v;
            $i++;
            $j++;

            // 到达每个批次的最大数，或者手机号遍历完毕
            if ($i == $max_number || $j == $length) {
                $params = array(
                    'mobile'   => $mobiles,
                    'content'  => $content,
                    'extra_1'  => $mobiles,
                    // 'extra_2'  => $content . $this->__surfix,
                    'extra_2'  => $content,
                    // 短信供应商 1 大汉三通 2 亿美营销通道 3 亿美非营销通道
                    'provider' => $provider,
                    'sign'     => $sign,
                );

                // 是否走队列
                if (true === $this->is_via_queue) {
                    $this->load->library('queue');
                    $result = $this->queue->send('sms', $params);
                } else {
                    $result = $obj->queue_send($mobiles, $content, $sign);
                }

                // 重置
                $i       = 0;
                $mobiles = array();
            }
        }

        if (false === $result) {
            $this->set_error(true === $this->is_via_queue ? $this->queue->get_error() : $this->get_error());
            return false;
        } else {
            return true;
        }
    }
}