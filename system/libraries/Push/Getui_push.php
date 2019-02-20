<?php
 /**
 *
 *
 * @author      熊飞龙
 * @date        2014-12-22
 * @category    Getui.php
 * @copyright   Copyright(c) 2014
 * @version     $Id$
 */
require_once(dirname(__FILE__) . '/getui/IGt.Push.php');
class MY_Getui_push extends IGeTui
{
    // 应用ID
    private $__app_id = "";

    // 应用名称
    private $__app_name = "";

    // 官方自带的，勿动
    private $__host = "http://sdk.open.api.igexin.com/apiex.htm";

    // 离线消息保存时间单位(毫秒)  例:24个小时离线为3600*1000*24
    private $__offline_expire_time = 600;

    // 记录错误消息
    private $__error = "";

    // 配置信息
    private $__config = array(
        'logo'         => '',	  // 通知消息的logo (默认 个推官方图标)
        'is_ring'  	   => true,	  // 是否震动 (默认 true)
        'is_vibrate'   => true,	  // 是否响铃 (默认 true)
        'is_clearable' => true,	  // 通知栏是否可清除 (默认 true)

        // 弹窗模板 【确认下载前的样式】
        'pop'  => array(
            'popTitle'   => '软件更新', // 弹框标题
            'popContent' => '',         // 弹框内容
            'popImage'   => '',         // 弹框图片 (弹框内容会显示在弹框图片之上)
            'popButton1' => '下载',     // 左键 按键
            'popButton2' => '取消',     // 右键 按键
        ),

        // 配置下载模版【在下载过程中的样式】
        'download' => array(
            'loadIcon'      => '',      // 下载图标
            'loadTitle'     => '',      // 下载标题
            'loadUrl'       => '',      // 下载地址
            'isAutoInstall' => false,   // 是否自动安装（默认 false）
            'isActived'     => false,   // 安装完成后是否自动启动应用程序（默认 false）
        ),
    );

    public function __construct()
    {

    }

    /**
     * 初始化
     *
     * @param   array   $params
     *
     * @rerutn  void
     */
    public function initialize(array $params = array())
    {
        // 设置通知样式
        if (isset($params['logo'])) {
            $this->__config['logo'] = $params['logo'];
        }

        if (isset($params['is_ring'])) {
            $this->__config['is_ring'] = (bool) $params['is_ring'];
        }

        if (isset($params['is_vibrate'])) {
            $this->__config['is_ring'] = (bool) $params['is_vibrate'];
        }

        if (isset($params['is_clearable'])) {
            $this->__config['is_ring'] = (bool) $params['is_clearable'];
        }

        // 设置下载的样式
        if (!empty($params['pop'])) {
            $this->__config['pop'] = array_merge($this->__config['pop'], $params['pop']);
        }

        // 下载的样式
        if (!empty($params['download'])) {
            $this->__config['download'] = array_merge($this->__config['download'], $params['download']);
        }

        if (empty($params['appid']) || empty($params['appname']) || empty($params['appkey']) || empty($params['masterSecret'])) {
            $this->set_error("个推应用配置信息不完整！");
            return false;
        }

        $this->host         = $this->__host;
        $this->__app_id     = $params['appid'];
        $this->__app_name   = $params['appname'];
        $this->appkey       = $params['appkey'];
        $this->masterSecret = $params['masterSecret'];

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
    * 处理返回结果集
    */
    private function __result($params = array())
    {
        if (is_array($params)) {

            if (isset($params['result'])) {

                if ($params['result'] != 'ok') {
                    $this->set_error($params['result']);
                    return false;
                }

            } else {

                foreach($params as $key => $val) {
                    if ($val['result'] != 'ok') {
                        $this->set_error($val['result']);
                        return false;
                    }
                }
            }

        } else {
            // 请求前出错了。
            $this->set_error($params);
            return false;
        }

        return json_encode($params);
    }

    /**
     * 推送透传消息 - 单个设备
     *
     * @param   mixed   $cid            接收的设备
     * @param   string  $content        通知内容
     *
     * @return  bool
     */
    public function push_transmission($cid = '', $content = '')
    {
        // 消息模版
        $template = $this->transmission_template($content);

        // 群推
        if($cid === 'all') {
            $result = $this->__push_message_all($template);

            // 多用户
        } else if (is_array($cid)) {
            $result = $this->__push_message_list($template, $cid);

            // 单用户
        } else if(is_string($cid)) {
            $result = $this->__push_message($template, $cid);

        } else {
            $this->set_error("CID 为未知的数据类型！");
            return false;
        }

        return $this->__result($result);
    }

    /**
     * 推送通知 - 点击打开应用
     *
     * @param   mixed   $cid            接收的设备
     * @param   string  $title          通知标题
     * @param   string  $content        通知内容
     * @param   string  $instruction    透传指令
     *
     * @return  bool
     */
    public function push_open_message($cid,  $title = '', $content = '', $instruction = '')
    {
        $template = $this->notification_template($title, $content, $instruction);

        // 群推
        if($cid === 'all') {
            $result = $this->__push_message_all($template);

            // 多用户
        } else if (is_array($cid)) {
            $result = $this->__push_message_list($template, $cid);

            // 单用户
        } else if(is_string($cid)) {
            $result = $this->__push_message($template, $cid);

        } else {
            $this->set_error("CID 为未知的数据类型！");
            return false;
        }

        return $this->__result($result);
    }

    /**
     * 推送通知 - 点击打开网页
     *
     * @param   mixed   $cid
     * @param   string  $title
     * @param   string  $content
     * @param   string  $url
     *
     * @return  array|bool
     */
    public function push_link_message($cid, $title = '', $content = '', $url = '')
    {
        $template = $this->link_template($title, $content, $url);

        // 群推
        if($cid === 'all') {
            $result = $this->__push_message_all($template);

        // 多用户
        } else if (is_array($cid)) {
            $result = $this->__push_message_list($template, $cid);

        // 单用户
        } else if(is_string($cid)) {
            $result = $this->__push_message($template, $cid);

        } else {
            $this->set_error("CID 为未知的数据类型！");
            return false;
        }

        return $this->__result($result);
    }

    /**
     * 推送通知 - 点击下载应用
     *
     * @param   mixed   $cid
     * @param   string  $title
     * @param   string  $content
     *
     * @return  array|bool
     */
    public function push_download_message($cid, $title = '', $content = '')
    {
        $template = $this->noty_pop_load_template($title, $content);

        // 群推
        if($cid === 'all') {
            $result = $this->__push_message_all($template);

            // 多用户
        } else if (is_array($cid)) {
            $result = $this->__push_message_list($template, $cid);

            // 单用户
        } else if(is_string($cid)) {
            $result = $this->__push_message($template, $cid);

        } else {
            $this->set_error("CID 为未知的数据类型！");
            return false;
        }

        return $this->__result($result);
    }

    /**
     * 推送 消息/透传 - 单用户
     *
     * @param   array   $template   消息模版
     * @param   mixed   $cid
     *
     * @return  array|bool
     */
    private function __push_message($template, $cid)
    {
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime($this->__offline_expire_time);
        $message->set_data($template);
        $message->set_PushNetWorkType(0);

        // 接收方
        $target = new IGtTarget();
        $target->set_appId($this->__app_id);
        $target->set_clientId($cid);

        return $this->pushMessageToSingle($message, $target);
    }

    /**
     * 推送 消息/透传 - 多用户
     *
     * @param   array   $template   消息模版
     * @param   array   $cid_list   设备列表
     *
     * @return  array|bool
     */
    private function __push_message_list($template, array $cid_list = array())
    {
        if (empty($cid_list)) {
            $this->set_error("未指定设备 Cid ！");
            return false;
        }

        putenv("needDetails=true");

        //个推信息体
        $message = new IGtListMessage();
        $message->set_isOffline(true);
        $message->set_data($template);
        $message->set_offlineExpireTime($this->__offline_expire_time);
        $contentId = $this->getContentId($message);

        // 接收方
        $target_list = array();
        foreach($cid_list as $key => $val) {

            if(!empty($val)) {
                $temp_target = new IGtTarget();
                $temp_target->set_appId($this->__app_id);
                $temp_target->set_clientId($val);

                $target_list[] = $temp_target;
            }

            // 个推官方建议为单次为 50 个用户， 此处每50次推送一次
            if(!empty($target_list) && !(count($target_list) % 50)) {
                $result[] = $this->pushMessageToList($contentId, $target_list);
                $target_list = array();
            }
        }

        if (count($target_list) < 50) {
            $result[] = $this->pushMessageToList($contentId, $target_list);
            $target_list = array();
        }

        return $result;
    }

    /**
     * 推送 消息/透传 - 多用户
     *
     * @param   array   $template   消息模版
     *
     * @return  array|bool
     */
    private function __push_message_all($template)
    {
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_data($template);
        $message->set_PushNetWorkType(0);
        $message->set_appIdList(array($this->__app_id));
        $message->set_offlineExpireTime($this->__offline_expire_time);

        // 根据TaskId设置组名，支持下划线，中文，英文，数字
        return $this->pushMessageToApp($message, 'toApp任务别名');
    }

    /**
     * 透传功能模板
     */
    public function transmission_template($content = '')
    {
        $template = new IGtTransmissionTemplate();
        $template->set_appId($this->__app_id);
        $template->set_appkey($this->appkey);
        $template->set_transmissionType(0);
        $template->set_transmissionContent($content);
        $template->set_pushInfo("actionLocKey","badge","message", "sound","payload","locKey","locArgs","launchImage");

        return $template;
    }

    /**
     * 点击通知打开网页模板
     */
    public function link_template($title = '', $content = '', $url = '')
    {
        $template =  new IGtLinkTemplate();

        $template->set_appId($this->__app_id);
        $template->set_appkey($this->appkey);
        $template->set_title($title);                                // 通知栏标题
        $template->set_text($content);                               // 通知栏内容
        $template->set_logo($this->__config['logo']);                // 通知栏logo
        $template->set_isRing($this->__config['is_ring']);           // 是否响铃
        $template->set_isVibrate($this->__config['is_vibrate']);     // 是否震动
        $template->set_isClearable($this->__config['is_clearable']); // 通知栏是否可清除
        $template->set_url($url);                                    // 打开 URL 地址

        return $template;
    }

    /**
     * 点击通知打开应用模板 【点击后有透传消息】
     */
    public function notification_template($title = '', $content = '', $instruction = '')
    {
        $template =  new IGtNotificationTemplate();
        $template->set_appId($this->__app_id);
        $template->set_appkey($this->appkey);
        $template->set_transmissionType(1);                          // 收到消息是否立即启动应用：1为立即启动，2则广播等待客户端自启动
        $template->set_transmissionContent($instruction);            // 透传的内容指令
        $template->set_title($title);                                // 通知栏标题
        $template->set_text($content);                               // 通知栏内容
        $template->set_logo($this->__config['logo']);                // 通知栏logo
        $template->set_isRing($this->__config['is_ring']);           // 是否响铃
        $template->set_isVibrate($this->__config['is_vibrate']);     // 是否震动
        $template->set_isClearable($this->__config['is_clearable']); // 通知栏是否可清除
        return $template;
    }

    /**
     * 点击通知栏弹框下载模版
     */
    public function noty_pop_load_template($title = '', $content = '')
    {
        if (empty($this->__config['pop']) || empty($this->__config['download']) ) {
            $this->set_error("下载模板未配置！");
            return false;
        }

        $template =  new IGtNotyPopLoadTemplate();
        $template->set_appId($this->__app_id);
        $template->set_appkey($this->appkey);

        // 通知栏
        $template->set_notyTitle($title);                            // 通知栏标题
        $template->set_notyContent($content);                        // 通知栏内容
        $template->set_notyIcon($this->__config['logo']);            // 通知栏logo
        $template->set_isBelled($this->__config['is_ring']);         // 是否响铃
        $template->set_isVibrationed($this->__config['is_vibrate']); // 是否震动

        // 弹窗模板 【确认下载前的样式】
        $template->set_popTitle($this->__config['pop']['popTitle']);      // 弹框标题
        $template->set_popContent($this->__config['pop']['popContent']);  // 弹框内容
        $template->set_popImage($this->__config['pop']['popImage']);      // 弹框图片 (弹框内容 在弹框图片之前)
        $template->set_popButton1($this->__config['pop']['popButton1']);  // 左键
        $template->set_popButton2($this->__config['pop']['popButton2']);  // 右键

        // 配置下载模版【在下载过程中的样式】
        $template->set_loadIcon($this->__config['download']['loadIcon']);           // 下载图标
        $template->set_loadTitle($this->__config['download']['loadTitle']);         // 下载标题
        $template->set_loadUrl($this->__config['download']['loadUrl']);             // 下载地址
        $template->set_isAutoInstall($this->__config['download']['isAutoInstall']); // 是否自动安装（默认 false）
        $template->set_isActived($this->__config['download']['isActived']);         // 安装完成后是否自动启动应用程序（默认 false）

        return $template;
    }
}