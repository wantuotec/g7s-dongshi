<?php
/**
 * 上传文件
 * 
 * @author      yankm
 * @date        2016-07-14
 * @category    Upload
 * @copyright   Copyright(c) 2016
 * @version     $Id:$
 */
class Upload extends CI_Controller
{
    /**
     * 上传文件-内容管理使用
     *
     * @access public
     *
     * @return void
     */
    public function index()
    {
        $params = $this->input->get();

        // 文件上一级目录(默认年月/自定义)
        $logic_type = isset($params['logic_type']) && !empty($params['logic_type']) ? $params['logic_type'].'/' : date('Ym').'/';

        // 根据业务来源区分存储的中间目录(兼容：默认内容管理public[公用的]目录)
        $origin_path = isset($params['origin_type']) && !empty($params['origin_type']) ? $params['origin_type'] : 'public';
        $image_path  = $origin_path . '/' . $logic_type;

        // 存储目录的公共路径
        $prefix_path = UPLOAD_IMG_DOMAIN;

        //文件保存目录URL
        $save_path = $prefix_path . $image_path;

        // 递归创建目录
        mkdir_recursive($save_path);

        // 上传配置
        $config['upload_path']   = $save_path;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|txt|pdf|xlsx|xls|docx|doc';
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('imgFile')) {
            $image_info = $this->upload->data();
            header('Content-type: text/html; charset=UTF-8');
            $cdn_domain = get_cdn_domain(ENVIRONMENT);
            $file_url = $cdn_domain . $image_path . $image_info['file_name'];
            echo json_encode(array('error' => 0, 'url' => $file_url,'file_name'=>$image_path . $image_info['file_name']));
            exit;
        } else {
            echo json_encode(array('error' => 1, 'message' => '附件上传失败' . $this->upload->display_errors()));
            exit;
        }
    }

    /**
     * 上传文件-配合多图插件使用，返回原图[及缩略图]完整地址
     *
     * @access public
     *
     * @return void
     */
    public function upload_image()
    {
        $params = $this->input->get();

        if (empty($params['origin_type'])) {
            echo json_encode(array('error' => 1, 'message' => '所传图片的类型为空'));
            exit;
        }

        // 将上传资料保存至服务器
        $file = [
            'action'   => $params['origin_type'],
            'filename' => 'imgFile',
        ];

        $this->load->model('Upload_model');
        $image_path = $this->Upload_model->upload_image($file);

        if (false == $image_path) {
            echo json_encode(array('error' => 1, 'message' => '附件上传失败' . $this->Upload_model->get_error()));
            exit;
        }

        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        if (is_array($image_path)) {
            $full_origin_url = $cdn_domain . $image_path['origin_path'];
            $full_thumb_url  = $cdn_domain . $image_path['thumb_path'];
            $thumb_url       = $image_path['thumb_path'];
            echo json_encode(array('error' => 0, 'full_origin_url' => $full_origin_url, 'full_thumb_url' => $full_thumb_url, 'thumb_url' => $thumb_url));
            exit;
        } else {
            $full_origin_url = $cdn_domain . $image_path;
            echo json_encode(array('error' => 0, 'full_origin_url' => $full_origin_url, 'origin_url' => $image_path));
            exit;
        }
    }
}