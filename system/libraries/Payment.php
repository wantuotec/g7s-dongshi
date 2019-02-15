<?php
/**
 * 支付平台统一处理类
 *
 * @author      杨海波
 * @date        2015-05-09
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class CI_Payment {
    // 是否走队列
    public $is_via_queue  = true;

    // 支付平台ID
    public $payment_id    = null;

    // 支付平台名称
    public $payment_name  = null;

    // 错误信息
    protected $_error     = null;

    // 调式模式
    protected $_is_debug  = false;

    // 运行环境
    public $env = null;

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
     * @param   object  $object     要转换的对象
     * @param   int     $options    解析设置
     *
     * @return  void
     */
    protected function xml2array($xml, $options = LIBXML_NOCDATA)
    {
        return @json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', $options)), true);
    }

    /**
     * 设置环境错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_env_error($error1, $error2)
    {
        if (2 == $this->get_env()) {
            $this->_error = $error2;
        } else {
            $this->_error = $error1;
        }
    }

    /**
     * 设置环境变量
     *
     * @param   array   $params 支付信息
     *
     * @return  int
     */
    public function set_env($env = null)
    {
        // 与 project 保持一致
        if (!is_null($env)) {
            $this->env = $env;
        } else if (strstr($_SERVER['HTTP_HOST'], 'xian168.com')) {
            $this->env = 2;
        } else {
            $this->env = 1;
        }
    }

    /**
     * 获取环境变量
     *
     * @param   array   $params 支付信息
     *
     * @return  int
     */
    public function get_env()
    {
        if (is_null($this->env)) {
            $this->set_env();
        }

        return $this->env;
    }

    /**
     * 添加支付通知日志记录
     *
     * @param   array   $params 支付信息
     *
     * @return  int
     */
    public function add_notify_log(array $params = array())
    {
        $pay_log = [
            'pay_sn'        => empty($params['pay_sn'])        ? '' : $params['pay_sn'],
            'pay_outer_sn'  => empty($params['pay_outer_sn'])  ? '' : $params['pay_outer_sn'],
            'pay_status'    => empty($params['pay_status'])    ? '' : $params['pay_status'],
            'notify_amount' => empty($params['notify_amount']) ? '' : round($params['notify_amount'], 2),
            'request'       => var_export($params['request'], true),
        ];

        if (2 == $this->get_env()) {
            $this->load->model('Pay_log_model');

            $insert_id = $this->Pay_log_model->add($pay_log, false, true);
        } else {
            $service_info = [
                'service_name'   => 'log.pay_log.add_pay_log',
                'service_params' => [
                    'is_batch'     => false,
                    'is_insert_id' => true,
                    'pay_log'      => $pay_log,
                ],
            ];

            $this->load->library('requester');
            $result = $this->requester->request($service_info);

            $insert_id = empty($result['data']) ? 0 : $result['data'];
        }

        return $insert_id;
    }

    /**
     * 更新支付通知日志记录
     *
     * @param   int     $log_id     信息ID
     * @param   array   $params     支付信息
     *
     * @return  bool
     */
    public function update_notify_log_by_id($log_id = 0, array $params = array())
    {
        if (empty($log_id)) {
            return false;
        }

        if (2 == $this->get_env()) {
            // 更新处理结果
            $this->load->model('Pay_log_model');

            return $this->Pay_log_model->update_by_id($log_id, [
                'is_success' => 1,
            ]);
        } else {
            $service_info = [
                'service_name'   => 'log.pay_log.update_pay_log_by_id',
                'service_params' => [
                    'pay_log_id' => $log_id,
                    'is_success' => 1,
                ],
            ];

            $this->load->library('requester');
            $result = $this->requester->request($service_info);

            return $result['success'];
        }
    }

    /**
     * 根据 pay_sn 获取支付信息
     *
     * @param   array   $transform  转换后的订单信息
     *
     * @return  bool
     */
    public function get_by_pay_sn($pay_sn, $fields = null)
    {
        $fields = is_null($fields) ? 'pay_outer_sn, app_type, amount, is_finish' : $fields;
        if (2 == $this->get_env()) {
            $this->load->model('Pay_model');
            $exist = $this->Pay_model->get_by_sn($pay_sn, $fields);
        } else {
            $service_info = [
                'service_name'   => 'pay.pay.get_pay_by_sn',
                'service_params' => [
                    'pay_sn' => $pay_sn,
                    'fields' => $fields,
                ],
            ];

            $this->load->library('requester');
            $result = $this->requester->request($service_info);

            $exist = empty($result['data']) ? array() : $result['data'];
        }

        return $exist;
    }

    /**
     * 支付通知成功后的操作
     *
     * @param   array   $transform  转换后的订单信息
     *
     * @return  bool
     */
    public function notify_success(array $transform = array())
    {
        // 有时候会多次发送同一状态，2015-10-08 add by mark
        if (!empty($transform['pay_sn'])) {

            $exist = $this->get_by_pay_sn($transform['pay_sn']);

            // 如果没有这条信息，那么说明是有问题的。
            if (empty($exist) || !is_array($exist)) {
                return false;
            } else {
                // 如果已经完成了，不执行我方业务处理流程
                if (1 == $exist['is_finish']) {
                    return true;
                } else {
                    $params = array(
                        'pay_sn'        => $transform['pay_sn'],
                        'pay_outer_sn'  => $transform['pay_outer_sn'],
                        'outer_user_id' => $transform['outer_user_id'],
                        'notify_amount' => (isset($transform['total_fee']) && 0 !== bccomp($exist['amount'], $transform['total_fee'], 2)) ? $transform['total_fee'] : $exist['amount'],
                    );

                    if (2 == $this->get_env()) {
                        $this->load->model('Pay_model');
                        // 充值金额以支付宝的为准
                        return $this->Pay_model->finish($params);
                    } else {
                        $service_info = [
                            'service_name'   => 'pay.pay.finish',
                            'service_params' => $params,
                        ];

                        $this->load->library('requester');
                        $result = $this->requester->request($service_info);

                        return $result['success'];
                    }
                }
            }
        }

        return false;
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
        // 自行改写
        return $params;
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
        // 缓存支付平台ID
        $this->payment_id = intval($code);

        // // 如果是标准接口
        // $this->load->model('Payment_model');
        // $result = $this->Payment_model->get_by_id($this->payment_id, 'payment_name, is_enabled, payment_code, is_debug');

        // // 缓存支付平台名称
        // $this->payment_name = $result['payment_name'];

        // // 如果是无效状态
        // if (1 != $result['is_enabled']) {
        //     $this->set_error('The payment was disabled');
        //     return false;
        // }

        // // 比如是标准或者其它指定类型
        // if (!empty($result['payment_code'])) {
        //     $code = $result['payment_code'];
        // }

        $name = 'Payment';
        $code = $name . '_' . $code;
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $code . '.php';
        if (file_exists($class)) {
            // 修复 CLI 里一个进程下自动 factory 多个文件后，因 $this->load->library 有同名缓存而导致的后续文件直接使用前文件的类及变量的 BUG add by willy 2014-03-18
            $this->load->library($name . '/' . $code, null, $code);
            $this->factory = $this->$code;
            unset($this->$code);

            // 传递数据到子类
            $this->factory->_is_debug     = 1 == $result['is_debug'] ? true : false; // 是否调试模式
            $this->factory->payment_id   = $this->payment_id;
            $this->factory->payment_name = $this->payment_name;

            return $this->factory;
        }

        $this->set_error('configure is not exists');
        return false;
    }

    /**
     * 白名单验证
     *
     * 如果是直接访问，且路由为这样的形式：payment/alipay/pay，需要进行白名单校验
     * 
     * @param  array $white_list 白名单列表
     * 
     * @return void
     */
    public function check_white_list($white_list)
    {
        $CI = & get_instance();
        $rsegment_array = $CI->uri->rsegment_array();
        if (3 == count($rsegment_array)) {
            if (!in_array($rsegment_array[3], $white_list)) {
                echo '不在白名单内！';
                exit;
            }
        }
    }
}