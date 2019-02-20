<?php
/**
 * 邮件发送类
 *
 * @author      willy
 * @date        2012-07-04
 * @copyright   Copyright(c) 2012 
 * @version     $Id: Mail.php 613 2013-08-12 09:26:16Z 杨海波 $
 */
class CI_Mail {
    // 是否走队列
    public $is_via_queue = true;

    // 错误信息
    private $__error     = null;

    // 邮件配置信息
    private $__config    = null;

    // 必须有的配置信息
    private $__necessary = array('host', 'port', 'from_email', 'from_name', 'username', 'password', 'charset');

    // phpmailer 路径
    private $__phpmailer_path = null;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->__error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->__error;
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
     * 构造函数
     *
     * @access  public
     * @param   string  $config 邮件配置
     *
     * @return  bool
     */
    public function __construct($config = array())
    {
        if (empty($config) || !is_array($config)) { // 如果没有定义 从配置文件获取
            $CI =& get_instance();
            if ($CI->config->load('phpmailer', TRUE, TRUE)) {
                if (is_array($CI->config->config['phpmailer'])) {
                    $this->__config = $CI->config->config['phpmailer'];         
                }            
            }
        } else {
            $this->__config = $config;
        }

        foreach ($this->__necessary as $need) {
            if (empty($this->__config[$need])) {
                $this->set_error('The ' . $need . ' is necessary');
                return false;
            }
        }

        $this->__phpmailer_path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                                . 'include'. DIRECTORY_SEPARATOR . 'phpmailer') . DIRECTORY_SEPARATOR;
        $class = $this->__phpmailer_path . 'class.phpmailer.php';
        if (file_exists($class)) {
            include_once $class;
            return true;
        }

        $this->set_error('The include file is not exists');
        return false;
    }

    /**
     * 检查一个变量是否是邮箱
     *
     * @param   mixed   $params 待检测的字符串
     *
     * @return  bool
     */
    public function is_email($params)
    {
        return (bool) preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i', $params);
    }

    /**
     * 发邮件
     *
     * @access  public
     *
     * @param   mixed   $email      电子邮箱
     * @param   string  $subject    邮件主题
     * @param   string  $content    邮件内容
     * @param   mixed   $attach     邮件附件
     *
     * @return  bool
     */
    private function __send($email, $subject, $content, $attach = null)
    {
        $email = is_array($email) ? $email : array($email);

        foreach ($email as $val) {
            if (!$this->is_email($val)) {
                $this->set_error('邮箱格式不正确');
                return false;
            }
        }

        $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

        $mail->IsSMTP(); // telling the class to use SMTP

        try {
            $mail->SMTPDebug  = false;
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = "ssl";
            $mail->Host       = $this->__config['host'];
            $mail->Port       = $this->__config['port'];
            $mail->Username   = $this->__config['username'];
            $mail->Password   = $this->__config['password'];
            
            $mail->set('CharSet', $this->__config['charset']);
            $mail->SetFrom($this->__config['from_email'], $this->__config['from_name']);
            // $mail->AddReplyTo('name@yourdomain.com', 'First Last');

            foreach ($email as $val) {
                $mail->AddAddress($val, '');
            }

            // $mail->AddReplyTo('name@yourdomain.com', 'First Last');
            $mail->Subject = $subject;
            // $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
            $mail->MsgHTML($content);
            if (!empty($attach)) {
                $attach = is_array($attach) ? $attach : array($attach);
                foreach ($attach as $key => $val) {
                    if (file_exists($val)) {
                        $mail->AddAttachment($val);
                    }
                }
            }
            // $mail->AddAttachment('images/phpmailer.gif');      // attachment
            // $mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
            return $mail->Send();
        } catch (phpmailerException $e) {
            $this->set_error($e->errorMessage()); //Pretty error messages from PHPMailer
            return false;
        } catch (Exception $e) {
            $this->set_error($e->getMessage());
            return false;
        }
    }

    /**
     * 发邮件 （队列里调用）
     *
     * @access  public
     *
     * @param   mixed   $email      电子邮箱
     * @param   string  $subject    邮件主题
     * @param   string  $content    邮件内容 如果使用模板，这里应该为数组
     * @param   string  $tpl_name   模板名称
     * @param   mixed   $attach     邮件附件
     *
     * @return  bool
     */
    public function queue_send($email, $subject, $content, $tpl_name = null, $attach = null)
    {
        if (!empty($tpl_name)) {
            if (empty($content) || !is_array($content)) {
                $this->set_error('使用模板时第三参数必须为数组');
                return false;
            }

            $tpl = $this->__phpmailer_path . 'tpl'. DIRECTORY_SEPARATOR . $tpl_name . '.tpl';
            if (!file_exists($tpl)) {
                $this->set_error('邮件模板不存在');
                return false;
            }

            $tpl_content = file_get_contents($tpl);
            foreach ($content as $key => $val) {
                $content['{{$' . $key . '}}'] = $val;
                unset($content[$key]);
            }

            $content = str_replace(array_keys($content), array_values($content), $tpl_content);
        }

        $result = $this->__send($email, $subject, $content, $attach);
        return $result;
    }

    /**
     * 发邮件 （先发送到队列）
     *
     * @access  public
     *
     * @param   mixed   $email      电子邮箱
     * @param   string  $subject    邮件主题
     * @param   string  $content    邮件内容 如果使用模板，这里应该为数组
     * @param   bool    $tpl_name   模板名称
     *
     * @return  bool
     */
    public function send($email, $subject, $content, $tpl_name = null)
    {
        if (!empty($tpl_name)) {
            if (empty($content) || !is_array($content)) {
                $this->set_error('使用模板时第三参数必须为数组');
                return false;
            }

            $tpl = $this->__phpmailer_path . 'tpl'. DIRECTORY_SEPARATOR . $tpl_name . '.tpl';
            if (!file_exists($tpl)) {
                $this->set_error('邮件模板不存在');
                return false;
            }
        }

        $params = array(
            'email'    => $email,
            'subject'  => $subject,
            'content'  => $content,
            'tpl_name' => $tpl_name,
            'config'   => $this->__config,
            'extra_1'  => $email,
            'extra_2'  => $subject,
        );

        // 是否走队列
        if (true === $this->is_via_queue) {
            $this->load->library('queue');
            $result = $this->queue->send('email', $params);
        } else { // 直接发邮件
            $result = $this->queue_send($email, $subject, $content, $tpl_name);
        }

        if (false === $result) {
            $this->set_error(true === $this->is_via_queue ? $this->queue->get_error() : $this->get_error());
            return false;
        } else {
            return true;
        }
    }
}