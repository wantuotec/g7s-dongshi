<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-06
 * @category    Article.php
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Article extends CI_Controller
{
    /*
    * (漫漫人生)-文章列表页面
    */
    public function index()
    {
        $params = $this->input->get();
        data_filter($params);

        // 获取网站顶部slogan
        $this->load->model('Slogan_model');
        $slogan_result = $this->Slogan_model->get_single_slogan(['item_type' => 3]);

        // 获取文章分类列表
        $this->load->model('Article_model');
        $article_category = $this->Article_model->get_article_category_list();

        // 获取文章列表
        if (empty($params['category_id'])) {
            $params['category_id'] = 'all';  // 默认全部分类
        } else {
            $params['article_category_id'] = $params['category_id'];
        }
        $article_result = $this->Article_model->get_article_list($params);

        $this->load->library('home_pagination');

        $data = [
            'search'      => $params,
            'slogan'      => $slogan_result['content'],
            'category'    => $article_category['list'],
            'article'     => $article_result['list'],
            'pagination'  => $this->home_pagination->get_page_bar($article_result['total']),
        ];

        $this->load->view('base/header.tpl', ['current_title' => 'article']);
        $this->load->view('article/index.tpl', $data);
        $this->load->view('base/footer.tpl');
    }

    /*
    * (漫漫人生)-文章详情页面
    */
    public function article_detail()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('Article_model');

        // 获取访问用户IP地址
        $ip = $this->input->ip_address();

        // 判断用户是否已点赞
        $is_market_like = 2;  // 默认是未点赞的
        $check_like = $_COOKIE['customer_like_'.md5($ip)];
        if (!empty($check_like) && intval($check_like) == intval($params['article_id'])) {
            $is_market_like = 1;
        }

        // 判断用户是否已阅读（24小时后可重新刷新身份）
        $check_read = $_COOKIE['customer_read_'.md5($ip)];
         if (empty($check_read) || intval($check_read) != intval($params['article_id'])) {
            // 阅读量+1
            $params['read_num'] = '`read_num`+1';
            $result = $this->Article_model->edit_article($params);
            setcookie("customer_read_".md5($ip), intval($params['article_id']), time()+24*3600);
        }

        // 获取评论控制
        $service_shop = [
            'service_name'   => 'operation.module_manage.get_list',
            'service_params' => ['fields' => 'module_mark,is_open', 'key_name' => 'module_mark'],
        ];
        $this->load->library('requester');
        $module_result = $this->requester->request($service_shop);

        // 获取网站顶部slogan
        $this->load->model('Slogan_model');
        $slogan_result = $this->Slogan_model->get_single_slogan(['item_type' => 4]);

        // 获取文章分类列表
        $article_category = $this->Article_model->get_article_category_list();

        // 获取此篇文章详情
        $result = $this->Article_model->get_article_detail($params);

        $data = [
            'search'         => $params,
            'slogan'         => $slogan_result['content'],
            'category'       => $article_category['list'],
            'list'           => $result,
            'module_open'    => $module_result['data']['list'],
            'is_market_like' => $is_market_like,
        ];

        // 开启页面缓存（10分钟）
        // $this->output->cache(10);

        $this->load->view('base/header.tpl', ['current_title' => 'article']);
        $this->load->view('article/article_detail.tpl', $data);
        $this->load->view('base/footer.tpl');
    }

    /**
     * 文章点赞操作
     *
     * @access  public
     *
     * @return  void
     */
    public function market_like()
    {
        $params = $this->input->post();

        //未关闭浏览器情况下，只计算一次点赞（24小时后可重新刷新身份）
        $ip = $this->input->ip_address();
        $check_like = $_COOKIE['customer_like_'.md5($ip)];
        if (!empty($check_like) && intval($check_like) == intval($params['article_id'])) {
            json_exit('\^o^/ 亲，谢谢您的支持，小编不贪心下次再赞吧...');
        }

        $params['like_num'] = '`like_num`+1';
        $this->load->model('Article_model');
        $result = $this->Article_model->edit_article($params);

        if (false === $result) {
            json_exit($this->Article_model->get_error());
        } else {
            // 将文章ID关联用户IP保存
            setcookie("customer_like_".md5($ip), intval($params['article_id']), time()+24*3600);
            json_exit('文章点赞成功', true);
        }
    }
}