<?php
/**
 * Memcached 配置信息
 *
 * @author      willy
 * @date        2012-05-24
 * @copyright   Copyright(c) 2012 
 * @version     $Id: memcached.php 770 2013-09-25 11:04:27Z 杨海波 $
 */
$config = array(
    'default' => array(
        'hostname'   => '127.0.0.1',
        'port'       => 11211,
        'persistent' => false,
        'weight'     => 1,
        'timeout'    => 10,
    ),
);