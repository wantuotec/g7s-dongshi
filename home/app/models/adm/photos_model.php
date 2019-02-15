<?php
/**
 * 照片管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    photos_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Photos_model extends CI_Model
{
    /**
     * 获取照片列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_photos_list($params = [])
    {
        data_filter($params);

        // 点赞排序
        $order_by = 'create_time DESC';
        if (!empty($params['order_by_like_num'])) {
            if (1 == $params['order_by_like_num']) {
                $order_by = 'like_num ASC';
            } else {
                $order_by = 'like_num DESC';
            }
        }

        $service_params = [
            'service_name'   => 'photos.photos.get_list',
            'service_params' => [
                'is_pages'        => true,
                'photos_album_id' => filter_empty('photos_album_id', $params),
                'is_enabled'      => filter_empty('is_enabled', $params),
                'like'            => ['photo_title' => !empty($params['like_photo_title']) ? $params['like_photo_title'] : null],
                'order_by'        => $order_by,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        // 处理照片地址
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$photo) {
                $photo['photo_url'] = $cdn_domain . $photo['photo_url'];
                $photo['date']      = date('Y-m-d', strtotime($photo['create_time']));
            }
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单个照片信息
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_photos($params = [])
    {
        data_filter($params);

        if (empty($params['photos_id'])) {
            $this->set_error('照片ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'photos.photos.get',
            'service_params' => [
                'photos_id' => filter_empty('photos_id', $params),
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        // 处理封面图
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        $result['data']['full_photo_url'] = $cdn_domain . $result['data']['photo_url'];

        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }

    /**
     * 添加照片
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_photos($params = [])
    {
        data_filter($params);

        $params = $this->__check_photos($params, $type="add");
        if (false == $params) {
            $this->set_error($this->get_error());
            return false;
        }

        // 查找相册信息
        $this->load->model('adm/Photos_album_model');
        $album = $this->Photos_album_model->get_single_album(['photos_album_id' => $params['photos_album_id']]);

        $params['album_name'] = $album['album_name'];
        append_create_update($params);

        // 保存新增信息
        $service_shop = [
            'service_name'   => 'photos.photos.add_photos',
            'service_params' => [
                'list'         => $params,
                'is_batch'     => false,
                'is_insert_id' => true,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_shop);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }

    /**
     * 编辑单个照片信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_photos($params = [])
    {
        data_filter($params);

        $params = $this->__check_photos($params, $type="edit");
        if (false == $params) {
            $this->set_error($this->get_error());
            return false;
        }

        // 查找相册信息
        $this->load->model('adm/Photos_album_model');
        $album = $this->Photos_album_model->get_single_album(['photos_album_id' => $params['photos_album_id']]);

        $params['album_name'] = $album['album_name'];
        append_update_info($params);

        // 保存修改信息
        $service_params = [
            'service_name'   => 'photos.photos.update_by_params',
            'service_params' => [
                'photos_id' => $params['photos_id'],
                'set'       => $params,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }

    /**
     * 照片内容检查
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    private function __check_photos($params = [], $type = 'add')
    {
        if (empty($params['photos_album_id'])) {
            $this->set_error('请选择照片所属相册');
            return false;
        }
        if (empty($params['photo_title'])) {
            $this->set_error('请填写照片标题');
            return false;
        }

        // 修改时，有单独判断
        if ($type == 'add') {
            if (empty($params['photo_url'])) {
                $this->set_error('请选择照片文件');
                return false;
            }
        } else if ($type == 'edit') {
            if (empty($params['photos_id'])) {
                $this->set_error('照片ID为空');
                return false;
            }
        }

        return $params;
    }

    /**
     * 设置照片为相册封面
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function set_cover($params = [])
    {
        if (empty($params['photos_id'])) {
            $this->set_error('照片ID为空');
            return false;
        }

        // 获取照片信息
        $photos = $this->get_single_photos($params);

        if (2 == $photos['is_enabled']) {
            $this->set_error('此照片当前无效');
            return false;
        }

        // 修改对应相册的封面
        $service_shop = [
            'service_name'   => 'photos.photos_album.update_by_params',
            'service_params' => [
                'photos_album_id' => $photos['photos_album_id'],
                'set'             => ['cover_url' => $photos['photo_url']],
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_shop);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }

    /**
     * 设置有效性
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function set_enabled($params = [])
    {
        if (empty($params['photos_id'])) {
            $this->set_error('照片ID为空');
            return false;
        }

        append_update_info($params);

        // 保存修改信息
        $service_params = [
            'service_name'   => 'photos.photos.update_by_params',
            'service_params' => [
                'photos_id' => $params['photos_id'],
                'set'       => $params,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }
}