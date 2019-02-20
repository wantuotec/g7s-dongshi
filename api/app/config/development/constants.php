<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Domain constants
|--------------------------------------------------------------------------
*/
define('DOMAIN'      , 'dreamma.cn');
define('M_DOMAIN'    , 'http://devm.' . DOMAIN . '/');
define('M_IMG_DOMAIN', 'http://devm.' . DOMAIN . '/mobile/public/upload/images/');
define('WX_JS_AUTH',   'http://devm.' . DOMAIN . '/mobile/include/');

/*
|--------------------------------------------------------------------------
| Website constants
|--------------------------------------------------------------------------
*/
define('COMPANY'    , '梦马空间个人站');
define('TITLE'      , '梦马空间');
define('ADM_TITLE'  , '梦马空间后台系统');
define('SLOGAN'     , '以梦为马，诗酒乘年华');
define('KEYWORDS'   , '个人空间，博客，摄影，90后，分享');
define('DESCRIPTION', '这里是小编的个人空间，希望分享一些日常的生活学习点滴，共同成长');
define('APP_VERSION', '1.0.0');
define('JS_VERSION' , '20170101');
define('CSS_VERSION', '20170101');
define('HTML_EDITION', 'v1');

/*
|--------------------------------------------------------------------------
| 常用变量设置
|--------------------------------------------------------------------------
*/

// 加载的网页模板版本
define('HTML_EDITION', 'v1');

// 根目录， 绝对路径
define("ROOT_PATH", substr(BASEPATH, 0, -7));

// 分页数
define("PAGE_SIZE", 10);

// 百度地图AK (KEY)
define("BAIDU_MAP_AK", "");

// 微信公众号Token
define("WX_TOKEN", "dreamma1993xiaoma1126");

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE',   0644);
define('FILE_WRITE_MODE',  0666);
define('DIR_READ_MODE',    0755);
define('DIR_WRITE_MODE',   0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',                          'rb');
define('FOPEN_READ_WRITE',                    'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',      'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',                  'ab');
define('FOPEN_READ_WRITE_CREATE',             'a+b');
define('FOPEN_WRITE_CREATE_STRICT',           'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',      'x+b');

/* End of file constants.php */
/* Location: ./application/config/constants.php */