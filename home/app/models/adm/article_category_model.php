<?php
/**
 * 文章分类管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    article_category_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Article_category_model extends CI_Model
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
                'is_pages'       => false,
                'key_name'       => 'article_category_id',
                'fields'         => filter_empty('fields', $params),
                'is_enabled'     => filter_empty('is_enabled', $params),
                'ge_article_num' => filter_empty('ge_article_num', $params),
                'le_article_num' => filter_empty('le_article_num', $params),
                'ge_create_time' => filter_empty('ge_create_time', $params),
                'le_create_time' => filter_empty('le_create_time', $params),
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        $article_category_ids = [];
        !empty($result['data']['list']) && $article_category_ids = array_keys($result['data']['list']);

        // 统计分类下的文章数
        $service_params = [
            'service_name'   => 'article.article.get_list',
            'service_params' => [
                'fields'     => 'article_category_id, count(*) as total',
                'is_enabled' => 1,
                'group_by'   => 'article_category_id',
                'key_name'   => 'article_category_id',
                'where_in'   => ['article_category_id' => $article_category_ids],
            ],
        ];
        $article = $this->requester->request($service_params);

        // 将实时有效文章数统计数据整合到分类信息中
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$category) {
                if (isset($article['data']['list'][$category['article_category_id']])) {
                    $category['article_num'] = $article['data']['list'][$category['article_category_id']]['total'];
                } else {
                    $category['article_num'] = 0;
                }
            }
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单条文章分类
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_category($params = [])
    {
        data_filter($params);

        if (empty($params['article_category_id'])) {
            $this->set_error('分类ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'article.article_category.get',
            'service_params' => [
                'article_category_id' => filter_empty('article_category_id', $params),
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
     * 添加文章分类
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function add_category($params = [])
    {
        data_filter($params);

        if (empty($params['category_name'])) {
            $this->set_error('心情内容为空');
            return false;
        }
        if (empty($params['is_enabled'])) {
            $this->set_error('是否有效为空');
            return false;
        }
        append_create_update($params);

        // 保存新增信息
        $service_shop = [
            'service_name'   => 'article.article_category.add_article_category',
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
     * 编辑文章分类信息
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_category($params = [])
    {
        data_filter($params);

        if (empty($params['article_category_id'])) {
            $this->set_error('记录ID为空');
            return false;
        }
        append_update_info($params);

        // 保存修改信息
        $service_shop = [
            'service_name'   => 'article.article_category.update_by_params',
            'service_params' => [
                'article_category_id' => $params['article_category_id'],
                'set'                 => $params,
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