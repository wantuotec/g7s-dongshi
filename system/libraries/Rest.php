<?php
class CI_Rest
{
    /* 
    使用方法：
        GET 示例
        $t = new Rest('http://localhost/rest.php?a=1&b=2', null, 'GET');
        // 或者 $t = new Rest('http://localhost/rest.php', array('a'=>'1', 'b'=>'2'), 'GET');
        $result = $t->execute(); // execute(true) 可以解密 json 格式内容
        POST 示例
        $t = new Rest('http://localhost/rest.php', array('c'=>'cc', 'd'=>'dd'));
        $result = $t->execute(); 
        构造函数的第四个参数为超时时间 默认为 10 秒
        timeout 示例
        $t = new Rest('http://localhost/rest.php', array('c'=>'cc', 'd'=>'dd'), 'POST', $timeout);
    */
    protected $url;           // 请求地址
    protected $verb;          // HTTP 方法 GET POST PUT DELETE
    protected $requestParams; // 请求参数
    protected $requestLength; // 请求长度
    protected $timeout;       // 最大请求时间
    protected $username;      // 用户名
    protected $password;      // 密码
    protected $acceptType;    // 接受类型
    protected $responseBody;  // 响应内容
    protected $responseInfo;  // 响应信息
    protected $encode;        // 是否加密请求
    protected $encodeKey = '9290A483A070EA08B5E77E2304EBB604'; // 加密使用的 key

    /**
     * 构造函数
     * 
     * @param   string  $params
     *
     * @return  void
     */
    /*
    public function __construct($params = array())
    {
        // 参数初始化
        $params = array(
            'url'     => empty($params['url'])     ? null   : $params['url'],
            'data'    => empty($params['data'])    ? null   : $params['data'],
            'verb'    => empty($params['verb'])    ? 'POST' : $params['verb'],
            'timeout' => empty($params['timeout']) ? 10     : intval($params['timeout']),
            'encode'  => is_bool($params['encode'])? $params['encode'] : false,
        );

        $this->url           = $params['url'];
        $this->verb          = $params['verb'];
        $this->requestBody   = $params['data'];
        $this->requestLength = 0;
        $this->timeout       = $params['timeout'];
        $this->username      = null;
        $this->password      = null;
        $this->acceptType    = 'application/json';
        $this->responseBody  = null;
        $this->responseInfo  = null;
        $this->encode        = $params['encode'];

        if ($this->requestBody !== null)
        {
            $this->buildPostBody();
        }

        // return $this;
    }
    */

    /**
     * 配置 rest 参数
     * 
     * @param   string  $params
     *
     * @return  void
     */
    public function config($params)
    {        
        // 参数初始化
        $params = array(
            'url'     => empty($params['url'])     ? null   : $params['url'],
            'data'    => empty($params['data'])    ? null   : $params['data'],
            'verb'    => empty($params['verb'])    ? 'POST' : $params['verb'],
            'timeout' => empty($params['timeout']) ? 10     : intval($params['timeout']),
            'encode'  => isset($params['encode']) && is_bool($params['encode'])? $params['encode'] : false,
        );

        $this->url           = $params['url'];
        $this->verb          = $params['verb'];
        $this->requestBody   = $params['data'];
        $this->requestLength = 0;
        $this->timeout       = $params['timeout'];
        $this->username      = null;
        $this->password      = null;
        $this->acceptType    = 'application/json';
        $this->responseBody  = null;
        $this->responseInfo  = null;
        $this->encode        = $params['encode'];

        if ($this->requestBody !== null)
        {
            $this->buildPostBody();
        }

        // return $this;
    }

    /**
     * 分发请求
     *
     * @param   bool    $isJsonDecode   是否对反馈的消息进行 json_decode
     *
     * @return  void
     */
    public function execute($isJsonDecode = false)
    {
        $ch = curl_init();
        $this->setAuth($ch);

        try {
            if (in_array(strtoupper($this->verb), array('GET', 'POST', 'PUT', 'DELETE'))) {
                $callBack = 'execute' . ucfirst(strtolower($this->verb));
                if (is_callable(array($this, $callBack))) {
                    call_user_func(array($this, $callBack), $ch);
                    $result = $this->getResponseBody();
                    return (bool) $isJsonDecode ? json_decode($result, true) : $result;
                } else {
                    throw new InvalidArgumentException('There is no (' . $callBack . ') function in the Rest Class.');
                }
            } else {
                throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
            }
        } catch (InvalidArgumentException $e) {
            curl_close($ch);
            throw $e;
        } catch (Exception $e) {
            curl_close($ch);
            throw $e;
        }
    }

    /**
     * 清空数据
     * 
     * @return  void
     */
    public function flush()
    {
        $this->requestBody   = null;
        $this->requestLength = 0;
        $this->timeout       = 10;
        $this->verb          = 'POST';
        $this->responseBody  = null;
        $this->responseInfo  = null;
    }

    /**
     * 创建请求数据
     * 
     * @param   array   $data   请求的数据
     *
     * @return  void
     */
    public function buildPostBody($data = null)
    {
        $data = ($data !== null) ? $data : $this->requestBody;
        
        if (!is_array($data))
        {
            throw new InvalidArgumentException('Invalid data input for postBody.  Array expected');
        }

        if (true === $this->encode) {
            $data['encode_time'] = date('YmdHis');
            ksort($data);
            $temp = http_build_query($data);
            $data['encode_sign'] = md5($temp . $this->encodeKey);
        }

        $data = http_build_query($data);

        $this->requestBody = $data;
    }

    /**
     * 解密
     * 
     * @access  public
     * @param   mixed   $params
     * @return  bool
     */
    public function decode(&$params)
    {
        if (empty($params) && !is_array($params)) {
            return false;
        }

        if (empty($params['encode_time']) || empty($params['encode_sign'])) {
            return false;
        }

        // 五分钟内有效
        if (time() > strtotime($params['encode_time']) + 300) {
            return false;
        }

        $sign = $params['encode_sign'];
        unset($params['encode_sign']);

        ksort($params);
        $temp = http_build_query($params);
        if (md5($temp . $this->encodeKey) == $sign) {
            unset($params['encode_time']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 执行 GET 请求
     * 
     * @param   mixed   $ch     CURL handle
     *
     * @return  void
     */
    protected function executeGet($ch)
    {
        if (isset($this->requestBody)) {
            if (false === strpos($this->url, '?')) {
                 $this->url .= '?' . $this->requestBody;
            } else {
                if (substr($this->url, -1) == '?') {
                    $this->url .= $this->requestBody;
                } else {
                    $this->url .= '&' . $this->requestBody;
                }
            }
        }

        $this->doExecute($ch);
    }

    /**
     * 执行 POST 请求
     * 
     * @param   mixed   $ch     CURL handle
     *
     * @return  void
     */
    protected function executePost($ch)
    {
        if (!is_string($this->requestBody))
        {
            $this->buildPostBody();
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        curl_setopt($ch, CURLOPT_POST      , 1);
        
        $this->doExecute($ch);    
    }

    /**
     * 执行 PUT 请求
     * 
     * @param   mixed   $ch     CURL handle
     *
     * @return  void
     */
    protected function executePut($ch)
    {
        if (!is_string($this->requestBody))
        {
            $this->buildPostBody();
        }
        
        $this->requestLength = strlen($this->requestBody);
        
        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $this->requestBody);
        rewind($fh);
        
        curl_setopt($ch, CURLOPT_INFILE    , $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
        curl_setopt($ch, CURLOPT_PUT       , true);
        
        $this->doExecute($ch);
        
        fclose($fh);
    }

    /**
     * 执行 DELETE 请求
     * 
     * @param   mixed   $ch     CURL handle
     *
     * @return  void
     */
    protected function executeDelete($ch)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        
        $this->doExecute($ch);
    }

    /**
     * 执行请求
     * 
     * @param   mixed   $curlHandle CURL handle
     *
     * @return  void
     */
    protected function doExecute(&$curlHandle)
    {
        $this->setCurlOpts($curlHandle);
        $this->responseBody = curl_exec($curlHandle);
        $this->responseInfo = curl_getinfo($curlHandle);

        curl_close($curlHandle);
    }

    /**
     * 配置 CURL
     * 
     * @param   mixed   $curlHandle CURL handle
     *
     * @return  void
     */
    protected function setCurlOpts(&$curlHandle)
    {
        curl_setopt($curlHandle, CURLOPT_TIMEOUT       , $this->timeout);
        curl_setopt($curlHandle, CURLOPT_URL           , $this->url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER    , array('Accept: ' . $this->acceptType));
        //curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
    }

    /**
     * 配置 CURL 登录信息
     * 
     * @param   mixed   $curlHandle CURL handle
     *
     * @return  void
     */
    protected function setAuth(&$curlHandle)
    {
        if ($this->username !== null && $this->password !== null)
        {
            curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($curlHandle, CURLOPT_USERPWD , $this->username . ':' . $this->password);
        }
    }

    /**
     * 获取最大请求时间
     * 
     * @return  void
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * 设置最大请求时间
     *
     * @param   int     $timeout    最大请求时间
     * @return  void
     */
    public function setTimeout($timeout)
    {
        return $this->timeout = $timeout;
    }

    /**
     * 获取接受类型
     * 
     * @return  void
     */
    public function getAcceptType()
    {
        return $this->acceptType;
    } 

    /**
     * 设置接受类型
     * 
     * @param   string  $acceptType 接受类型
     *
     * @return  void
     */
    public function setAcceptType($acceptType)
    {
        $this->acceptType = $acceptType;
    }
    /**
     * 获取用户名
     * 
     * @return  void
     */
    public function getUsername()
    {
        return $this->username;
    } 

    /**
     * 设置用户名
     * 
     * @param   string  $username   用户名
     *
     * @return  void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * 获取密码
     * 
     * @return  void
     */
    public function getPassword()
    {
        return $this->password;
    } 

    /**
     * 设置密码
     * 
     * @param   string  $password   密码
     *
     * @return  void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * 获取响应内容
     * 
     * @return  void
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    } 

    /**
     * 获取响应信息
     * 
     * @return  void
     */
    public function getResponseInfo()
    {
        return $this->responseInfo;
    } 

    /**
     * 获取请求地址
     * 
     * @return  void
     */
    public function getUrl()
    {
        return $this->url;
    } 

    /**
     * 设置请求地址
     * 
     * @param   string  $url    请求地址
     *
     * @return  void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    } 

    /**
     * 获取 HTTP 方法名
     * 
     * @return  void
     */
    public function getVerb()
    {
        return $this->verb;
    } 

    /**
     * 设置 HTTP 方法名
     * 
     * @param   string  $verb   HTTP 方法名
     *
     * @return  void
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    } 
}