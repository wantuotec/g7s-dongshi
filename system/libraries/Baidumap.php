<?php
/**
 * 百度地图
 *
 * @author      杨海波
 * @date        2015-06-10
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class CI_Baidumap {
    // 密钥
    private $__ak     = 'M5nqXKNwSugTNrUYBx09jf67';

    // 错误信息
    protected $_error = null;

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
        if (0 !== $result['status']) {
            $this->set_error('百度地图有误status为' . $result['status']);
            return false;
        }

        // 本来是打算直接返回 结果里的格式不固定，只有status是一直存在的。
        return $result;
    }

    /**
     * 获得配置的 ak (供 JS SDK 使用)
     *
     * @return  string
     */
    public function get_ak()
    {
        return $this->__ak;
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
        $address = urlencode($address);
        $city    = urlencode($city);

        $url = "http://api.map.baidu.com/geocoder/v2/?address={$address}&ak={$this->__ak}&output=json&city={$city}";
        $result = $this->__request($url);

        if ($result['status'] !== 0) {
            return false;
        } else {
            return array(
                'latitude'  => $result['result']['location']['lat'],
                'longitude' => $result['result']['location']['lng'],
                'level'     => $result['result']['level']
            );
        }
    }

    /**
     * 在地址前面加上城市名称，再查询经纬度
     *
     * @param   string  $address    地址
     * @param   string  $city       城市名称
     *
     * @return  array|bool  经纬度
     */
    private function get_location_after_add_city_name($address, $city)
    {
        $address_more = self::add_city_name_to_address($address, $city);  // 在具体地址前面加上城市名称

        // 表示有加上了城市名称，则继续查询经纬度
        if ($address != $address_more) {
            $position = $this->address_to_location($address_more, $city);

            if(in_array($position['level'], self::get_imprecise_levels())) {  // 能解析，但level为 城市 区县 UNKNOWN，表示没有找到精确位置或有错误。报错
                return false;
            } else if (false !== $position && intval($position['latitude']) != 0 && intval($position['longitude']) != 0) {  // 获取到的经纬度没问题，再继续验证城市名称
                $city_name = $this->get_city_name_by_gbs($position['latitude'], $position['longitude']);  // 再查城市名称

                // 如果不属于当前城市。报错
                if ($city != $city_name) {
                    return false;
                } else { // 返回最终经纬度
                    return $position;
                }
            } else {  // 加上城市之后的查询，仍然找不到经纬度，报错
                return false;
            }
        } else {  // 源地址中已经包含城市名，不用再查询，报错
            return false;
        }
    }

    /**
     * 返回不精确的level
     *
     * @return  array
     */
    private static function get_imprecise_levels()
    {
        return ['城市', '区县', 'UNKNOWN'];
    }

    /**
     * 把地址信息转换成百度经纬度  （查询出来的经纬度已经验证了是否属于当前城市）
     * 比address_to_location方法更准确
     *
     * @param   string  $address  具体地址
     * @param   string  $city     城市名称
     *
     * @return  array
     */
    public function address_to_location_validated($address, $city = '')
    {
        $position = $this->address_to_location($address, $city);

        if(in_array($position['level'], self::get_imprecise_levels())) {  // 能解析，但level为 城市 区县 UNKNOWN，表示没有找到精确位置或有错误。报错
            return false;
        } else if (false === $position || intval($position['latitude']) == 0 || intval($position['longitude']) == 0) {  // 不能解析，加上城市名称再次查询
            return $this->get_location_after_add_city_name($address, $city);
        } else {    // 解析出来的经纬度认为是正确的，再查询城市名称
            $city_name = $this->get_city_name_by_gbs($position['latitude'], $position['longitude']);

            // 如果不属于当前城市。加上城市名称再次验证
            if ($city != $city_name) {
                return $this->get_location_after_add_city_name($address, $city);
            } else {  // 查询出来的经纬度为当前城市，返回最终经纬度
                return $position;
            }
        }
    }

    /**
     * 在地址前加上城市名称
     *
     * @param   string  $address  具体地址
     * @param   string  $city     城市名称
     *
     * @return  array
     */
    private static function add_city_name_to_address($address, $city)
    {
        // 在address变量前加上城市名称，为了解析更准确
        $city_single = rtrim($city, '市');
        if (!empty($city) && 0 !== stripos($address, $city_single)) {
            $address = $city_single . '市' . $address;
        }

        return $address;
    }

    /**
     * 经纬度反查地址
     *
     * @param   float   $latitude     坐标经度
     * @param   float   $longitude    坐标纬度
     *
     * @return  array
     */
    public function geocoder($latitude = '', $longitude = '')
    {   
        if(empty($latitude) || empty($longitude)){
            return '';
        }

        $url = "http://api.map.baidu.com/geocoder/v2/?renderReverse&location=" . $latitude . "," . $longitude . "&output=json&pois=1&ak={$this->__ak}";
        $result = $this->__request($url);

        return empty($result['result']['formatted_address']) ? '' : $result['result']['formatted_address'];
    }

    /**
     * 圆形区域检索参数
     *
     * @param   string   $address   具体位置
     * @param   string   $city      具体城市名字
     *
     * @return  bool|array
     */
    public function place_search_nearby($params = [])
    {   
        $params = [
            'query'    => urlencode($params['query']),
            'location' => $params['location'],
            'radius'   => empty($params['radius']) ? '' : intval($params['radius']),
        ];

        $url = "http://api.map.baidu.com/place/v2/search?query={$params['query']}&location={$params['location']['latitude']},{$params['location']['longitude']}&radius={$params['radius']}&output=json&page_size=20&ak={$this->__ak}";
        $result = $this->__request($url);

        return $result;
    }

    /**
     * 城市内检索
     *
     * @param   string   $address   具体位置
     * @param   string   $city      具体城市名字
     *
     * @return  bool|array
     */
    public function place_search_city($params = [])
    {   
        $params = [
            'query'  => urlencode($params['query']),
            'region' => urlencode($params['region']),
        ];

        $url = "http://api.map.baidu.com/place/v2/search?query={$params['query']}&page_size=20&page_num=0&scope=1&region={$params['region']}&output=json&ak={$this->__ak}";
        $result = $this->__request($url);

        return $result;
    }

    /**
     * 根据关键词推荐地址
     *
     * @param   string   $address   具体位置
     * @param   string   $city      具体城市名字
     *
     * @return  bool|array
     */
    public function suggestion($params = [])
    {   
        $params = [
            'query'  => urlencode($params['query']),
            'region' => urlencode($params['region']),
        ];

        // 推荐的地址点
        $places = [];



        // 城市内检索
        $result = $this->place_search_city([
            'query'  => urldecode($params['query']),
            'region' => urldecode($params['region']),
        ]);

        if (0 === $result['status']) {
            if (!empty($result['results']) && is_array($result['results'])) {
                foreach ($result['results'] as $v) {
                    if (isset($v['location']['lat']) && isset($v['location']['lng'])) {
                        $places[] = [
                            'name'     => $v['name'],
                            'location' => $v['location'],
                            'address'  => $v['address'],
                            'telephone' => $v['telephone'],
                            'uid'      => $v['uid'],
                        ];
                    }
                }
            }
        }

        // 按第一个坐标点再找附近的点
        if (!empty($places) && is_array($places)) {
            $gps = null;

            // 找到一个有经纬度的为止
            foreach ($places as $k => $v) {
                if (isset($v['location']['lat']) && isset($v['location']['lng'])) {
                    $gps = array(
                        'latitude'  => $v['location']['lat'],
                        'longitude' => $v['location']['lng'],
                    );
                } else {
                    $rs = $this->address_to_location($v['name'], urldecode($params['region']));
                    if (isset($rs['latitude']) && isset($rs['longitude'])) {
                        $gps = $rs;
                    }
                }

                if (!empty($gps)) {
                    break;
                }
            }

            // 查找附近地点
            $result = $this->place_search_nearby([
                'query'    => urldecode($params['query']),
                'location' => $gps,
                'radius'   => 5000,
            ]);

            if (0 === $result['status']) {
                if (!empty($result['results']) && is_array($result['results'])) {
                    foreach ($result['results'] as $v) {
                        if (isset($v['location']['lat']) && isset($v['location']['lng'])) {
                            $places[] = [
                                'name'      => $v['name'],
                                'location'  => $v['location'],
                                'address'   => $v['address'],
                                'telephone' => $v['telephone'],
                                'uid'       => $v['uid'],
                            ];
                        }
                    }
                }
            }
        }

        // 百度推荐的地点
        $url = "http://api.map.baidu.com/place/v2/suggestion?query=" . $params['query'] . "&region=" . $params['region'] . "&output=json&ak={$this->__ak}";
        $result = $this->__request($url);

        if (0 === $result['status']) {
            if (!empty($result['result']) && is_array($result['result'])) {
                foreach ($result['result'] as $v) {
                    if (isset($v['location']['lat']) && isset($v['location']['lng'])) {
                        $places[] = [
                            'name'     => $v['name'],
                            'location' => $v['location'],
                            'address'  => $v['city'] . $v['district'],
                            'telephone' => $v['telephone'],
                            'uid'      => $v['uid'],
                        ];
                    }
                }
            }
        }

        // 把uid去掉
        return array_values($places);
    }

    /**
     * 根据纬经度查询所在城市名称
     *
     * @param   float   $latitude     坐标纬度
     * @param   float   $longitude    坐标经度
     *
     * @return  string
     */
    public function get_city_name_by_gbs($latitude = '', $longitude = '')
    {   
        if(empty($latitude) || empty($longitude)){
            return '';
        }

        $url = "http://api.map.baidu.com/geocoder/v2/?renderReverse&location=" . $latitude . "," . $longitude . "&output=json&pois=1&ak={$this->__ak}";
        $result = $this->__request($url);

        return empty($result['result']['addressComponent']['city']) ? '' : $result['result']['addressComponent']['city'];
    }


    /**
     * 根据纬经度查询省-市-区地址信息
     *
     * @param   float   $latitude     坐标纬度
     * @param   float   $longitude    坐标经度
     *
     * @return  string
     */
    public function get_address_detail_by_gbs($latitude = '', $longitude = '')
    {   
        if(empty($latitude) || empty($longitude)){
            return '';
        }

        $url = "http://api.map.baidu.com/geocoder/v2/?renderReverse&location=" . $latitude . "," . $longitude . "&output=json&pois=1&ak={$this->__ak}";
        $result = $this->__request($url);

        return empty($result['result']['addressComponent']) ? '' : $result['result']['addressComponent'];
    }
}