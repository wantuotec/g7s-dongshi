<?php
/**
 * 百度天气
 *
 * @author      yankm
 * @date        2016-05-21
 * @copyright   Copyright(c) 2016
 * @version     $Id$
 */
class CI_Baiduweather {
    // 密钥
    private $__ak = 'M5nqXKNwSugTNrUYBx09jf67';

    // 错误信息
    protected $_error    = null;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI =& get_instance();
        return $CI->$key;
    }

    /**
     * 使用 curl 发起网络请求
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string/false
     */
    private function __curl($url, $params = array(), $method = 'GET', $timeout = 30)
    {
        $data = '';
        if (!empty($params) && is_array($params)) {
            $data = http_build_query($params);
        }

        $method = strtoupper($method);

        $ch = curl_init();

        if ('GET' == $method) {
            $url .= empty($data) ? '' : '?' . $data;
        } else {
            if(empty($data)) {
                $this->set_error('no data');
                return false;
            }

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 设置最大连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout * 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code != 200){ //数据未完整读取
            $this->set_error('请求失败，http状态码为' . $http_code . ' 数据为：' . $response);
            return false;
        }

        return $response;
    }

    /**
     * 请求网络并处理结果
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string/false
     */
    private function __request($url, $params = array(), $method = 'GET', $timeout = 30)
    {   
        $result = $this->__curl($url, $params, $method, $timeout);
        if (false === $result) {
            return false;
        }

        $result = json_decode($result, true);

        if (empty($result) || !is_array($result)) {
            $this->set_error('不是json格式');
            return false;
        }

        // 百度地图 status为 0 表示正常，其它表示错误
        // if (0 !== $result['status']) {
        //     $this->set_error('百度地图有误status为' . $result['status']);
        //     return false;
        // }
        // 本来是打算直接返回 结果里的格式不固定，只有status是一直存在的。
        return $result;
    }

    /**
     * GCJ02 坐标转 BD09 坐标
     *
     * @param   float   $latitude   纬度
     * @param   float   $longitude  经度
     *
     * @return  bool|array
     */
    public function GCJ02_to_BD09($latitude, $longitude)
    {
        $url = "http://api.map.baidu.com/geoconv/v1/?coords={$longitude},{$latitude}&from=3&to=5&ak={$this->__ak}&output=json";

        $result = $this->__request($url);

        if (false === $result) {
            return false;
        } else {
            return array(
                'latitude'  => $result['result'][0]['y'],
                'longitude' => $result['result'][0]['x'],
            );
        }
    }

    /**
     * 其它坐标转 BD09 坐标
     *
     * @param   float   $latitude   纬度
     * @param   float   $longitude  经度
     * @param   int     $from       坐标系代码
     * @param   int     $to         坐标系代码
     *
     * @return  bool|array
     */
    public function geoconv($latitude, $longitude, $from = 3, $to = 5)
    {
        $url = "http://api.map.baidu.com/geoconv/v1/?coords={$longitude},{$latitude}&from={$from}&to={$to}&ak={$this->__ak}&output=json";

        $result = $this->__request($url);

        if (false === $result) {
            return false;
        } else {
            return array(
                'latitude'  => $result['result'][0]['y'],
                'longitude' => $result['result'][0]['x'],
            );
        }
    }

    /**
     * 把地址信息转换成百度经纬度
     *
     * @param   string   $address   具体位置
     * @param   string   $city      具体城市名字
     *
     * @return  bool|array
     */
    public function address_to_location($address, $city = '')
    {
        $url = "http://api.map.baidu.com/geocoder/v2/?address={$address}&ak={$this->__ak}&output=json&city={$city}";

        $result = $this->__request($url);

        if (false === $result) {
            return false;
        } else {
            return array(
                'latitude'  => $result['result']['location']['lat'],
                'longitude' => $result['result']['location']['lng'],
            );
        }
    }

    /**
     * 通过地址查到天气
     *
     * @param   float   $latitude     坐标经度
     * @param   float   $longitude    坐标纬度
     *
     * @return  array
     */
    public function getweather($latitude,$longitude)
    {
        if(empty($latitude)){
            return '';
        }
        if(empty($longitude)){
            return '';
        }
//http://api.map.baidu.com/telematics/v3/weather?location=121.398953,31.240832&output=json&ak=M5nqXKNwSugTNrUYBx09jf67
        $url = "http://api.map.baidu.com/telematics/v3/weather?location=$longitude,$latitude&output=json&ak=M5nqXKNwSugTNrUYBx09jf67";
        $result = $this->__request($url);
//dump($result);
        return ($result['results'][0]['weather_data'][0]['weather']);
    }
}
