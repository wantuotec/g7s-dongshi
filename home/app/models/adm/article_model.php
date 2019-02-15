<?php
/**
 * 心情杂记管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    article_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Article_model extends CI_Model
{
    /**
     * 获取文章列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_article_list($params = [])
    {
        data_filter($params);

        // 单个排序还是多个排序
        $order_by = 'sort ASC';
        if (!empty($params['order_by_read_num'])) {
            if (1 == $params['order_by_read_num']) {
                $order_by = 'read_num ASC';
            } else {
                $order_by = 'read_num DESC';
            }

            // 同时有点赞排序
            if (!empty($params['order_by_like_num'])) {
                if (1 == $params['order_by_like_num']) {
                    $order_by .= ',like_num ASC';
                } else {
                    $order_by .= ',like_num DESC';
                }
            }
        } else {
            if (!empty($params['order_by_like_num'])) {
                if (1 == $params['order_by_like_num']) {
                    $order_by = 'like_num ASC';
                } else {
                    $order_by = 'like_num DESC';
                }
            }
        }

        $service_params = [
            'service_name'   => 'article.article.get_article_list',
            'service_params' => [
                'is_pages'            => true,
                'article_category_id' => filter_empty('article_category_id', $params),
                'is_enabled'     => filter_empty('is_enabled', $params),
                'is_recommend'   => filter_empty('is_recommend', $params),
                'ge_create_time' => filter_empty('ge_create_time', $params),
                'le_create_time' => filter_empty('le_create_time', $params),
                'like'           => ['article_title' => !empty($params['like_article_title']) ? $params['like_article_title'] : null],
                'order_by'       => $order_by,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单条文章
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_article($params = [])
    {
        data_filter($params);

        if (empty($params['article_id'])) {
            $this->set_error('文章ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'article.article.get',
            'service_params' => [
                'article_id' => filter_empty('article_id', $params),
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        // 处理封面图
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        $result['data']['cover_photo'] = $cdn_domain . $result['data']['cover_photo'];

        if (true == $result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['message']);
            return false;
        }
    }

    /**
     * 添加文章
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_article($params = [])
    {
        // notice：包含表情等特殊字符，不要使用过滤
        $params = $this->__check_article($params, $type="add");
        if (false == $params) {
            $this->set_error($this->get_error());
            return false;
        }
        $params['no_filter'] = true;
        append_create_update($params);

        // 保存新增信息
        $service_shop = [
            'service_name'   => 'article.article.add_article',
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
     * 编辑文章信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_article($params = [])
    {
        // notice：包含表情等特殊字符，不要使用过滤
        $params = $this->__check_article($params, $type="edit");
        if (false == $params) {
            $this->set_error($this->get_error());
            return false;
        }

        $params['no_filter'] = true;
        append_update_info($params);

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

    /**
     * 文章内容检查
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    private function __check_article($params = [], $type = 'add')
    {
        if (empty($params['article_title'])) {
            $this->set_error('文章标题为空');
            return false;
        }
        if (empty($params['article_category_id'])) {
            $this->set_error('文章分类为空');
            return false;
        }
        if (empty($params['origin_type'])) {
            $this->set_error('文章来源为空');
            return false;
        }
        if (empty($params['cover_words'])) {
            $this->set_error('封面文字为空');
            return false;
        }
        if (empty($params['content'])) {
            $this->set_error('文章内容为空');
            return false;
        }

        // 修改时，有单独判断
        if ($type == 'add') {
            if (empty($params['cover_photo'])) {
                $this->set_error('封面图片为空');
                return false;
            }
        } else if ($type == 'edit') {
            if (empty($params['article_id'])) {
                $this->set_error('文章ID为空');
                return false;
            }
            // 由是否含主域名，判断封面图是否变更
            $cdn_domain = get_cdn_domain(ENVIRONMENT);
            if (!empty($params['cover_photo']) && strpos($params['cover_photo'], $cdn_domain) !== false) {
                $params['cover_photo'] = str_replace($cdn_domain, '', $params['cover_photo']);
            }
        }

        return $params;
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
        if (empty($params['article_id'])) {
            $this->set_error('文章ID为空');
            return false;
        }

        append_update_info($params);

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

    /**
     * 设置首页推荐
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function set_recommend($params = [])
    {
        $recommend_num = 6;  //最多推荐篇数

        if (empty($params['article_id'])) {
            $this->set_error('文章ID为空');
            return false;
        }
        if (empty($params['is_recommend'])) {
            $this->set_error('要设置的推荐标识值为空');
            return false;
        }

        //统计当前已有的推荐文章数（不能超6篇）
        $service_params = [
            'service_name'   => 'article.article.get_list',
            'service_params' => [
                'fields'       => 'article_id',
                'is_recommend' => 1,
            ],
        ];

        $this->load->library('requester');
        $count_result = $this->requester->request($service_params);
        if (true != $count_result['success']) {
            $this->set_error($count_result['message']);
            return false;
        }

        // 如果已推荐篇数达到限定篇，则不能再设置新的推荐
        if (1 == $params['is_recommend'] && $count_result['data']['total'] >= $recommend_num) {
            $this->set_error('当前已推荐'. $recommend_num .'篇，不能再设置新的推荐文章');
            return false;
        }

        // 保存修改信息
        $service_params = [
            'service_name'   => 'article.article.update_by_params',
            'service_params' => [
                'article_id' => $params['article_id'],
                'set'        => ['is_recommend' => $params['is_recommend']],
            ],
        ];

        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        return true;
    }

    /**
     * 修改排序
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_sort($params = [])
    {
        if (empty($params['article_id']) || empty($params['sort'])) {
            $this->set_error('文章ID和排序值不能为空');
            return false;
        }
        if (count($params['article_id']) != count($params['sort'])) {
            $this->set_error('文章ID和排序值数量不匹配');
            return false;
        }

        // 修改排序值
        $this->load->library('requester');
        foreach ($params['article_id'] as $key => $id) {
            $service_params = [
                'service_name'   => 'article.article.update_by_params',
                'service_params' => [
                    'article_id' => $id,
                    'set'        => ['sort' => $params['sort'][$key]],
                ],
            ];

            $result = $this->requester->request($service_params);
            if (true != $result['success']) {
                $this->set_error($result['message']);
                return false;
            }
        }

        return true;
    }
}