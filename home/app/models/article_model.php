<?php
/**
 * 文章管理
 *
* @author     madesheng
* @date       2017-04-19
* @category   Article_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Article_model extends CI_Model
{
	/**
	 * 获取文章分类列表
	 * 
	 * @param    $params    array    请求参数
	 *
	 * @return   array | bool
	 */
	public function get_article_category_list($params = [])
	{
	    data_filter($params);

	    $service_params = [
	        'service_name'   => 'article.article_category.get_list',
	        'service_params' => [
	        	'fields'     => 'article_category_id, category_name',
	            'is_pages'   => false,
	            'is_enabled' => 1,
	            'key_name'   => 'article_category_id',
	        ],
	    ];

	    $this->load->library('requester');
	    $result = $this->requester->request($service_params);
	    if (true != $result['success']) {
	        $this->set_error($result['message']);
	        return false;
	    }
	    !empty($result['data']['list']) && $article_category_ids = array_keys($result['data']['list']);

		// 统计分类下的文章数
		$service_params = [
		    'service_name'   => 'article.article.get_list',
		    'service_params' => [
		    	'fields'     => 'article_category_id, count(*) as total',
		        'is_pages'   => false,
		        'is_enabled' => 1,
		        'group_by'   => 'article_category_id',
		        'key_name'   => 'article_category_id',
		        'where_in'   => ['article_category_id' => $article_category_ids],
		    ],
		];
		$article = $this->requester->request($service_params);
		$enabled_category_ids = [];
		!empty($article['data']['list']) && $enabled_category_ids = array_keys($article['data']['list']);

	    // 当前没有有效文章的分类就不显示了
	    if (!empty($result['data']['list'])) {
	    	foreach ($result['data']['list'] as $key => $val) {
	    		if (!in_array($val['article_category_id'], $enabled_category_ids)) {
	    			unset($result['data']['list'][$key]);
	    		}
	    	}
	    }

	    // 增加一个所有分类
	    array_push($result['data']['list'],['article_category_id' => 'all', 'category_name' => '全部分类']);

	    return $result['data'];
	}

	/**
	 * 获取文章列表
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_article_list($params = [])
	{
		data_filter($params);

		$this->load->library('requester');

		// 判断是否指定文章分类,没有则要限制所有有效分类
		if (empty($params['article_category_id']) || $params['article_category_id'] == 'all') {
			$service_params = [
			    'service_name'   => 'article.article_category.get_list',
			    'service_params' => ['is_pages' => false,'is_enabled' => 1,'key_name' => 'article_category_id'],
			];
			$category = $this->requester->request($service_params);
			if (empty($category['data']['list'])) {
				return [];
			} else {
				$article_category_id = array_keys($category['data']['list']);
			}

		} else {
			$article_category_id[] = $params['article_category_id'];
		}

		$service_info = [
			'service_name'   => 'article.article.get_article_list',
			'service_params' => [
				'is_pages'   => true,
				'is_enabled' => 1,
				'order_by'   => 'create_time DESC',
				'where_in'   => ['article_category_id' => $article_category_id],
			],
		];
		$result = $this->requester->request($service_info);

		if (true == $result['success']) {
			return $result['data'];
		} else {
			$this->set_error($result['message']);
			return false;
		}
	}

	/**
	 * 获取单篇文章详情
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_article_detail($params = [])
	{
		data_filter($params);

		$service_info = [
			'service_name'   => 'article.article.get',
			'service_params' => [
				'article_id' => filter_empty('article_id', $params),
			],
		];
		$this->load->library('requester');
		$result = $this->requester->request($service_info);
		if (false == $result['success']) {
			$this->set_error($result['message']);
			return false;
		}

		// 处理部分字段
		$result['data']['date'] = date('Y-m-d', strtotime($result['data']['create_time']));

		return $result['data'];
	}

	/**
	 * 文章修改
	 * 
	 * @param    $params    array    保存参数
	 *
	 * @return   array | bool
	 */
	public function edit_article($params = [])
	{
	    if (empty($params['article_id'])) {
	        $this->set_error('哎呀，暂时无法操作了/// 小编会尽快把把脉的');
	        return false;
	    }

	    // 保存修改信息
	    $service_params = [
	        'service_name'   => 'article.article.update_by_params',
	        'service_params' => [
	            'article_id' => $params['article_id'],
	            'set'        => $params,
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
