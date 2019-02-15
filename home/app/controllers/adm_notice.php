<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-04-14
 * @category    adm_notice
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_notice extends CI_Controller
{
    /**
     * 心情杂记列表
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

        $this->load->model('adm/Notice_model');
        $result = $this->Notice_model->get_notice_list($params);

        $this->load->library('pagination');
        $data = [
            'search'     => $params,
            'list'       => $result['list'],
            'pagination' => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/notice/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 新增公告
     *
     * @access  public
     *
     * @return  void
     */
    public function add_notice()
    {
        validate_priv('addnew');

        $this->load->model('adm/Notice_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Notice_model->add_notice($params);
            if (false === $result) {
                json_exit($this->Notice_model->get_error());
            } else {
                json_exit('添加心情成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $data = [
                'search' => $params,
            ];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/notice/add_notice.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 查看心情内容
     *
     * @access  public
     *
     * @return  void
     */
    public function notice_detail()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Notice_model');
        $result = $this->Notice_model->get_single_notice($params);
        if (false == $result) {
            show_msg($this->Notice_model->get_error(), 'javascript:history.back();');
        }

        $data = ['list' => $result];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/notice/notice_detail.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 编辑心情信息
     *
     * @access  public
     *
     * @return  void
     */
    public function edit_notice()
    {
        validate_priv('edit');

        $this->load->model('adm/Notice_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Notice_model->edit_notice($params);
            if (false === $result) {
                json_exit($this->Mood_model->get_error());
            } else {
                json_exit('网站公告修改成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $result = $this->Notice_model->get_single_notice($params);
            $data = ['search' => array_merge($params, $result)];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/notice/edit_notice.tpl', $data);
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

        $this->load->model('adm/Notice_model');
        $result = $this->Notice_model->edit_notice($params);

        if(false == $result){
            json_exit($this->Notice_model->get_error());
        } else {
            json_exit('修改成功', true);
        }
    }
}