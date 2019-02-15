<?php
/**
 * 文章相关服务
 *
 * @author      madesheng
 * @date        2017-04-18
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class S_article_model extends CI_Model
{
    // 不定义默认和model保持一致
    public $id  = 'article_id';
    public $dao = 'article/s_article_dao';

    public $cfg = [
        'is_enabled' => [
            1 => '有效',
            2 => '无效',
        ],
        'origin_type' => [
            1 => '原创',
            2 => '转载',
        ],
    ];

    // 获取配置
    public function get_cfg($params = [])
    {
        return $this->cfg;
    }

    /**
     * 获取文章列表
     *
     * @access  public
     *
     * @param   array   $params    查询数据
     *
     * @return  bool|int
     */
    public function get_article_list($params = [])
    {
        // 定义输入输出
        $input  = ['is_pages', 'article_category_id', 'is_enabled', 'is_recommend', 'ge_create_time', 'le_create_time', 'like', 'order_by', 'where_in'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        // 获取基本数据
        $result = $this->get_list($params);
        if (false == $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // 获取额外数据
        if (!empty($result['list'])) {
            $article_ids          = [];
            $article_category_ids = [];
            foreach ($result['list'] as $article) {
                $article_ids[]          = $article['article_id'];
                $article_category_ids[] = $article['article_category_id'];
            }

            // 获取文章所属分类
            $category_search = [
                'fields'   => 'article_category_id, category_name',
                'key_name' => 'article_category_id',
                'where_in' => ['article_category_id' => array_unique($article_category_ids)]
            ];
            $this->load->model('article/S_article_category_model');
            $category_result = $this->S_article_category_model->get_list($category_search);

            // 获取评论数
            $comment_search = [
                'fields'   => 'article_id,count(*) as total',
                'key_name' => 'article_id',
                'group_by' => 'article_id',
                'where_in' => ['article_id' => array_unique($article_ids)]
            ];
            $this->load->model('article/S_article_comment_model');
            $comment_result = $this->S_article_comment_model->get_list($comment_search);

            $cdn_domain = get_cdn_domain(ENVIRONMENT);
            foreach ($result['list'] as &$article) {
                // 标题处理（超过20字显示简短标题）
                if (mb_strlen($article['article_title']) > 20) {
                    $article['article_title'] = mb_substr($article['article_title'], 0, 10) . '...';
                }
                // 封面图
                $article['cover_photo']   = $cdn_domain . $article['cover_photo'];
                // 所属分类
                $category = $category_result['list'][$article['article_category_id']];
                $article['category_name'] = !empty($category) ? $category['category_name'] : '';
                // 评论数
                $comment = $comment_result['list'][$article['article_id']];
                $article['comment_total'] = !empty($comment) ? $comment['total'] : 0;
                // 日期格式
                $article['date']          = date('Y-m-d', strtotime($article['create_time']));
            }
        }

        return $result;
    }

    /**
     * 添加文章
     *
     * @access  public
     *
     * @param   array   $params    插入数据
     *
     * @return  bool|int
     */
    public function add_article($params = [])
    {
        // 定义输入输出
        $input  = ['list', 'is_batch', 'is_insert_id'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        // 判断入库数据是否为空
        if (empty($params['list'])) {
            $this->set_error(30004);
            return false;
        }

        $is_batch     = ($params['is_batch']     && true == $params['is_batch'])     ? true : false;
        $is_insert_id = ($params['is_insert_id'] && true == $params['is_insert_id']) ? true : false;

        $result = $this->add($params['list'],$is_batch,$is_insert_id);

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 通过唯一字段修改单条文章
     *
     * @access  public
     *
     * @param   array   $params    要更新的信息
     *
     * @return  bool
     */
    public function update_by_params($params = [])
    {
        data_filter($params);

        // 定义输入输出
        $input  = ['article_id', 'set'];
        $output = [];

        // 过滤输入
        $this->filter($input, $params);

        if (empty($params['set'])) {
            $this->set_error(30001);
            return false;
        }

        // 按article_id进行修改
        if (isset($params['article_id']) && !empty($params['article_id'])) {
            $result = $this->update_by_id($params['article_id'], $params['set']);
        // 都不存在时，给出提示
        } else {
            $this->set_error(30003);
            return false;
        }

        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        return $result;
    }
}