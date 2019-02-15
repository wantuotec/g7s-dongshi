<?php
/**
 * Push 配置信息
 *
 * @author      杨海波
 * @date        2015-05-29
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */

$config = array(
    // 极光推送应用信息配置
    'jpush' => array(
        // 鲜米用户
        'xianmi_member' => array(
            'app_key'         => '178c38ff3cc27e46f65346dc',
            'master_secret'   => '276c3008ade21c4bb90afc81',
            'expire_time'     => 120,  // 离线消息保留时长（秒）不填默认 1 天
            'apns_production' => true, // APNs 是否生产环境推送
        ),

        // 鲜米商家
        'xianmi_shop' => array(
            'app_key'         => '5de69f0b9820fd9dcf0a2d6e',
            'master_secret'   => '62cf51208c7c7f597565ad7c',
            'expire_time'     => 120,
            'apns_production' => true,
        ),

        // 鲜米配送员
        'xianmi_courier' => array(
            'app_key'         => '',
            'master_secret'   => '',
            'expire_time'     => 120,
            'apns_production' => true,
        ),

        // 鲜急送配送
        'xjs_courier' => array(
            'app_key'         => 'd85d31d719aa2820e2cc5be7',
            'master_secret'   => '25bafad8528680ff69bd819f',
            'expire_time'     => 120,
            'apns_production' => true,
        ),
    ),

    // 个推推送应用信息配置
    'getui' => array(
        // 鲜急送商家
        'xjs_shop' => array(
            'appname'      => '鲜急送商家',
            'appid'        => '',
            'appkey'       => '',
            'masterSecret' => '',
        ),

        // 鲜急送配送
        'xjs_courier' => array(
            'appname'      => '鲜急送配送',
            'appid'        => '',
            'appkey'       => '',
            'masterSecret' => '',
        ),

        // 鲜急送配送市区
        'xjs_courier_city' => array(
            'appname'      => '鲜急送配送市区',
            'appid'        => '',
            'appkey'       => '',
            'masterSecret' => '',
        ),

        // 飞到门用户
        'fdm_member' => array(
            'appname'      => 'fdm_member',
            'appid'        => '',
            'appkey'       => '',
            'masterSecret' => '',
        ),

        // 飞到门众包员
        'fdm_crowdsource' => array(
            'appname'      => '飞到门众包员',
            'appid'        => '',
            'appkey'       => '',
            'masterSecret' => '',
        ),
    ),
);
