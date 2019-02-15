<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-18
 * @category    adm_article_category
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_article_category extends CI_Controller
{
    /**
     * 文章分类列表
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

        $this->load->model('adm/Article_category_model');
        $result = $this->Article_category_model->get_article_category_list($params);

        $data = [
            'search' => $params,
            'list'   => $result['list'],
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article_category/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 新增文章分类
     *
     * @access  public
     *
     * @return  void
     */
    public function add_category()
    {
        validate_priv('addnew');

        $this->load->model('adm/Article_category_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Article_category_model->add_category($params);
            if (false === $result) {
                json_exit($this->Article_category_model->get_error());
            } else {
                json_exit('添加文章分类成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $data = [
                'search' => $params,
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/article_category/add_category.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 编辑文章分类
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_category()
    {
        validate_priv('edit');

        $this->load->model('adm/Article_category_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $result = $this->Article_category_model->edit_category($params);
            if (false === $result) {
                json_exit($this->Article_category_model->get_error());
            } else {
                json_exit('文章分类编辑成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $result = $this->Article_category_model->get_single_category($params);
            $data = ['search' => $result];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/article_category/edit_category.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 设置心情有效性
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

        $this->load->model('adm/Article_category_model');
        $result = $this->Article_category_model->edit_category($params);

        if(false == $result){
            json_exit($this->Article_category_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}