<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-26
 * @category    adm_configure
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_configure extends CI_Controller
{
    /**
     * 获取列表
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

        $params['is_pages'] = true;
        $params['order_by'] = 'configure_id DESC';
        $this->load->model('adm/Configure_model');
        $configure_result = $this->Configure_model->get_list($params);

        $this->load->library('pagination');

        $data = array(
            'search'     => $params,
            'list'       => $configure_result['list'],
            'type'       => $this->Configure_model->type,
            'pagination' => $this->pagination->get_page_bar($configure_result['total'], $params['page_size']),
        );

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/configure/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 详情展示
     *
     * @access  public
     *
     * @return  void
     */
    public function detail()
    {
        validate_priv('access');

        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Configure_model');
        $configure_result = $this->Configure_model->get_by_id($params['configure_id']);

        $data = array(
            'search'    => $params,
            'configure' => $configure_result,
        );

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/configure/detail.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 添加
     *
     * @access  public
     *
     * @return  void
     */
    public function add()
    {
        validate_priv('addnew');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            array_map_recursive('urlencode', $params);
            data_filter($params);

            $this->load->model('adm/Configure_model');
            $result = $this->Configure_model->add_configure($params);

            if (false === $result) {
                json_exit($this->Configure_model->get_error());
            } else {
                json_exit('添加成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $this->load->model('adm/Configure_model');
            $configure_result = $this->Configure_model->get_by_id($params['configure_id']);

            $data = array(
                'configure' => $configure_result,
            );

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/configure/add.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

    /**
     * 修改
     *
     * @access  public
     *
     * @return  void
     */
    public function edit()
    {
        validate_priv('edit');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            array_map_recursive('urlencode', $params);
            data_filter($params);

            $this->load->model('adm/Configure_model');
            $result = $this->Configure_model->edit_configure($params);

            if (false === $result) {
                json_exit($this->Configure_model->get_error());
            } else {
                json_exit('修改成功', true);
            }
        } else {
            $params = $this->input->get();
            data_filter($params);

            $this->load->model('adm/Configure_model');
            $configure_result = $this->Configure_model->get_by_id($params['configure_id']);

            $data = array(
                'search'    => $configure_result,
                'configure' => $configure_result,
            );

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/configure/edit.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }

}