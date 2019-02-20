<?php
 /**
 * 极光推送
 *
 * @author      熊飞龙
 * @date        2015-05-29
 * @category    Jpush
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
require_once(dirname(__FILE__) . '/jpush/vendor/autoload.php');

use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

class MY_Jpush_push
{
    /**
     * 初始化
     *
     * @param   array   $params
     *
     * @rerutn  void
     */
    public function initialize(array $params = array())
    {
        if (empty($params['app_key']) || empty($params['master_secret'])) {
            $this->set_error("推送应用配置信息不完整！");
            return false;
        }

        $this->app_key         = $params['app_key'];
        $this->master_secret   = $params['master_secret'];
        $this->expire_time     = $params['expire_time'];
        $this->apns_production = $params['apns_production'];

        return true;
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
     * 推送透传消息 - 单个设备
     *
     * @param   mixed   $cid            接收的设备
     * @param   string  $push_data      推送的内容
     *
     * @return  bool
     */
    public function push_transmission($cid = '', $push_data = '')
    {
        $params = array(
            'cid'       => $cid,
            'push_data' => $push_data,
            'push_type' => 2,
        );

        return $this->__push($params, 2);
    }

    /**
     * 推送通知 - 点击打开应用
     *
     * @param   mixed   $cid            接收的设备
     * @param   string  $title          通知标题
     * @param   string  $message        通知内容
     * @param   string  $push_data      推送的内容
     *
     * @return  bool
     */
    public function push_open_message($cid,  $title = '', $content = '', $push_data = '')
    {
        $params = array(
            'cid'       => $cid,
            'push_data' => $push_data,
            'title'     => $title,
            'content'   => $content,
            'push_type' => 1,
        );

        return $this->__push($params, 1);
    }

    /**
     * 底层推送
     *
     * @param   mixed   $cid            接收的设备
     * @param   string  $title          通知标题
     * @param   string  $content        通知内容
     * @param   string  $instruction    透传指令
     *
     * @return  bool
     */
    private function __push($params, $push_type)
    {
        $client = new JPushClient($this->app_key, $this->master_secret);

        try {
            $sdk = $client->push();

            // 之前的处理已经把 cid 处理成数组了。如果只有一条数据，剥离出来
            if (!empty($params['cid']) && is_array($params['cid']) && 1 == count($params['cid'])) {
                $params['cid'] = array_shift($params['cid']);
            }

            // 设置不同的平台
            if ('all' === $params['cid']) {
                $sdk->setPlatform(M\all);
            } else if ('android' === $params['cid']) {
                $sdk->setPlatform(M\platform('android'));
            } else if ('ios' === $params['cid']) {
                $sdk->setPlatform(M\platform('ios'));
            } else if ('winphone' === $params['cid']) {
                $sdk->setPlatform(M\platform('winphone'));
            } else {
                $sdk->setPlatform(M\all);
            }


            // 全部设备群推
            if ('all' === $params['cid'] || in_array($params['cid'], array('android', 'ios', 'winphone'))) {
                $sdk->setAudience(M\all);
            } elseif (is_array($params['cid'])) {
                $sdk->setAudience(M\audience(M\registration_id($params['cid'])));
            } elseif (is_string($params['cid'])) {
                $sdk->setAudience(M\audience(M\registration_id(array($params['cid']))));
            } else {
                $this->set_error("CID 为未知的数据类型！");
                return false;
            }

            // 1：通知+透传，2：透传
            if (1 == $params['push_type']) {
                // 检测当前payload是否超出ios notification长度限定。返回true/false。（ios notification不超过220并且ios notification + message不超过1200）
                // function: JPush/Model/ios($alert, $sound=null, $badge=null, $contentAvailable=null, $extras=null)
                // function: JPush/Model/android($alert, $title=null, $builder_id=null, $extras=null, $category=null)
                // function: JPush/Model/winphone($alert, $title=null, $_open_page=null, $extras=null)

                $alert_tmp = $params['content'];
                $title_tmp = $params['title'];
                $extra_tmp = array('push_data' => $params['push_data']);

                $sdk->setNotification(M\notification($alert_tmp, M\android($alert_tmp, $title_tmp, null, $extra_tmp), M\ios($alert_tmp, 'happy', 1, true, $extra_tmp, 'THE-CATEGORY'), M\winphone($alert_tmp, $title_tmp, null, $extra_tmp)));
                unset($alert_tmp, $title_tmp, $extra_tmp);
            } else if (2 == $params['push_type']) {
                // 不管是通知还是透传都是需要执行setMessage
                // JPush/Model/message($msg_content, $title=null, $content_type=null, $extras=null)
                $sdk->setMessage(M\message($params['push_data'], null, null, null));
            }

            // options($sendno=null, $time_to_live=null, $override_msg_id=null, $apns_production=null, $big_push_duration=null)
            $sdk->setOptions(M\options(null, $this->expire_time, null, $this->apns_production, 0));

            // 推送消息是否超过一定的 ios notification + message不超过1200）
            // android total length of message and notification should be less than 1000 bytes
            $global_length = $sdk->getGlobalLength();
            if (true === $sdk->isGlobalExceedLength()) {
                $this->set_error("推送信息过长：" . $global_length);
                return false;
            }

            $result = $sdk->send();

            // $br = "<br />";
            // echo 'Push Success.' . $br;
            // echo 'sendno : ' . $result->sendno . $br;
            // echo 'msg_id : ' .$result->msg_id . $br;
            // echo 'Response JSON : ' . $result->json . $br;

            $response = array(
                'isOk'   => $result->isOk,
                'sendno' => $result->sendno,
                'msg_id' => $result->msg_id,
                'length' => $global_length,
            );

        } catch (APIRequestException $e) {

            // echo 'Push Fail.' . $br;
            // echo 'Http Code : ' . $e->httpCode . $br;
            // echo 'code : ' . $e->code . $br;
            // echo 'message : ' . $e->message . $br;
            // echo 'Response JSON : ' . $e->json . $br;
            // echo 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
            // echo 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
            // echo 'rateLimitReset : ' . $e->rateLimitReset . $br;

            // rateLimitLimit 现在变成 1200 了 一分钟 1200 次 add by mark 2015-08-08
            $response = array(
                'isOk'     => false,
                'HttpCode' => $e->httpCode,
                'code'     => $e->code,
                'message'  => $e->message,
                'rateLimitLimit'     => $e->rateLimitLimit,
                'rateLimitRemaining' => $e->rateLimitRemaining,
                'rateLimitReset'     => $e->rateLimitReset,
            );
        } catch (APIConnectionException $e) {
            // echo 'Push Fail: ' . $br;
            // echo 'Error Message: ' . $e->getMessage() . $br;
            // //response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
            // echo 'IsResponseTimeout: ' . $e->isResponseTimeout . $br;

            $response = array(
                'isOk'              => false,
                'errorMessage'      => $e->getMessage(),
                'IsResponseTimeout' => $e->isResponseTimeout,
            );
        } catch (Exception $e) {
            $response = array(
                'isOk'         => false,
                'code'         => $e->getCode(),
                'errorMessage' => $e->getMessage(),
            );
        }

        if (false === $response['isOk']) {
            $this->set_error(json_encode($response));
            return false;
        }

        return json_encode($response);
    }
}