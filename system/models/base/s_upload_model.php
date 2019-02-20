<?php
/**
 * 上传图片
 *
 * @author       liunian
 * @date         2016-03-20
 * @category     upload_model
 * @copyright    Copyright(c) 2016
 *
 * @version      $Id$
 */
class S_upload_model extends CI_Model
{
    /**
     * 上传图片
     *
     * @param   array   $params     上传的数据
     *
     * @return  array|bool
     */
    public function upload_image($params = [])
    {
        data_filter($params);

        // 必要参数
        if (empty($params['image'])) {
            $this->set_error(47501);
            return false;
        }

        // 不需要在存储路径中自动加入 年月日 分类的action名单
        $month_action = [ 'activity', ];

        //创建图片存放目录
        $clock        = '/data/apps/52xianmi.com/cdn/upload/';

        // 如果传了action,则拼接此自定义存储路径，否则使用默认的 images
        $action = isset($params['action']) && !empty($params['action']) ? $params['action'] : 'images';

        $month        = isset($params['action']) && in_array($params['action'], $month_action) ? '' : date("Ymd") . '/';
        $picture_path = $clock . $action . '/' . $month . '/';
        mkdir_recursive($picture_path);

        //生成图片名称
        $rand_number    = rand(10000, 99999);
        $portrait_name  = time() . microtime(true) . $rand_number . ".jpg";
        // 图片在服务器上存放的路径
        $image_path_url = $picture_path . $portrait_name;

        //图片过滤
        $image = str_replace("\u003d", "=", $params['image']);
        $image = stripslashes($image);

        //图片解码
        $image = base64_decode($image);

        //图片保存
        $result = file_put_contents($image_path_url, $image);
        if (false === $result) {
            $this->set_error(47502);
            return false;
        }

        $image_url = 'upload/' . $action . '/' . $month . $portrait_name;

        return ['image_url' => $image_url];
    }

    /**
     * 把外网的图片拉到本地
     *
     * @param   array   $params     上传图片数据
     *
     * @return  bool|array
     */
    public function upload_image_url($params = [])
    {
        // 必要参数
        $params = [
            'image_url' => isset($params['image_url']) ? $params['image_url'] : '',
            'action'    => isset($params['action'])    ? $params['action']    : '',
        ];

        // 允许的 action
        $allowed_actions = [
            'm_header', // 鲜米用户头像
        ];

        // action 检查
        if (empty($params['action']) || !in_array($params['action'], $allowed_actions)) {
            $this->set_error(47504);
            return false;
        }

        $prefix_path   = '/data/apps/52xianmi.com/cdn/';
        // $prefix_path = 'E:/52xianmi/52xianmi_cdn/'; // 本地测试
        $middle_path   = 'upload/' . $params['action'] . '/' . date('Ymd') . '/';
        $upload_path   = $prefix_path . $middle_path;
        $file_name     = md5(uniqid('', true));
        $filename_path = $upload_path . $file_name . '.jpg';

        // 递归创建目录
        mkdir_recursive($upload_path);

        $image_result = $this->get_origin_image($params['image_url'], $filename_path, 1);
        if (false == $image_result) {
            $this->set_error($this->get_error());
            return false;
        }

        return [
            'image_path' => $middle_path . $file_name . '.jpg',
        ];
    }

    /**
     * 把网络上面的图片获取到本地
     *
     * @param   string  $url        图片地址
     * @param   string  $filename   图片名称
     * @param   int     $type       走的类型
     *
     * @return  bool|array
     */
    public function get_origin_image($url = '', $filename = '', $type = 0) {
        if ('' == $url) {
            $this->set_error(47501);
            return false;
        }

        if ('' == $filename) {
            $this->set_error(47502);
            return false;
        }

        //文件保存路径
        if ($type > 0) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $img = curl_exec($ch);
            curl_close($ch);

        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }

        $size = strlen($img);
        //文件大小
        $fp2 = @fopen($filename, 'a');
        fwrite($fp2, $img);
        fclose($fp2);

        return [
            'filename' => $filename,
            'url'      => $url,
        ];
    }
}