<?php
/**
 * Created by PhpStorm.
 * User: wangyuanlei
 * Date: 2014/10/10
 * Time: 14:05
 */
class CI_Xinge{

    private $access_id;
    private $access_key;
    private $secret_key;
    private $XingeApp   = null;
    public  $deviceType = 0;

    public function __construct()
    {
        $CI =& get_instance();
        if ($CI->config->load('xinge')) {
            $this->access_id    = $CI->config->config['xg_access_id'];
            $this->access_key   = $CI->config->config['xg_access_key'];
            $this->secret_key   = $CI->config->config['xg_secret_key'];
        }

        $this->__xinge_path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                . 'include'. DIRECTORY_SEPARATOR . 'xinge') . DIRECTORY_SEPARATOR;
        $class = $this->__xinge_path . 'XingeApp.php';

        if (file_exists($class)) {
            include_once $class;
            $this->XingeApp = new XingeApp( $this->access_id, $this->secret_key);
            return true;
        }

        $this->set_error('The include file is not exists');
        return false;
    }

    public function setDeviceType($type){
        switch($type) {
            case 0 :    //不限
                $this->deviceType = XingeApp::DEVICE_ALL;
                break;
            case 1 :    //浏览器
                $this->deviceType = XingeApp::DEVICE_BROWSER;
                break;
            case 2 :    //pc
                $this->deviceType = XingeApp::DEVICE_PC;
                break;
            case 3 :    //安卓
                $this->deviceType = XingeApp::DEVICE_ANDROID;
                break;
            case 4 :    //ios
                $this->deviceType = XingeApp::DEVICE_IOS;
                break;
            case 5 :    //Windows phone
                $this->deviceType = XingeApp::DEVICE_WINPHONE;
                break;
            default:    //未知
                $this->set_error('deviceType error');
                return false;
        }
        return true;
    }

    public function setMessage(){

    }

    //单个设备下发透传消息
    public function PushSingleDeviceMessage($title, $content, $token)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_MESSAGE);
        $ret = $this->XingeApp->PushSingleDevice($token, $mess);
        return $ret;
    }

    //推送消息给单个账户或别名
    public function PushSingleAccountMessage($title, $content, $token)
    {
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_MESSAGE);
        $ret = $this->XingeApp->PushSingleAccount($deviceType, $account, $message, $environment=0);
        return $ret;
    }

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
}