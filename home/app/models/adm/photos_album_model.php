<?php
/**
 * 文章分类管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    photos_album_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Photos_album_model extends CI_Model
{
    /**
     * 获取相册列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_photos_album_list($params = [])
    {
        data_filter($params);

        $service_params = [
            'service_name'   => 'photos.photos_album.get_list',
            'service_params' => [
                'fields'     => filter_empty('fields', $params),
                'is_enabled' => filter_empty('is_enabled', $params),
                'is_pages'   => false,
                'key_name'   => 'photos_album_id',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        $photos_album_ids = [];
        !empty($result['data']['list']) && $photos_album_ids = array_keys($result['data']['list']);

        // 统计相册下的文章数
        $service_params = [
            'service_name'   => 'photos.photos.get_list',
            'service_params' => [
                'fields'     => 'photos_album_id, count(*) as total',
                'is_enabled' => 1,
                'group_by'   => 'photos_album_id',
                'key_name'   => 'photos_album_id',
                'where_in'   => ['photos_album_id' => $photos_album_ids],
            ],
        ];
        $photos = $this->requester->request($service_params);

        // 将实时有效照片数统计数据整合到相册信息中
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$album) {
                $album['date'] = date('Y-m-d', strtotime($album['create_time']));
                if (!empty($album['cover_url'])) {
                    $album['cover_url'] = $cdn_domain . $album['cover_url'];
                } else {
                    // 默认封面
                    $album['cover_url'] = HOME_DOMAIN . 'public/images/photo_64px.png';
                }
                if (isset($photos['data']['list'][$album['photos_album_id']])) {
                    $album['photos_num'] = $photos['data']['list'][$album['photos_album_id']]['total'];
                } else {
                    $album['photos_num'] = 0;
                }
            }
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单条相册信息
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_album($params = [])
    {
        data_filter($params);

        if (empty($params['photos_album_id'])) {
            $this->set_error('相册ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'photos.photos_album.get',
            'service_params' => [
                'photos_album_id' => filter_empty('photos_album_id', $params),
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }

    /**
     * 添加相册
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_album($params = [])
    {
        data_filter($params);

        if (empty($params['album_name'])) {
            $this->set_error('相册名称为空');
            return false;
        }
        append_create_update($params);

        // 保存新增信息
        $service_shop = [
            'service_name'   => 'photos.photos_album.add_photos_album',
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
     * 编辑相册信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_album($params = [])
    {
        data_filter($params);

        if (empty($params['photos_album_id'])) {
            $this->set_error('相册ID为空');
            return false;
        }
        append_update_info($params);

        // 保存修改信息
        $service_shop = [
            'service_name'   => 'photos.photos_album.update_by_params',
            'service_params' => [
                'photos_album_id' => $params['photos_album_id'],
                'set'             => $params,
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
}