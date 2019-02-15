<?php
/**
 * 上传文件管理
 *
 * @author      liunian
 * @date        2016-03-20
 * @category    upload
 *
 * @version     $Id$
 */
class Upload_model extends CI_Model
{
    /**
     * 上传
     *
     * @param   array   $params     上传的数据
     *
     * @return  bool|array
     */
    public function upload_image($params = [])
    {
        data_filter($params);

        if (empty($params['image'])) {
            $this->set_error(47501);
            return false;
        }

        $service_info = [
            'service_name'   => 'base.upload.upload_image',
            'service_params' => [
                'image' => $params['image'],
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return [
            'image_url' => $result['data']['image_url'],
        ];
    }

    /**
     * 通过浏览器上传图片后，返回的信息
     *
     * @param   int     $errcode    错误代码
     * @param   array   $data       返回的内容
     * @param   string  $message    错误信息
     *
     * @return  void
     */
    private function __output($errcode = 0, $data = [], $message = '')
    {
        // 设置错误信息
        $this->load->library('error_list');
        $error_list = $this->error_list->error_list;
        $message = empty($message) ? (isset($error_list[$errcode]) ? $error_list[$errcode] : $errcode) : $message;
        unset($error_list);

        $this->_error = array(
            'success'        => 10000 == $errcode ? true : false,
            'errcode'        => $errcode,
            'message'        => $message,
            'data'           => $data,
        );

        header('Content-type: application/json');

        exit(json_encode($this->_error));
    }

    /**
     * 通过浏览器上传图片
     *
     * @param   array   $params     上传的数据
     *
     * @return  bool|array
     */
    public function upload_image_by_browser(array $params = [])
    {
        // 必要参数
        $params = [
            'auth'   => empty($params['auth'])   ? '' : $params['auth'],
            'action' => empty($params['action']) ? '' : $params['action'],
        ];

        // 允许的 action
        $allowed_actions = [
            'm_header',        // 用户头像
            'order_complaint', // 订单投诉图片
            'shop_apply',      // 店铺申请入驻
        ];

        // 配置：不需要在存储路径中自动加入 [年月日] 分类的action名单
        $month_action = [];

        // 配置：需要创建缩略图的action名单及尺寸 width*height,这里仅允许每种图片创建一种尺寸的缩略图
        $thumb_action = [
            'shop_apply' => [320, 194],
        ];

        // 认证检查
        if (empty($params['auth']) || '4acdd1136d69c86969a4d6b0a9bce483' != $params['auth']) {
            $this->__output(47503);
        }

        // action 检查
        if (empty($params['action']) || !in_array($params['action'], $allowed_actions)) {
            $this->__output(47504);
        }

        $prefix_path = '/data/apps/52xianmi.com/cdn/';
        // $prefix_path = 'E:/x_52xianmi/xianmi_cdn/'; // 本地测试
        $month_path  = isset($params['action']) && in_array($params['action'], $month_action) ? '' : date("Ymd") . '/';
        $middle_path = 'upload/' . $params['action'] . '/' . $month_path;
        $upload_path = $prefix_path . $middle_path;
        $file_name   = md5(uniqid('', true));

        // 递归创建目录
        mkdir_recursive($upload_path);

        // 执行上传操作
        $this->load->library('upload');
        $this->upload->initialize([
            'max_size'      => 5120, // 单位 KB
            'allowed_types' => 'gif|jpg|png|jpeg',
            'upload_path'   => $upload_path,
            'file_name'     => $file_name,
        ]);

        if (!$this->upload->do_upload('userfile')) {
            $error = implode(',', $this->upload->error_msg);
            $this->__output(47502, [], '上传图片失败,' . $error);
        } else {
            $result = $this->upload->data();

            if (empty($result['orig_name'])) {
                $this->__output(47502, [], '上传图片失败,orig_name不存在');
            }

            //----------创建图片缩略图(压缩)---------
            if (in_array($params['action'], array_keys($thumb_action))) {
                $this->load->library('image_lib');

                $thumb_size_config        = $thumb_action[$params['action']];   // 压缩图尺寸配置(每张图片会生成这些尺寸的等比压缩图,单位：px)
                $config['source_image']   = $result['full_path'];               // 原图路径
                $config['create_thumb']   = TRUE;                               // 是否生成缩略图
                $config['maintain_ratio'] = false;                              // 是否等比缩放
                $config['quality']        = 90;                                 // 图像品质(1-100)
                $config['width']          = $thumb_size_config[0];              // 缩略图宽度
                $config['height']         = $thumb_size_config[1];              // 缩略图高度
                $config['thumb_marker']   = '_' . $thumb_size_config[0] . 'x' . $thumb_size_config[1]; // 缩略图文件后缀

                mkdir_recursive($config['new_image']);                          // 创建保存缩略图的目录
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()){
                    echo $this->image_lib->display_errors();
                }

                // 同时返回缩略图和原图路径
                $image_url      = $middle_path . $result['raw_name'] . $config['thumb_marker'] . $result['file_ext'];
                $full_image_url = $middle_path . $result['orig_name'];
                $this->__output(10000, ['image_url' => $image_url, 'full_image_url' => $full_image_url]);
            }
            // ------------创建缩略图结束------------

            $this->__output(10000, ['image_url' => $middle_path . $result['orig_name']]);
        }
    }

}