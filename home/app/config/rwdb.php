<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('DB_USER_00'    , 'www_dreamma_cn');
define('DB_PASS_00'    , 'j5tGtyN7ijbkPMki');
define('DB_PORT_00'    , '3306');
define('DB_DRIVER_00'  , 'mysqli');
define('DB_PREFIX_00'  , ''); // 表前缀
define('DATABASE_W_00' , '118.24.157.44');
define('DATABASE_R_00' , '118.24.157.44');

$dbConfig = array(
    "db_physical" => array(
        0 => array( // physical master-slave shard configuration;
            'write' => array(
                'hostname' => DATABASE_W_00,
                'port'     => DB_PORT_00,
                'username' => DB_USER_00,
                'password' => DB_PASS_00,
                'dbdriver' => DB_DRIVER_00,
                'dbprefix' => DB_PREFIX_00,
                'pconnect' => false,
                'db_debug' => true,
                'cache_on' => false,
                'cachedir' => '',
                'char_set' => 'utf8',
                'dbcollat' => 'utf8',
                'swap_pre' => '',
                'autoinit' => true,
                'stricton' => false,
            ),
            'read' => array(
                'hostname' => DATABASE_R_00,
                'port'     => DB_PORT_00,
                'username' => DB_USER_00,
                'password' => DB_PASS_00,
                'dbdriver' => DB_DRIVER_00,
                'dbprefix' => DB_PREFIX_00,
                'pconnect' => false,
                'db_debug' => true,
                'cache_on' => false,
                'cachedir' => '',
                'char_set' => 'utf8',
                'dbcollat' => 'utf8',
                'swap_pre' => '',
                'autoinit' => true,
                'stricton' => false,
            ),
        ),
    ),
    "db_cluster" => array(
        0 => array( // key stands logical cluster id
            'map'            => 0,
            'db_name_prefix' => '',
            'farm_count'     => 0,
            'farm_policy'    => '',
        ),
    ),
    "db_singles" => array(
        // 目前所使用阿里服务器不支持自创建其它数据库
        1 => array(
            'map'      => 0,
            'database' => 'www_dreamma_cn',
        ),
    ),
);