<?php

class CI_Socket
{
    private $_fp      = null;
    private $_timeout = 0;
    private $_errno   = 0;
    private $_error   = '';

    private $_response_header = '';
    private $_response_body   = '';

    /**
     * Constructor
     *
     * @param  int
     * @return object
     */
    public function __construct($config = array())
    {
        $this->_timeout = (isset($config['timeout'])) ? intval($config['timeout']) : 30;
    }

    /**
     * Initialize
     *
     * @param  string
     * @return void
     */
    private function _initialize($host)
    {
        $this->_fp = fsockopen(
            $host,
            80,
            $this->_errno,
            $this->_error,
            $this->_timeout
        );
    }

    /**
     * Parse a URL
     *
     * @param  string
     * @return bool
     */
    private function _parse($url)
    {
        $result = parse_url($url);
        if (!isset($result['path'])) {
            $result['path'] = '/';
        }
        if (!isset($result['query'])) {
            $result['query'] = '';
        }
        return $result;
    }

    /**
     * Fetch errno
     *
     * @return int
     */
    public function get_errno()
    {
        return $this->_errno;
    }

    /**
     * Fetch error
     *
     * @return string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * Send a request
     *
     * @param  string
     * @return bool
     */
    private function _request($method = 'GET', $url, $params = array())
    {
        $part = $this->_parse($url);
        if ($part === false) {
            return false;
        }

        $this->_initialize($part['host']);
        if ($this->_fp === false) {
            return false;
        }

        $params = http_build_query($params);
        
        // send
        if (strtoupper($method) === 'POST') {
            fwrite($this->_fp, "POST {$part['path']} HTTP/1.1\r\n");
            fwrite($this->_fp, "Host: {$part['host']}\r\n");
            fwrite($this->_fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fwrite($this->_fp, 'Content-Length: '.strlen($params)."\r\n");
        } else {
            fwrite($this->_fp, "GET {$part['path']}?{$part['query']} HTTP/1.1\r\n");
            fwrite($this->_fp, "Host: {$part['host']}\r\n");
        }
        fwrite($this->_fp, "Connection: Close\r\n\r\n");
        if (strtoupper($method) === 'POST') {
            fwrite($this->_fp, $params."\r\n");
        }

        $result = '';
        while (!feof($this->_fp)) {
            $result .= fgets($this->_fp);
        }

        list($this->_response_header, $this->_response_body) = preg_split('/\r\n\r\n|\n\n|\r\r/', $result, 2);

        fclose($this->_fp);
    }

    /**
     * Send a get request
     *
     * @param  string
     * @return bool
     */
    public function get($url)
    {
        return $this->_request('GET', $url);
    }

    /**
     * Send a post request
     *
     * @param  string
     * @param  array
     * @return bool
     */
    public function post($url, $params = array())
    {
        return $this->_request('POST', $url, $params);
    }

    /**
     * Fetch response
     *
     * @param  bool
     * @return mixed
     */
    public function get_response($with_header = false)
    {
        if ($with_header === true) {
            return array($this->_response_header, $this->_response_body);
        } else {
            return $this->_response_body;
        }
    }

}