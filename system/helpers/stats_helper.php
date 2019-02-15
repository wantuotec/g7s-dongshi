<?php
/**
 * 获得浏览器名称和版本
 *
 * @access  public
 * @return  string
 */
function get_user_browser()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return '';
    }

    $agent   = $_SERVER['HTTP_USER_AGENT'];
    $browser = '';
    $version = '';

    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $matches)) {
        $browser = 'Internet Explorer';
        $version = $matches[1];
    } elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $matches)) {
        $browser = 'FireFox';
        $version = $matches[1];
    } elseif (preg_match('/Maxthon/i', $agent, $matches)) {
        $browser = 'Maxthon';
        $version = '';
    } elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $matches)) {
        $browser = 'Opera';
        $version = $matches[1];
    } elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $matches)) {
        $browser = 'OmniWeb';
        $version = $matches[2];
    } elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $matches)) {
        $browser = 'Netscape';
        $version = $matches[2];
    } elseif (preg_match('/chrome\/([^\s]+)/i', $agent, $matches)) {
        $browser = 'Chrome';
        $version = $matches[1];
    } elseif (preg_match('/safari\/([^\s]+)/i', $agent, $matches)) {
        $browser = 'Safari';
        $version = $matches[1];
    } elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $matches)) {
        $browser = 'NetCaptor';
        $version = $matches[1];
    } elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $matches)) {
        $browser = 'Lynx';
        $version = $matches[1];
    }

    if (!empty($browser)) {
       return addslashes($browser . ' ' . $version);
    } else {
        return 'unknow';
    }
}

/**
 * 判断是否为搜索引擎蜘蛛
 *
 * @access  public
 * @return  string
 */
function is_spider($record = true)
{
    static $spider = NULL;

    if ($spider !== NULL) {
        return $spider;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $spider = '';

        return '';
    }

    $searchengine_bot = array(
        'googlebot',
        'mediapartners-google',
        'baiduspider+',
        'msnbot',
        'yodaobot',
        'yahoo! slurp;',
        'yahoo! slurp china;',
        'iaskspider',
        'sogou web spider',
        'sogou push spider'
    );

    $searchengine_name = array(
        'GOOGLE',
        'GOOGLE ADSENSE',
        'BAIDU',
        'MSN',
        'YODAO',
        'YAHOO',
        'Yahoo China',
        'IASK',
        'SOGOU',
        'SOGOU'
    );

    $spider = strtolower($_SERVER['HTTP_USER_AGENT']);

    foreach ($searchengine_bot as $key => $value) {
        if (false !== strpos($spider, $value)) {
            $spider = $searchengine_name[$key];

            if ($record === true) {
                $GLOBALS['db']->autoReplace($GLOBALS['ecs']->table('searchengine'), array('date' => local_date('Y-m-d'), 'searchengine' => $spider, 'count' => 1), array('count' => 1));
            }

            return $spider;
        }
    }

    $spider = '';

    return '';
}

/**
 * 获得客户端的操作系统
 *
 * @access  private
 * @return  void
 */
function get_os()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return 'Unknown';
    }

    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $os    = '';

    if (strpos($agent, 'win') !== false) {
        if (strpos($agent, 'nt 5.1') !== false) {
            $os = 'Windows XP';
        } elseif (strpos($agent, 'nt 5.2') !== false) {
            $os = 'Windows 2003';
        } elseif (strpos($agent, 'nt 5.0') !== false) {
            $os = 'Windows 2000';
        } elseif (strpos($agent, 'nt 6.0') !== false) {
            $os = 'Windows Vista';
        } elseif (strpos($agent, 'nt 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (strpos($agent, 'nt') !== false) {
            $os = 'Windows NT';
        } elseif (strpos($agent, 'win 9x') !== false && strpos($agent, '4.90') !== false) {
            $os = 'Windows ME';
        } elseif (strpos($agent, '98') !== false) {
            $os = 'Windows 98';
        } elseif (strpos($agent, '95') !== false) {
            $os = 'Windows 95';
        } elseif (strpos($agent, '32') !== false) {
            $os = 'Windows 32';
        } elseif (strpos($agent, 'ce') !== false) {
            $os = 'Windows CE';
        }
    } elseif (strpos($agent, 'linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($agent, 'unix') !== false) {
        $os = 'Unix';
    } elseif (strpos($agent, 'sun') !== false && strpos($agent, 'os') !== false) {
        $os = 'SunOS';
    } elseif (strpos($agent, 'ibm') !== false && strpos($agent, 'os') !== false) {
        $os = 'IBM OS/2';
    } elseif (strpos($agent, 'mac') !== false && strpos($agent, 'pc') !== false) {
        $os = 'Macintosh';
    } elseif (strpos($agent, 'powerpc') !== false) {
        $os = 'PowerPC';
    } elseif (strpos($agent, 'aix') !== false) {
        $os = 'AIX';
    } elseif (strpos($agent, 'hpux') !== false) {
        $os = 'HPUX';
    } elseif (strpos($agent, 'netbsd') !== false) {
        $os = 'NetBSD';
    } elseif (strpos($agent, 'bsd') !== false) {
        $os = 'BSD';
    } elseif (strpos($agent, 'osf1') !== false) {
        $os = 'OSF1';
    } elseif (strpos($agent, 'irix') !== false) {
        $os = 'IRIX';
    } elseif (strpos($agent, 'freebsd') !== false) {
        $os = 'FreeBSD';
    } elseif (strpos($agent, 'teleport') !== false) {
        $os = 'teleport';
    } elseif (strpos($agent, 'flashget') !== false) {
        $os = 'flashget';
    } elseif (strpos($agent, 'webzip') !== false) {
        $os = 'webzip';
    } elseif (strpos($agent, 'offline') !== false) {
        $os = 'offline';
    } else {
        $os = 'unknown';
    }

    return $os;
}


/**
 * 获得用户的真实IP地址
 *
 * @access  public
 * @return  string
 */
function real_ip()
{
    static $realip = NULL;

    if ($realip !== NULL) {
        return $realip;
    }

    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

    return $realip;
}


/**
 * 获得搜索引擎关键字
 *
 * @access  public
 * @return  void
 */
function save_searchengine_keyword($domain, $path)
{
    $searchengine = '';
    $keywords = '';
    if (strpos($domain, 'google.com.tw') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE TAIWAN';
        $keywords = urldecode($regs[1]); // google taiwan
    }
    if (strpos($domain, 'google.cn') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE CHINA';
        $keywords = urldecode($regs[1]); // google china
    }
    if (strpos($domain, 'google.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE';
        $keywords = urldecode($regs[1]); // google
    } elseif (strpos($domain, 'baidu.') !== false && preg_match('/wd=([^&]*)/i', $path, $regs)) {
        $searchengine = 'BAIDU';
        $keywords = urldecode($regs[1]); // baidu
    } elseif (strpos($domain, 'baidu.') !== false && preg_match('/word=([^&]*)/i', $path, $regs)) {
        $searchengine = 'BAIDU';
        $keywords = urldecode($regs[1]); // baidu
    } elseif (strpos($domain, '114.vnet.cn') !== false && preg_match('/kw=([^&]*)/i', $path, $regs)) {
        $searchengine = 'CT114';
        $keywords = urldecode($regs[1]); // ct114
    } elseif (strpos($domain, 'iask.com') !== false && preg_match('/k=([^&]*)/i', $path, $regs)) {
        $searchengine = 'IASK';
        $keywords = urldecode($regs[1]); // iask
    } elseif (strpos($domain, 'soso.com') !== false && preg_match('/w=([^&]*)/i', $path, $regs)) {
        $searchengine = 'SOSO';
        $keywords = urldecode($regs[1]); // soso
    } elseif (strpos($domain, 'sogou.com') !== false && preg_match('/query=([^&]*)/i', $path, $regs)) {
        $searchengine = 'SOGOU';
        $keywords = urldecode($regs[1]); // sogou
    } elseif (strpos($domain, 'so.163.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'NETEASE';
        $keywords = urldecode($regs[1]); // netease
    } elseif (strpos($domain, 'yodao.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YODAO';
        $keywords = urldecode($regs[1]); // yodao
    } elseif (strpos($domain, 'zhongsou.com') !== false && preg_match('/word=([^&]*)/i', $path, $regs)) {
        $searchengine = 'ZHONGSOU';
        $keywords = urldecode($regs[1]); // zhongsou
    } elseif (strpos($domain, 'search.tom.com') !== false && preg_match('/w=([^&]*)/i', $path, $regs)) {
        $searchengine = 'TOM';
        $keywords = urldecode($regs[1]); // tom
    } elseif (strpos($domain, 'live.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSLIVE';
        $keywords = urldecode($regs[1]); // MSLIVE
    } elseif (strpos($domain, 'tw.search.yahoo.com') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO TAIWAN';
        $keywords = urldecode($regs[1]); // yahoo taiwan
    } elseif (strpos($domain, 'cn.yahoo.') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO CHINA';
        $keywords = urldecode($regs[1]); // yahoo china
    } elseif (strpos($domain, 'yahoo.') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO';
        $keywords = urldecode($regs[1]); // yahoo
    } elseif (strpos($domain, 'msn.com.tw') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN TAIWAN';
        $keywords = urldecode($regs[1]); // msn taiwan
    } elseif (strpos($domain, 'msn.com.cn') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN CHINA';
        $keywords = urldecode($regs[1]); // msn china
    } elseif (strpos($domain, 'msn.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN';
        $keywords = urldecode($regs[1]); // msn
    }

    return array($searchengine , $keywords);
}

/**
 * 通过IP获取地区
 *
 * @access  public
 * @return  void
 */
function geoip($ip)
{
    static $fp = NULL, $offset = array(), $index = NULL;

    $ip    = gethostbyname($ip);
    $ipdot = explode('.', $ip);
    $ip    = pack('N', ip2long($ip));

    $ipdot[0] = (int)$ipdot[0];
    $ipdot[1] = (int)$ipdot[1];
    if ($ipdot[0] == 10 || $ipdot[0] == 127 || ($ipdot[0] == 192 && $ipdot[1] == 168) || ($ipdot[0] == 172 && ($ipdot[1] >= 16 && $ipdot[1] <= 31))) {
        return 'LAN';
    }

    if ($fp === NULL) {
        $fp = fopen(BASEPATH . '/ipdata.dat', 'rb');
        if ($fp === false) {
            return 'Invalid IP data file';
        }
        $offset = unpack('Nlen', fread($fp, 4));
        if ($offset['len'] < 4) {
            return 'Invalid IP data file';
        }
        $index  = fread($fp, $offset['len'] - 4);
    }

    $length = $offset['len'] - 1028;
    $start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
            $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }

    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    $area = fread($fp, $index_length['len']);

    fclose($fp);
    $fp = NULL;

    return $area;
}

/**
 * 获取统计访问信息
 *
 * @access  public
 *
 * @param   array   $params 要记录的参数
 *
 * @return  void
 */
function visit_stats($params = array())
{
    $time = date('Y-m-d H:i:s');
    /* 检查客户端是否存在访问统计的cookie */
    $visit_times = (!empty($_SESSION['MX']['visit_times'])) ? intval($_SESSION['MX']['visit_times']) + 1 : 1;
    $_SESSION['MX']['visit_times'] = $visit_times;
    $user_name = !empty($_SESSION['user']['username'])?$_SESSION['user']['username']:'';

    $browser      = get_user_browser();
    $os           = get_os();
    $ip           = real_ip();
    $area         = geoip($ip);
    $searchengine = '';
    $keywords     = '';

    /* 语言 */
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $pos  = strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ';');
        $lang = addslashes(($pos !== false) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, $pos) : $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    } else {
        $lang = '';
    }

    $referer     = empty($params) ? $_SERVER['HTTP_REFERER'] : $params['referer'];
    $union_id    = 0;
    $union_extra = '';
    if (empty($params['url'])) {
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = $_SERVER['REQUEST_URI'];
        $query = $_SERVER['QUERY_STRING'];
    } else {
        $url   = parse_url($params['url']);
        $host  = empty($url['host'])   ? '' : $url['host'];
        $query = empty($url['query'])  ? '' : $url['query'];
        $uri   = (empty($url['path'])  ? '' : $url['path']);
        !empty($uri) && !empty($query) && $uri = $uri . '?' . $query;

        if (!empty($params['u'])) {
            $union_id    = $params['u'];
            $union_extra = $params['e'];
        }
    }

    /* 来源 */
    if (!empty($referer) && strlen($referer) > 9) {
        $pos = strpos($referer, '/', 9);
        if ($pos !== false) {
            $domain = substr($referer, 0, $pos);
            $path   = substr($referer, $pos);

            /* 来源关键字 */
            if (!empty($domain) && !empty($path)) {
                $ret = save_searchengine_keyword($domain, $path);
                $searchengine =$ret[0];
            }
        } else {
            $domain = $path = '';
        }
    } else {
        $domain = $path = '';
    }

    $params = array(
        'ip_address'     => $ip, 
        'visit_times'    => $visit_times ,
        'browser'        => $browser,
        'system'         => $os,
        'language'       => $lang, 
        'area'           => $area,  
        'referer_domain' => addslashes($domain), 
        'referer_path'   => addslashes($path), 
        'access_domain'  => addslashes($host),
        'access_url'     => addslashes($uri),
        'query_string'   => addslashes($query),
        'access_time'    => $time, 
        'searchengine'   => $searchengine,
        'keywords'       => $keywords,
        'user_name'      => $user_name,
        'session_id'     => session_id(),
        'agent'          => strval($_SERVER['HTTP_USER_AGENT']),
        'union_id'       => empty($union_id)    ?  0 : max(0, intval($union_id)),
        'union_extra'    => empty($union_extra) ? '' : $union_extra,
    );

    // $CI =& get_instance();
    // $CI->load->rwdb(array('cluster' => 5, 'mode' => 'write'));
    // $CI->rwdb->insert('visit_stats' , $params);
}
