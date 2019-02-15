<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-06-22
 * @category    adm_guestbook
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Adm_guestbook extends CI_Controller
{
    /**
     * 留言列表
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

        $this->load->model('adm/Guestbook_model');
        $result = $this->Guestbook_model->get_guestbook_list($params);

        // 获取留言类型配置
        $service_shop = [
            'service_name'   => 'member.guestbook.get_cfg',
            'service_params' => [],
        ];
        $this->load->library('requester');
        $cfg_result = $this->requester->request($service_shop);

        $this->load->library('pagination');
        $data = [
            'search'     => $params,
            'list'       => $result['list'],
            'cfg'        => $cfg_result['data'],
            'pagination' => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/guestbook/index.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 留言待审核列表
     *
     * @access  public
     *
     * @return  void
     */
    public function audit_list()
    {
        validate_priv('access');

        $params = $this->input->get();
        data_filter($params);

        if(isset($params['le_create_time']) && !empty($params['le_create_time'])) {
            $params['le_create_time'] = $params['le_create_time'] . ' 23:59:59';
        }

        $this->load->model('adm/Guestbook_model');
        $result = $this->Guestbook_model->get_audit_list($params);

        $this->load->library('pagination');
        $data = [
            'search'     => $params,
            'list'       => $result['list'],
            'pagination' => $this->pagination->get_page_bar($result['total']),
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/guestbook/audit_list.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 查看留言内容
     *
     * @access  public
     *
     * @return  void
     */
    public function message_detail()
    {
        $params = $this->input->get();
        data_filter($params);

        $this->load->model('adm/Guestbook_model');
        $result = $this->Guestbook_model->get_single_message($params);
        if (false === $result) {
            show_msg($this->Guestbook_model->get_error(), 'javascript:history.back();');
        }

        $data = [
            'search' => $params,
            'list'   => $result,
        ];

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/guestbook/message_detail.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 审核留言
     *
     * @access  public
     *
     * @return  void
     */
    public function set_audit()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);

        $this->load->model('adm/Guestbook_model');
        $result = $this->Guestbook_model->edit_guestbook($params);

        if(false === $result){
            json_exit($this->Guestbook_model->get_error());
        } else {
            json_exit('审核状态设置成功', true);
        }
    }

    /**
     * 删除留言
     *
     * @access  public
     *
     * @return  void
     */
    public function delete_guestbook()
    {
        validate_priv('edit');

        $params = $this->input->post();
        data_filter($params);
        $params['is_deleted'] = 1;

        $this->load->model('adm/Guestbook_model');
        $result = $this->Guestbook_model->edit_guestbook($params);

        if(false === $result){
            json_exit($this->Guestbook_model->get_error());
        } else {
            json_exit('删除留言成功', true);
        }
    }

    /**
     * 回复用户留言
     *
     * @access  public
     *
     * @return  void
     */
    public function admin_reply()
    {
        $this->load->model('adm/Guestbook_model');

        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $result = $this->Guestbook_model->admin_reply($params);
            if (false === $result) {
                json_exit($this->Guestbook_model->get_error());
            } else {
                json_exit('回复用户留言成功', true);
            }

        } else {
            $params = $this->input->get();
            data_filter($params);

            $result = $this->Guestbook_model->get_single_message($params);
            $data = ['search' => array_merge($params, $result)];

            $this->load->view('adm/base/head.tpl');
            $this->load->view('adm/guestbook/admin_reply.tpl', $data);
            $this->load->view('adm/base/foot.tpl');
        }
    }
}