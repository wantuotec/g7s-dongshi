<?php
/**
 * Reids 配置信息
 *
 * @author      willy
 * @date        2016-04-08
 * @copyright   Copyright(c) 2016 
 * @version     $Id$
 */
$config = [
    'host'             => '127.0.0.1',
    'port'             => 6379,
    'timeout'          => 10,  // 单位 s 0 为不限制
    'reserved'         => NULL,
    'retry_interval'   => 100, // 单位 ms 重连
    'password'         => '',
    'default_db_index' => 0,   // 默认切换到哪个db
    'serializer'       => 1,   // 0 不序列化 1 使用PHP内置的serialize/unserialize 2 使用 igBinary serialize/unserialize
];