<?php
 /**
 * 上传
 *
 * @author      liunian
 * @date        2015-02-03
 * @category    Upload_model.php
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class Upload_model extends CI_Model
{
    /**
     * 上传图片
     *
     * @param   array   $params     上传的数据
     *
     * @return  bool|array
     */
    public function upload_image(array $params = [])
    {
        // 必要参数
        $params = [
            'action'     => empty($params['action'])     ? '' : $params['action'],     // 图片所属项目(如product、activity...)
            'filename'   => empty($params['filename'])   ? '' : $params['filename'],   // 原图文件
            'logic_type' => empty($params['logic_type']) ? '' : $params['logic_type'], // 自定义图片的上级目录(即:upload/(年月)/自定义目录/文件 如:user22)
        ];

        // 配置：允许的 action
        $allowed_actions = [
            'article',  // 文章图片
            'photos' ,  // 相册照片
        ];

        // 限制：后台上传原图不能大于100KB/张
        // if ($_FILES[$params['filename']]['size'] > 100*1024) {
        //     $this->set_error('原图文件不能大于100KB，请控制大小后上传');
        //     return false;
        // }

        // 配置：不需要在存储路径中自动加入 [年月] 分类的action名单
        $month_action = [];

        // 配置：需要去生成缩略图的 action
        $need_thumb_action = [];

        // action 检查
        if (empty($params['action']) || !in_array($params['action'], $allowed_actions)) {
            $this->set_error('没有此操作');
            return false;
        }

        // 定义图片地址主路径
        $prefix_path = UPLOAD_IMG_DOMAIN;

        // 年月目录
        $month_path  = isset($params['action']) && in_array($params['action'], $month_action) ? '' : date("Ym") . '/';
        // 用户自定义上级目录
        $logic_type  = isset($params['logic_type']) && !empty($params['logic_type']) ? $params['logic_type'] . '/' : '';
        // 拼接图片路径
        $middle_path = $params['action'] . '/' . $month_path . $logic_type;

        $upload_path = $prefix_path . $middle_path;
        $file_name   = md5(uniqid('', true));

        // 递归创建目录
        mkdir_recursive($upload_path);

        // 执行上传操作
        $this->load->library('upload');
        $this->upload->initialize([
            'max_size'      => 5120, // 单位 KB
            'allowed_types' => 'gif|jpg|png',
            'upload_path'   => $upload_path,
            'file_name'     => $file_name,
        ]);


        if (!$this->upload->do_upload($params['filename'])) {
            $this->set_error('上传图片失败,' . $this->upload->error_msg);
            return false;
        } else {
            $result = $this->upload->data();

            if (empty($result['orig_name'])) {
                $this->set_error('上传图片失败,orig_name不存在');
                return false;
            }

            //----------创建图片缩略图(压缩)---------
            if (in_array($params['action'], $need_thumb_action)) {
                $this->load->library('image_lib');

                // 配置：每种图片需要的尺寸列表 width*height px
                $thumb_action = [
                    // 'market' => [[118,86]]
                ];

                $thumb_size_config = $thumb_action[$params['action']];  // 压缩图尺寸配置(每张图片会生成这些尺寸的等比压缩图,单位：px)
                if (0 == count($thumb_size_config)) {
                    $this->set_error('请配置需要生成缩略图的尺寸');
                    return false;
                }

                $config['source_image']   = $result['full_path'];              // 原图路径
                $config['create_thumb']   = TRUE;                              // 是否生成缩略图
                $config['maintain_ratio'] = false;                             // 是否等比缩放
                $config['quality']        = 80;                                // 图像品质(1-100)

                foreach ($thumb_size_config as $size) {
                    $config['width']        = $size[0];                        // 缩略图宽度
                    $config['height']       = $size[1];                        // 缩略图高度
                    $config['thumb_marker'] = '_' . $size[0] . 'x' . $size[1]; // 缩略图文件后缀

                    mkdir_recursive($config['new_image']);                     // 创建保存缩略图的目录
                    $this->image_lib->initialize($config);

                    if (!$this->image_lib->resize()){
                        echo $this->image_lib->display_errors();
                    }
                }

                // 只有一种缩略图尺寸需求的，返回缩略图及原图url
                if (1 == count($thumb_size_config)) {
                    return [
                        'thumb_path'  => $middle_path . $result['raw_name'] . $config['thumb_marker'] . $result['file_ext'],
                        'origin_path' => $middle_path . $result['orig_name']
                    ];

                // 有多个缩略图尺寸需求的，仅返回原图url
                } else if (1 < count($thumb_size_config)) {
                    return $middle_path . $result['orig_name'];
                }
            }

            // 返回图片信息
            return $middle_path . $result['orig_name'];
        }
    }
}