<?php
/**
 * 亿美短信发送
 *
 * @author      willy
 * @date        2014-10-10
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR . 'Emay/include') . DIRECTORY_SEPARATOR . 'Client.php';

class CI_Sms_2 extends CI_Sms {
    // 同一条短信最多可以发给多少个手机号
    public $max_number = 200;

    // 提交地址
    private $__request_url = 'http://sdk4report.eucp.b2m.cn:8080/sdk/SDKService';
    // 用户名
    private $__username    = '0SDK-EBB-6688-JFVML'; // 序列号,请通过亿美销售人员获取
    // 密码
    private $__password    = '006302';
    // session key
    private $__session_key = '123456';
    // 连接超时秒数
    private $__timeout     = 5;

    // 状态列表
    private $__status_list = array(
        '0'     => '成功',
        '-1'    => '系统异常',
        '-2'    => '客户端异常',
        '-101'  => '命令不被支持',
        '-102'  => 'RegistryTransInfo删除信息失败',
        '-103'  => 'RegistryInfo更新信息失败',
        '-104'  => '请求超过限制',
        '-110'  => '号码注册激活失败',
        '-111'  => '企业注册失败',
        '-113'  => '充值失败',
        '-117'  => '发送短信失败',
        '-118'  => '接收MO失败',
        '-119'  => '接收Report失败',
        '-120'  => '修改密码失败',
        '-122'  => '号码注销激活失败',
        '-123'  => '查询单价失败',
        '-124'  => '查询余额失败',
        '-125'  => '设置MO转发失败',
        '-126'  => '路由信息失败',
        '-127'  => '计费失败0余额',
        '-128'  => '计费失败余额不足',
        '-190'  => '数据操作失败',
        '-1100' => '序列号错误,序列号不存在内存中,或尝试攻击的用户',
        '-1102' => '序列号密码错误',
        '-1103' => '序列号Key错误',
        '-1104' => '路由失败，请联系系统管理员',
        '-1105' => '注册号状态异常, 未用 1',
        '-1107' => '注册号状态异常, 停用 3',
        '-1108' => '注册号状态异常, 停止 5',
        '-1131' => '充值卡无效',
        '-1132' => '充值密码无效',
        '-1133' => '充值卡绑定异常',
        '-1134' => '充值状态无效',
        '-1135' => '充值金额无效',
        '-1901' => '数据库插入操作失败',
        '-1902' => '数据库更新操作失败',
        '-1903' => '数据库删除操作失败',
        '-9000' => '数据格式错误,数据超出数据库允许范围',
        '-9001' => '序列号格式错误',
        '-9002' => '密码格式错误',
        '-9003' => '客户端Key格式错误',
        '-9004' => '设置转发格式错误',
        '-9005' => '公司地址格式错误',
        '-9006' => '企业中文名格式错误',
        '-9007' => '企业中文名简称格式错误',
        '-9008' => '邮件地址格式错误',
        '-9009' => '企业英文名格式错误',
        '-9010' => '企业英文名简称格式错误',
        '-9011' => '传真格式错误',
        '-9012' => '联系人格式错误',
        '-9013' => '联系电话',
        '-9014' => '邮编格式错误',
        '-9015' => '新密码格式错误',
        '-9016' => '发送短信包大小超出范围',
        '-9017' => '发送短信内容格式错误',
        '-9018' => '发送短信扩展号格式错误',
        '-9019' => '发送短信优先级格式错误',
        '-9020' => '发送短信手机号格式错误',
        '-9021' => '发送短信定时时间格式错误',
        '-9022' => '发送短信唯一序列值错误',
        '-9023' => '充值卡号格式错误',
        '-9024' => '充值密码格式错误',
        '-9025' => '客户端请求sdk5超时',
    );

    /**
     * 构造函数
     *
     * @return  void
     */
    public function __construct()
    {
        $this->_client = new Client($this->__request_url, $this->__username, $this->__password, $this->__session_key, false, false, false, false, $this->__timeout, $this->__timeout);
        $this->_client->setOutgoingEncoding("UTF-8");
    }

    /**
     * 发送短信 （队列里调用）
     *
     * @access  public
     *
     * @param   string  $mobile     手机号
     * @param   string  $content    短信内容
     *
     * @return  void
     */
    protected function _queue_send($mobile, $content)
    {
        // sendSMS($mobiles=array(),$content,$sendTime='',$addSerial='',$charset='GBK',$priority=5,$smsId=8888)
        $status = $this->_client->sendSMS($mobile, $content, '', '');

        if ('0' === $status) {
            if(isset($this->__status_list[$status])) {
                $this->set_error($this->__status_list[$status]);
            }
            return true;
        } else if (isset($this->__status_list[$status])) {
            $this->set_error($this->__status_list[$status]);
            return false;
        } else {
            $this->set_error('发送失败：未知错误');
            return false;
        }
    }
}