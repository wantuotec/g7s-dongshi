<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 微信公众号 API入口
 *
 * @author       madesheng
 * @date         2018-01-26
 * @category     Weixin
 * @copyright    Copyright(c) 2014
 * @version      $Id$
 */
class Weixin extends CI_Controller
{
    private $_wx_token = "";  // 微信token
	private $_wx_msg   = [];  // 微信传来的消息

	/**
     * 构造函数
     */
	public function __construct() {
        parent::__construct();
	}

    /**
     * 访问入口方法
     */
    public function index()
    {
        //初始化微信Token
        $this->_wx_token = WX_TOKEN;
        //处理微信请求
        $this->doRequest();
    }

	/**
     * 微信签名验证/响应用户消息
     */
    private function doRequest()
    {
    	//获取请求中的随机字符串
    	$echostr = $_GET['echostr'];

    	// 如果有随机字符串，则走签名验证
    	if (!empty($echostr)) {
			//验证通过，返回原随机字符串
			if ($this->checkSignature()) {
				echo $echostr;
				exit;
			}

    	//没有随机字符串，走消息响应业务
    	} else {
    		$this->responseUserMsg();
    	}
    }

    /**
     * 执行签名验证
     */
    private function checkSignature()
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce     = $_GET['nonce'];
        $token     = $this->_wx_token;

        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature) {
        	return true;
        } else {
        	return false;
        }
    }

    /**
     * 响应用户消息
     */
    public function responseUserMsg()
    {
    	// 接收微服POST过来的数据
    	$msgXml = file_get_contents('php://input');

    	if (!empty($msgXml)) {
    		//xml转array
    		$this->_wx_msg = xml2array($msgXml);
            //回复的内容
            $resCont = "";
            
    		//根据消息类型进行响应
    		switch ($this->_wx_msg['MsgType']) {
    			case 'text':
    				$resCont = $this->responseText();
    				break;
    			case 'image':
    				$resCont = $this->responseImage();
    				break;
    			case 'voice':
    				$resCont = $this->responseVoice();
    				break;
    			case 'video':
    				$resCont = $this->responseVideo();
    				break;
    			case 'music':
    				$resCont = $this->responseMusic();
    				break;
    			case 'news':
    				$resCont = $this->responseNews();
    				break;
    			default:
    				echo "";
    		}
            // add_txt_log('Dreamma回复的内容为：' . $resCont, 'returnCont');
    		//输出回复内容
    		echo $resCont;

    	} else {
    		echo "";
    		exit;
    	}
    }

    /**
     * 回复Text类型消息
     */
    public function responseText()
    {
        // $this->add_log_wx_msg();
        // 当前时间戳
        $timestamp = time();

        $testTpl  = "<xml>
            <ToUserName><![CDATA[{$this->_wx_msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->_wx_msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$timestamp}</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[欢迎来到Dreamma的订阅号！您发送的内容为：【%s】，常来逛逛啊...]]></Content>
            </xml>";

        return sprintf($testTpl, $this->_wx_msg['Content']);
    }

    /**
     * 回复Image类型消息
     */
    public function responseImage()
    {

    }

    /**
     * 回复Voice类型消息
     */
    public function responseVoice()
    {

    }

    /**
     * 回复Video类型消息
     */
    public function responseVideo()
    {

    }

    /**
     * 回复Music类型消息
     */
    public function responseMusic()
    {

    }

    /**
     * 回复News类型消息
     */
    public function responseNews()
    {

    }

    // 本地调试，记录微信传来的xml解析后数据
    public function add_log_wx_msg()
    {
        $msgArr = $this->_wx_msg;
        if (is_array($msgArr) && !empty($msgArr)) {
            add_txt_log('======解析'. $msgArr['MsgType'] .'数据=======', 'postXml');
            $msgStr = '';
            foreach ($msgArr as $key => $val) {
                $msgStr .= $key . '=>' . $val . '||';
            }
            add_txt_log($msgStr, 'postXml');
        }
    }
}
