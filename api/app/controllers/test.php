<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 接口测试工具
 *
 * @author       杨海波
 * @date         2015-04-24
 * @category     Test
 * @copyright    Copyright(c) 2015
 * @version      $Id$
 */

class Test extends CI_Controller
{
    private $__api_uris = null;

    private  $__params = array(
        'charset'       =>  'UTF-8',
        'format'        =>  'json',
        'sign_method'   =>  'md5',
        'version'       =>  '1.0',
    );

    public function __construct()
    {
        parent::__construct();

        $this->__api_uris = array(
            '1' => array('http://devm.'  . DOMAIN . '/rest', 'Dev本地环境'   , '84f6dca8fa4d1cea71e78defd0a78b61', '44292f0591261ec115454df2605e208b', 'c0682d5ab02386fb223fd2e1635560b8', '1.0.0', '1', '599F9C00-92DC-4B5C-9464-7971F01F8370'),
            '2' => array('http://m.'     . DOMAIN . '/rest', 'Online正式环境', '84f6dca8fa4d1cea71e78defd0a78b61', '44292f0591261ec115454df2605e208b', 'c0682d5ab02386fb223fd2e1635560b8', '1.0.0', '1', '599F9C00-92DC-4B5C-9464-7971F01F8370'),
        );
    }

    /**
     * 接口默认入口地址
     */
    public function index()
    {
        // 如果是开发环境才可以用
        if ('development' == ENVIRONMENT) {
            $result = array(
                'api_uris' => $this->__api_uris,
            );

            if ($this->input->is_post_request()) {
                $params = $this->input->post();

                $params['charset']      = $this->__params['charset'];
                $params['format']       = $this->__params['format'];
                $params['sign_method']  = $this->__params['sign_method'];
                $params['version']      = $this->__params['version'];

                foreach ($params['params_fields'] as $key => $val) {
                    $params['params_fields'][$key] = trim($val);
                    if (empty($val)) {
                        unset($params['params_fields'][$key]);
                        unset($params['params_values'][$key]);
                    }
                }
                $post_params_json = stripslashes(str_replace('\n','',$params['params']));
                $post_params = empty($post_params_json) ? [] : json_decode($post_params_json, true);
                if (!empty($post_params) && !empty($params['params_fields'])) {
                    $params['params'] = array_merge($post_params, array_combine($params['params_fields'], $params['params_values']));
                } elseif (empty($post_params) && !empty($params['params_fields'])) {
                    $params['params'] = array_combine($params['params_fields'], $params['params_values']);
                }

                if($_POST['preview_md']){
                    foreach($post_params as $key => $val)
                    {
                        if(!empty($val) && !in_array($key, $params['params_fields']))
                        {
                            array_push($params['params_fields'], $key);
                        }
                    }
                }

                if ('1.0' === $params['version']) {
                    if (empty($params['app_key']) && empty($params['app_secret']) && empty($params['app_session'])) {
                        $params['app_key']     = $this->__api_uris[$params['api_uri']][2];
                        $params['app_secret']  = $this->__api_uris[$params['api_uri']][3];
                        $params['app_session'] = $this->__api_uris[$params['api_uri']][4];
                    }

                    if (empty($params['app_version'])) {
                        $params['app_version'] = $this->__api_uris[$params['api_uri']][5];
                    }

                    if (empty($params['app_type'])) {
                        $params['app_type'] = $this->__api_uris[$params['api_uri']][6];
                    }

                    if (empty($params['uuid'])) {
                        $params['uuid'] = $this->__api_uris[$params['api_uri']][7];
                    }

                    $origin = $params;

                    $this->load->library('api');
                    $this->api->set_uri($this->__api_uris[$params['api_uri']][0]);
                    $this->api->set_ssl(false);  // 不加密通信
                    $response = $this->api->request_test($params);

                    if (false === $response) {
                        $response = $this->api->get_error();
                    }
                    $result['response_var'] = $response;
                    $result['response']     = var_export($response, true);
                    $result['params_json']  = json_encode($params);
                }

                $result = array_merge($result, $origin, $params);

                if ($_POST['preview_md']) {
                    $content = $this->load->view('test/preview_md.tpl', $result, true);
                    $file_name = $params['method'] . '.md';
                    $this->save_to_file($content, $file_name);
                    exit ();
                }
            }

            if (!$_POST['preview_md']) {
                $this->load->view('test/index.tpl', $result);
            }
        } else {
            // 不允许访问
            url_404();
        }
    }

    /**
     * 获取一个唯一 KEY
     */
    public function get_unique_key()
    {
        // 如果是开发环境才可以用
        if ('development' == ENVIRONMENT) {
            echo get_unique_key(), '   ', date('Y-m-d H:i:s'), '<br />';
            echo get_unique_key(), '   ', date('Y-m-d H:i:s'), '<br />';
            echo get_unique_key(), '   ', date('Y-m-d H:i:s'), '<br />';
        }
    }

    private function save_to_file($content, $file_name)
    {
        Header ( "Content-type: application/octet-stream" );
        Header ( "Accept-Ranges: bytes" );
        Header ( "Accept-Length: " . strlen($content) );
        Header ( "Content-Disposition: attachment; filename=" . $file_name );
        echo $content;
        exit ();
    }

    // 测试post数据到微信端，获取响应
    public function postXmlToWx()
    {
        $xmlData = [];

        // Text类型数据：
        $xmlData['text']  = "<xml>
            <ToUserName><![CDATA[Dreamma]]></ToUserName>
            <FromUserName><![CDATA[gh_42ee5ebbfbdf]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[你好啊！Dreamma小编，2018年会更好的......]]></Content>
            <MsgId>12345678901993</MsgId>
            </xml>";

        // Image类型数据：
        $xmlData['image']  = "<xml>
            <ToUserName><![CDATA[Dreamma]]></ToUserName>
            <FromUserName><![CDATA[gh_42ee5ebbfbdf]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[你好啊！Dreamma小编，2018年会更好的......]]></Content>
            <MsgId>12345678901993</MsgId>
            </xml>";


        // post数据到微信接口
        $this->load->library('api');
        $this->api->set_uri('http://devm.dreamma.cn/weixin');
        $response = $this->api->post($xmlData['text']);
    }
}