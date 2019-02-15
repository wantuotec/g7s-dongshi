<?php
/**
 * 用户文章评论管理
 *
 * @@author     madesheng
 * @date        2017-02-27
 * @category    article_comment_model
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Article_comment_model extends CI_Model
{
    /**
     * 获取用户文章评论列表
     * 
     * @param    $params    array    请求参数
     *
     * @return   array | bool
     */
    public function get_member_comment_list($params = [])
    {
        data_filter($params);

        $service_params = [
            'service_name'   => 'article.article_comment.get_list',
            'service_params' => [
                'is_pages'          => true,
                'type'              => filter_empty('type', $params),
                'is_return'         => filter_empty('is_return', $params),
                'article_id'        => filter_empty('article_id', $params),
                'parent_comment_id' => isset($params['parent_comment_id']) ? $params['parent_comment_id'] : null,
                'audit_status'      => filter_empty('audit_status', $params),
                'ge_create_time'    => filter_empty('ge_create_time', $params),
                'le_create_time'    => filter_empty('le_create_time', $params),
                'order_by'          => 'create_time DESC',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_params);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        // 统计回复类型（首次评论|回复评论）
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$comment) {
                if (0 == $comment['parent_comment_id']) {
                    $comment['return_type'] = '首次评论';
                } else {
                    $comment['return_type'] = '评论回复';
                }
            }
        }

        return $result['data'];
    }

    /**
     * 通过ID获取单条文章评论
     * 
     * @param  array $params 请求参数
     *
     * @return  bool|array
     */
    public function get_single_comment($params = [])
    {
        data_filter($params);

        if (empty($params['article_comment_id'])) {
            $this->set_error('文章评论ID为空');
            return false;
        }

        $service_info = [
            'service_name'   => 'article.article_comment.get',
            'service_params' => [
                'article_comment_id' => filter_empty('article_comment_id', $params),
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
     * 回复用户评论
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function return_comment($params = [])
    {
        // notice：包含表情等特殊字符，不要使用过滤
        if (empty($params['comment'])) {
            $this->set_error('评论回复内容为空');
            return false;
        }
        if (empty($params['article_id'])) {
            $this->set_error('文章ID为空');
            return false;
        }
        if (empty($params['parent_comment_id'])) {
            $this->set_error('回复评论对象的ID为空');
            return false;
        }
        if ($params['level'] === '') {
            $this->set_error('回复评论对象的ID为空');
            return false;
        }
        $params['no_filter'] = true;

        // 管理员信息
        $admin_info              = get_operate_user();
        $params['customer_id']   = $admin_info['user_id'];
        $params['customer_name'] = $admin_info['userName'];
        $params['type']          = 2;
        $params['audit_status']  = 2;
        $params['level']         = $params['level'] + 1;
        append_create_info($params);

        // 新增回复评论内容
        $service_shop = [
            'service_name'   => 'article.article_comment.add_article_comment',
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

        // 修改对象评论，管理员已回复
        $this->edit_article(['article_comment_id' => $params['parent_comment_id'], 'is_return' => 1]);

        return true;
    }

    /**
     * 编辑文章评论
     * 
     * @param    $params    array    保存参数
     *
     * @return   array | bool
     */
    public function edit_article($params = [])
    {
        data_filter($params);

        if (empty($params['article_comment_id'])) {
            $this->set_error('文章评论ID为空');
            return false;
        }

        // 保存修改信息
        $service_shop = [
            'service_name'   => 'article.article_comment.update_by_params',
            'service_params' => [
                'article_comment_id' => $params['article_comment_id'],
                'set'                => $params,
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
     * 文章评论批量审核通过操作
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function batch_audit_ok($params = array())
    {
        data_filter($params);

        if (empty($params['article_comment_ids']) || '' == $params['article_comment_ids']) {
            $this->set_error('请选择要审核通过的提现单据');
            return false;
        }

        $article_comment_ids = explode(',', $params['article_comment_ids']);
        $this->load->library('requester');

        if (is_array($article_comment_ids) && !empty($article_comment_ids)) {
            foreach ($article_comment_ids as $value) {
                $service_info = [
                    'service_name'   => 'article.article_comment.update_by_params',
                    'service_params' => [
                        'article_comment_id' => $value,
                        'set' => ['audit_status' => 2],
                    ],
                ];

                $result = $this->requester->request($service_info);
                if (true != $result['success']) {
                    $this->set_error($result['message']);
                    return false;
                }
            }
        }

        return $result['data'];
    }
}