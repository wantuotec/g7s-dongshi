<?php
/**
 * openssl加解密
 * Created by niezuxue.
 * Date: 2016/11/7
 * Time: 13:52
 */

class CI_Rsa
{
    private $error          = null;  // 错误

    private $private_file   = '';  // 私钥文件路径

    private $public_file    = '';  // 公钥文件路径

    function __construct()
    {
        $this->private_file = BASEPATH . 'include/https/rsa_private_key.pem';
        $this->public_file  = BASEPATH . 'include/https/rsa_public_key.pem';
    }

    /**
     * 设置私钥文件路径
     *
     * @param   string  $private_file  私钥文件路径
     *
     * @return  void|false
     */
    public function set_private_file($private_file)
    {
        if (empty($this->private_file)) {
            $this->set_error('RSA证书文件路径不能为空');
            return false;
        }

        if (!file_exists($this->private_file)) {
            $this->set_error('RSA证书不存在');
            return false;
        }

        $this->private_file = $private_file;
    }

    /**
     * 设置公钥文件路径
     *
     * @param   string  $public_file  公钥文件路径
     *
     * @return  void|false
     */
    public function set_public_file($public_file)
    {
        if (empty($this->public_file)) {
            $this->set_error('RSA证书文件路径不能为空');
            return false;
        }

        if (!file_exists($this->public_file)) {
            $this->set_error('RSA证书不存在');
            return false;
        }

        $this->public_file = $public_file;
    }

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    private function set_error($error)
    {
        $this->error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->error;
    }

    /**
     * 获取私钥
     *
     * @return  bool|resource
     */
    private function get_private_key()
    {
        if (!file_exists($this->private_file)) {
            $this->set_error('RSA证书不存在');
            return false;
        }

        $private_key_string = file_get_contents($this->private_file);
        if (false === $private_key_string) {
            $this->set_error('读取RSA证书失败');
            return false;
        }

        $private_key = openssl_pkey_get_private($private_key_string);
        if (false === $private_key) {
            $this->set_error('获取RSA证书key失败');
            return false;
        }

        return $private_key;
    }

    /**
     * 获取公钥
     *
     * @return  bool|resource
     */
    private function get_public_key()
    {
        if (!file_exists($this->public_file)) {
            $this->set_error('RSA证书不存在');
            return false;
        }

        $public_key_string = file_get_contents($this->public_file);
        if (false === $public_key_string) {
            $this->set_error('读取RSA证书失败');
            return false;
        }

        $public_key = openssl_pkey_get_public($public_key_string);
        if (false === $public_key) {
            $this->set_error('获取RSA证书key失败');
            return false;
        }

        return $public_key;
    }

    /**
     * 用私钥来解密
     *
     * @param   string  $params         参数
     * @param   bool    $base64_decode  是否需要base64解码
     * @param   bool    $url_decode     是否需要url解码
     *
     * @return  bool|string
     */
    public function decrypt_private($params, $base64_decode = false, $url_decode = false)
    {
        if (!is_string($params)) {
            $this->set_error('传入加解密方法中参数必须为字符串');
            return false;
        }

        $private_key = $this->get_private_key();  // 私钥
        if (false === $private_key) {
            return false;
        }

        // 请求过程中的所有参数都必须以键值对的形式，现在的请求只有值。所以这里删掉自动加上的在值前面的"="
        $params = ltrim($params, '=');

        $url_decode    && $params = urldecode($params);
        $base64_decode && $params = base64_decode($params);

        $slices = str_split($params, 128);
        $result = '';
        foreach ($slices as $slice) {
            $decrypt_result = openssl_private_decrypt($slice, $slice_result, $private_key);
            if (false === $decrypt_result) {
                $this->set_error('获取请求参数失败');
                return false;
            }
            $result .= $slice_result;
        }

        openssl_free_key($private_key);

        return $result;
    }

    /**
     * 用私钥来加密
     *
     * @param   string  $params         参数
     * @param   bool    $base64_encode  是否需要base64编码
     * @param   bool    $url_encode     是否需要url编码
     *
     * @return  string
     */
    public function encrypt_private($params, $base64_encode = false, $url_encode = false)
    {
        if (!is_string($params)) {
            $this->set_error('传入加解密方法中参数必须为字符串');
            return false;
        }

        $private_key  = $this->get_private_key();
        if (false === $private_key) {
            return false;
        }

        $slices = str_split($params, 117);
        $result = '';
        foreach ($slices as $slice) {
            // 用私钥加密
            $encrypt_result = openssl_private_encrypt($slice, $slice_result, $private_key);
            if (false === $encrypt_result) {
                $this->set_error('发送响应数据失败');
                return false;
            }

            $result .= $slice_result;
        }

        openssl_free_key($private_key);

        $base64_encode && $result = base64_encode($result);
        $url_encode    && $result = urlencode($result);

        return $result;
    }


    /**
     * 用 公钥 来解密
     *
     * @param   string  $params         参数
     * @param   bool    $base64_decode  是否需要base64解码
     * @param   bool    $url_decode     是否需要url解码
     *
     * @return  bool|string
     */
    public function decrypt_public($params, $base64_decode = false, $url_decode = false)
    {
        if (!is_string($params)) {
            $this->set_error('传入加解密方法中参数必须为字符串');
            return false;
        }

        $public_key = $this->get_public_key();  // 公钥
        if (false === $public_key) {
            return false;
        }

        $url_decode    && $params = urldecode($params);
        $base64_decode && $params = base64_decode($params);

        $slices = str_split($params, 128);
        $result = '';
        foreach ($slices as $slice) {
            $decrypt_result = openssl_public_decrypt($slice, $slice_result, $public_key);
            if (false === $decrypt_result) {
                $this->set_error('公钥获取请求参数失败');
                return false;
            }
            $result .= $slice_result;
        }

        openssl_free_key($public_key);

        return $result;
    }

    /**
     * 用 公钥 来加密
     *
     * @param   string  $params         参数
     * @param   bool    $base64_encode  是否需要base64编码
     * @param   bool    $url_encode     是否需要url编码
     *
     * @return  string
     */
    public function encrypt_public($params, $base64_encode = false, $url_encode = false)
    {
        if (!is_string($params)) {
            $this->set_error('传入加解密方法中参数必须为字符串');
            return false;
        }

        $public_key  = $this->get_public_key();
        if (false === $public_key) {
            return false;
        }

        $slices = str_split($params, 117);
        $result = '';
        foreach ($slices as $slice) {
            // 用私钥加密
            $encrypt_result = openssl_public_encrypt($slice, $slice_result, $public_key);
            if (false === $encrypt_result) {
                $this->set_error('公钥发送响应数据失败');
                return false;
            }

            $result .= $slice_result;
        }

        openssl_free_key($public_key);

        $base64_encode && $result = base64_encode($result);
        $url_encode    && $result = urlencode($result);

        return $result;
    }

    /**
     * openssl error
     */
    private function get_openssl_error()
    {
        while($msg = openssl_error_string())
            add_txt_log($msg);

    }
}