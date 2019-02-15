<?php
/**
 * 相册照片管理
 *
* @author     madesheng
* @date       2017-02-18
* @category   Mood_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Photos_model extends CI_Model
{
	/**
	 * 获取相册列表
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_album_list($params = [])
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
		            $album['cover_url'] = HOME_DOMAIN . 'images/photo_64px.png';
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
	 * 获取照片列表
	 * 
	 * @param    $params    array    请求参数
	 *
	 * @return   array | bool
	 */
	public function get_photos_list($params = [])
	{
	    data_filter($params);

	    $service_params = [
	        'service_name'   => 'photos.photos.get_list',
	        'service_params' => [
	            'is_pages'        => true,
	            'photos_album_id' => filter_empty('photos_album_id', $params),
	            'is_enabled'      => 1,
	            'order_by'        => 'create_time DESC',
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
}
