<?php
/**
 * 首页管理
 *
* @author     madesheng
* @date       2017-02-18
* @category   Home_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Home_model extends CI_Model
{
	/**
	 * 获取网站首页数据
	 * 
	 * @return  bool|array
	 */
	public function get_home_data()
	{
		// 获取当前用户信息（当前还没有用户系统使用默认信息）
		$user_info = get_user_info();
		if (empty($user_info)) {
			$user_info = [
				'user_name'   => '美丽帅气君',
                'sex'         => 1,
                'user_header' => HOME_DOMAIN . 'public/images/header/header_9.jpg',
			];
		}

		// 获取首页站点公告
		$notice_result = $this->get_website_notice();

		// 获取首页最新动态
		$new_result = $this->get_new_info();

		// 获取首页推荐文章
		$article_result = $this->get_recommend_article();

		$result['user_info']         = $user_info;
		$result['website_notice']    = $notice_result['list'];
		$result['new_info']          = $new_result;
		$result['recommend_article'] = $article_result;

		return $result;
	}

	/**
	 * 获取网站首页数据-网站公告
	 * 
	 * @return  bool|array
	 */
	public function get_website_notice()
	{
		$service_params = [
		    'service_name'   => 'operation.website_notice.get_list',
		    'service_params' => [
		    	'fields'     => 'website_notice_id,content,create_time',
		    	'is_enabled' => 1,
		    ],
		];
		$this->load->library('requester');
		$notice_result = $this->requester->request($service_params);

		return $notice_result['data'];
	}

	/**
	 * 获取网站首页数据-推荐文章
	 * 
	 * @return  bool|array
	 */
	public function get_recommend_article()
	{
		$service_info = [
		    'service_name'   => 'article.article.get_list',
		    'service_params' => [
		    	'fields'       => 'article_id,article_title,cover_words,cover_photo,article_category_id,origin_type,read_num,like_num,create_time',
		    	'is_enabled'   => 1,
		    	'is_recommend' => 1,
		    	'order_by'     => 'sort ASC',
		    ],
		];
		$this->load->library('requester');
		$article_result = $this->requester->request($service_info);

		// 处理封面图片
		$cdn_domain = get_cdn_domain(ENVIRONMENT);
		if (!empty($article_result['data']['list'])) {
		    foreach ($article_result['data']['list'] as &$article) {
		        $article['cover_photo'] = $cdn_domain . $article['cover_photo'];
		        $article['date']        = date('Y-m-d', strtotime($article['create_time']));
		        $article['href']        = HOME_DOMAIN . 'article/article_detail?article_id=' . $article['article_id'];
		    }
		}

		return $article_result['data']['list'];
	}

	/**
	 * 获取网站首页数据-最新动态
	 * 
	 * @return  bool|array
	 */
	public function get_new_info()
	{
		//心情动态
		$new_mood = $this->get_new_mood();

		//相片动态
		$new_photo = $this->get_new_photo();

		//文章动态
		$new_article = $this->get_new_article();

		$result = [];
		!empty($new_mood) && $result = array_merge($result, $new_mood);
		!empty($new_mood) && $result = array_merge($result, $new_photo);
		!empty($new_mood) && $result = array_merge($result, $new_article);

		return $result;
	}

	/**
	 * 获取首页最新动态-心情杂记
	 * 
	 * @return  bool|array
	 */
	public function get_new_mood()
	{
		//拉取规则：默认去拉取最后一条数据
		$service_params = [
            'service_name'   => 'mood.mood.get',
            'service_params' => [
                'is_enabled' => 1,
                'order_by'   => 'create_time DESC',
            ],
        ];
        $this->load->library('requester');
        $mood_result = $this->requester->request($service_params);

		if (!empty($mood_result['data'])) {
			$result[] = [
				'title' => '心情杂记', 
				'desc'  => '小编最近发表了心情O(∩_∩)O',
				'href'  => HOME_DOMAIN . 'mood/index?',
				'date'  => date('Y-m-d', strtotime($mood_result['data']['create_time'])),
			];
		} else {
			$result[] = [
				'title' => '心情杂记', 
				'desc'  => '可能雾霾有点多，小编暂时还没有发表任何心情(*>﹏<*)///',
				'href'  => HOME_DOMAIN . 'mood/index?',
				'date'  => 'xxxx-xx-xx',
			];
		}

		return $result;
	}

	/**
	 * 获取首页最新动态-光影流年
	 * 
	 * @return  bool|array
	 */
	public function get_new_photo()
	{
		//拉取规则：默认拉取最近一个月的最新一条数据，有则拼接数据，没有则去拉取最后一条数据
		$service_params = [
            'service_name'   => 'photos.photos.get_list',
            'service_params' => [
                'is_enabled'     => 1,
                'ge_create_time' => date('Y-m-d', strtotime("-30 days")),
                'order_by'       => 'create_time DESC',
                'group_by'       => 'photos_album_id',
            ],
        ];
        $this->load->library('requester');
        $photo_result = $this->requester->request($service_params);

		//一个月内无更新，则去拉取最后一条
		if (!empty($photo_result['data']['list'])) {
			foreach ($photo_result['data']['list'] as $photo) {
				$result[] = [
					'title' => '光影流年', 
		        	'desc'  => '在相册《' . $photo['album_name'] . '》上传了新的照片——' . $photo['photo_title'],
		        	'href'  => HOME_DOMAIN . 'photo/photo_list?photos_album_id=' . $photo['photos_album_id'],
					'date'  => date('Y-m-d', strtotime($photo['create_time'])),
	        	];
			}

		} else {
			$service_params = [
	            'service_name'   => 'photos.photos.get',
	            'service_params' => [
	                'is_enabled' => 1,
	                'order_by'   => 'create_time DESC',
	            ],
	        ];
	        $photo_result = $this->requester->request($service_params);

	        if (!empty($photo_result['data'])) {
	        	$result[] = [
		        	'title' => '光影流年', 
		        	'desc'  => '在相册《' . $photo_result['data']['album_name'] . '》上传了新的照片——' . $photo_result['data']['photo_title'],
		        	'href'  => HOME_DOMAIN . 'photo/photo_list?photos_album_id=' . $photo_result['data']['photos_album_id'],
					'date'  => date('Y-m-d', strtotime($photo_result['data']['create_time'])),										
	        	];
	        } else {
	        	$result = [];
	        }
		}

		return $result;
	}

	/**
	 * 获取首页最新动态-文章随笔
	 * 
	 * @return  bool|array
	 */
	public function get_new_article()
	{
		//拉取规则：默认拉取最近一个月的最新一条数据，有则拼接数据，没有则去拉取最后一条数据
		$service_params = [
            'service_name'   => 'article.article.get_list',
            'service_params' => [
            	'fields'         => 'article_id,article_title,origin_type,create_time',
                'is_enabled'     => 1,
                'ge_create_time' => date('Y-m-d', strtotime("-2 month")),
                'order_by'       => 'create_time DESC',
                'group_by'       => 'article_category_id',
            ],
        ];
        $this->load->library('requester');
        $article_result = $this->requester->request($service_params);

		//一个月内无更新，则去拉取最后一条
		if (!empty($article_result['data']['list'])) {
			foreach ($article_result['data']['list'] as $article) {
				$content_type = $article['origin_type'] == 1 ? '发表' : '转载'; 
				$result[] = [
					'title' => '文章随笔', 
	        		'desc'  => '小编' . $content_type . '了'. $article['origin_type_title'] . '文章《' . $article['article_title'] . '》',
	        		'href'  => HOME_DOMAIN . 'article/article_detail?article_id=' . $article['article_id'],
					'date'  => date('Y-m-d', strtotime($article['create_time'])),
	        	];
			}

		} else {
			$service_params = [
	            'service_name'   => 'article.article.get',
	            'service_params' => [
	                'is_enabled' => 1,
	                'order_by'   => 'create_time DESC',
	            ],
	        ];
	        $article_result = $this->requester->request($service_params);

	        if (!empty($article_result['data'])) {
    	        $content_type = $article_result['data']['origin_type'] == 1 ? '发表' : '转载'; 
    	        $result[] = [
    	        	'title' => '文章随笔', 
    	        	'desc'  => '小编' . $content_type . '了'. $article_result['data']['origin_type_title'] . '文章《' . $article_result['data']['article_title'] . '》',
    	        	'href'  => HOME_DOMAIN . 'article/article_detail?article_id=' . $article_result['data']['article_id'],
    				'date'  => date('Y-m-d', strtotime($article_result['data']['create_time'])),
    	        ];
	        } else {
	        	$result = [];
	        }
		}

		return $result;
	}
}
