<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-17
 * @category    Adm_article_comment
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_article_comment extends CI_Controller
{
    /**
     * 文章评论列表
     *
     * @access  public
     *
     * @return  void
     */
    public function index()
    {
        validate_priv('access', 'audit_list');

        $params = $this->input->get();
        data_filter($params);

        !empty($params['le_create_time']) && $params['le_create_time'] = $params['le_create_time'] . ' 23:59:59';
        $this->load->model('adm/Article_comment_model');
        $result = $this->Article_comment_model->get_member_comment_list($params);

        $service_info = [
            'service_name'   => 'article.article_comment.get_cfg',
            'service_params' => [],
        ];
        $this->load->library('requester');
        $cfg_result = $this->requester->request($service_info);

        $this->load->library('pagination');

        $data = [
            'search'     => $params,
            'cfg'        => $cfg_result['data'],
            'list'       => $result['list'],
            'pagination' => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article_comment/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 文章评论审核列表
     *
     * @access  public
     *
     * @return  void
     */
    public function audit_list()
    {
        validate_priv('access', 'audit_list');

        $params = $this->input->get();
        data_filter($params);

        $params['type']         = 1;
        $params['audit_status'] = 1;
        !empty($params['le_create_time']) && $params['le_create_time'] = $params['le_create_time'] . ' 23:59:59';
        $this->load->model('adm/Article_comment_model');
        $result = $this->Article_comment_model->get_member_comment_list($params);

        $this->load->library('pagination');

        $data = [
            'search'     => $params,
            'list'       => $result['list'],
            'pagination' => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article_comment/audit_list.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 查看评论内容
     *
     * @access  public
     *
     * @return  void
     */
    public function comment_detail()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Article_comment_model');
        $result = $this->Article_comment_model->get_single_comment($params);
        if (false == $result) {
            show_msg($this->Article_comment_model->get_error(), 'javascript:history.back();');
        }

        $data = [
            'search' => $params,
            'list'   => $result,
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/article_comment/comment_detail.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 回复用户评论
     *
     * @access  public
     *
     * @return  void
     */
    public function return_comment()
    {
        $this->load->model('adm/Article_comment_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Article_comment_model->return_comment($params);

            if (false === $result) {
                json_exit($this->Article_comment_model->get_error());
            } else {
                json_exit('审核成功', true);
            }
        } else {
            $params = $this->input->get();

            $result = $this->Article_comment_model->get_single_comment($params);

            if (in_array($result['audit_status'], [1, 3])) {
                show_msg('该评论当前未审核通过，不能执行回复操作！');
                exit;
            }

            $data = [
                'search' => $params,
                'list'   => $result,
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/article_comment/return_comment.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 文章评论审核操作
     *
     * @access  public
     *
     * @return  void
     */
    public function audit()
    {
        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Article_comment_model');
        $result = $this->Article_comment_model->edit_article($params);

        if (false === $result) {
            json_exit($this->Article_comment_model->get_error());
        } else {
            json_exit('审核操作成功', true);
        }
    }

    /**
     * 文章评论批量审核通过操作
     *
     * @access  public
     *
     * @return  void
     */
    public function batch_audit_ok()
    {
        if ($this->input->is_post_request()) {
            $params = $this->input->post();
            data_filter($params);

            $this->load->model('adm/Article_comment_model');
            $result = $this->Article_comment_model->batch_audit_ok($params);

            if (false === $result) {
                json_exit($this->Article_comment_model->get_error());
            } else {
                json_exit('审核通过操作成功', true);
            }
        }
    }
}