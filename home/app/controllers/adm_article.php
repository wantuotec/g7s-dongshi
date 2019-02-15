<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-17
 * @category    adm_article
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_article extends CI_Controller
{
    /**
     * 文章列表
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access');

        $params = $this->input->get();
        data_filter($params);

        if(isset($params['le_create_time']) && !empty($params['le_create_time'])) {
            $params['le_create_time'] = $params['le_create_time'] . ' 23:59:59';
        }

        $this->load->model('adm/Article_model');
        $result = $this->Article_model->get_article_list($params);

        // 获取文章分类
        $this->load->model('adm/Article_category_model');
        $article_category = $this->Article_category_model->get_article_category_list(['fields' => 'article_category_id, category_name', 'is_enabled' => 1]);

        $this->load->library('pagination');
        $data = [
            'search'        => $params,
            'category_list' => $article_category['list'],
            'list'          => $result['list'],
            'pagination'    => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 新增心情
     *
     * @access  public
     *
     * @return  void
     */
    public function add_article()
    {
        validate_priv('addnew');

        $this->load->model('adm/Article_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Article_model->add_article($params);
            if (false === $result) {
                json_exit($this->Article_model->get_error());
            } else {
                json_exit('添加心情成功', true);
            }
        } else {
            $params = $this->input->get();

            // 获取文章分类
            $this->load->model('adm/Article_category_model');
            $article_category = $this->Article_category_model->get_article_category_list(['fields' => 'article_category_id, category_name', 'is_enabled' => 1]);

            // 获取文章来源配置
            $service_shop = [
                'service_name'   => 'article.article.get_cfg',
                'service_params' => [],
            ];
            $this->load->library('requester');
            $cfg_result = $this->requester->request($service_shop);

            $data = [
                'search'        => $params,
                'category_list' => $article_category['list'],
                'origin_type'   => $cfg_result['data']['origin_type'],
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/article/add_article.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 查看文章内容
     *
     * @access  public
     *
     * @return  void
     */
    public function article_detail()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Article_model');
        $result = $this->Article_model->get_single_article($params);
        if (false == $result) {
            show_msg($this->Article_model->get_error(), 'javascript:history.back();');
        }

        $data = [
            'search' => $params,
            'list'   => $result,
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article/article_detail.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 编辑心情信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_article()
    {
        validate_priv('edit');

        $this->load->model('adm/Article_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Article_model->edit_article($params);
            if (false === $result) {
                json_exit($this->Article_model->get_error());
            } else {
                json_exit('文章编辑成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            // 获取文章信息
            $result = $this->Article_model->get_single_article($params);

            // 获取文章分类
            $this->load->model('adm/Article_category_model');
            $article_category = $this->Article_category_model->get_article_category_list(['fields' => 'article_category_id, category_name', 'is_enabled' => 1]);

            // 获取文章来源配置
            $service_shop = [
                'service_name'   => 'article.article.get_cfg',
                'service_params' => [],
            ];
            $this->load->library('requester');
            $cfg_result = $this->requester->request($service_shop);

            $data = [
                'search'        => $result,
                'category_list' => $article_category['list'],
                'origin_type'   => $cfg_result['data']['origin_type'],
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/article/edit_article.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 设置有效性
     *
     * @access  public
     *
     * @return  void
     */
    public function set_enabled()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Article_model');
        $result = $this->Article_model->set_enabled($params);

        if(false == $result){
            json_exit($this->Article_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }

    /**
     * 设置首页推荐
     *
     * @access  public
     *
     * @return  void
     */
    public function set_recommend()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Article_model');
        $result = $this->Article_model->set_recommend($params);

        if(false == $result){
            json_exit($this->Article_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }

    /**
     * 修改排序
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_sort()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Article_model');
        $result = $this->Article_model->edit_sort($params);

        if(false == $result){
            json_exit($this->Article_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}