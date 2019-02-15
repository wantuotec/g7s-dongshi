<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author      madesheng
 * @date        2017-02-06
 * @category    Guestbook.php
 * @copyright   Copyright(c) 2017
 * @version     $Id:$
 */
class Guestbook extends CI_Controller
{
    /*
    * 留言首页
    */
    public function index()
    {
        // 获取用户留言列表(基于当前能看到的)
        $this->load->model('Guestbook_model');
        $result = $this->Guestbook_model->get_guestbook_list();

        // 获取留言控制
        $service_shop = [
            'service_name'   => 'operation.module_manage.get',
            'service_params' => ['module_mark' => 2],
        ];
        $this->load->library('requester');
        $module_result = $this->requester->request($service_shop);

        // 获取网站顶部slogan
        $this->load->model('Slogan_model');
        $slogan_result = $this->Slogan_model->get_single_slogan(['item_type' => 8]);

        $this->load->library('home_pagination');
        $data = [
            'slogan'      => $slogan_result['content'],
            'module_open' => $module_result['data']['is_open'],
            'list'        => $result['list'],
            'pagination'  => $this->home_pagination->get_page_bar($result['total']),
        ];

        $this->load->view('base/header.tpl', ['current_title' => 'guestbook']);
        if (1 == $module_result['data']['is_open']) {
            $this->load->view('guestbook/index.tpl', $data);
        } else {
            $this->load->view('404/wait.tpl', $data);
        }
        $this->load->view('base/footer.tpl');
    }

    /*
    * 添加留言
    */
    public function add_message()
    {
        $params = $this->input->post();

        //获取留言用户的IP地址
        $params['ip'] = $this->input->ip_address();

        $this->load->model('Guestbook_model');
        $result = $this->Guestbook_model->add_message($params);

        if (false === $result) {
            json_exit($this->Guestbook_model->get_error());
        } else {
            json_exit('└(^o^)┘留言成功了，小编会尽快回复的', true);
        }
    }

    /*
    * 添加留言回复
    */
    public function add_reply()
    {
        $params = $this->input->post();

        //获取回复用户的IP地址
        $params['ip'] = $this->input->ip_address();

        $this->load->model('Guestbook_model');
        $result = $this->Guestbook_model->add_message($params);

        if (false === $result) {
            json_exit($this->Guestbook_model->get_error());
        } else {
            json_exit('└(^o^)┘回复成功啦!小编会仔细研读的', true);
        }
    }

    //临时方法，将留言用户IP转地址
    public function ip_to_addr()
    {
        $service_info = [
            'service_name'   => 'member.guestbook.get_list',
            'service_params' => [
                // 'type'       => 1,  //用户和管理员IP都转
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (true != $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as $val) {
                if (!empty($val['ip'])) {
                    $addr_temp = getTaobaoAddress($val['ip']);
                    $addr_data = $addr_temp['data'];
                    if ('内网IP' != $addr_data['country']) {
                        $address = $addr_data['country'] . $addr_data['area'] . $addr_data['region'] . $addr_data['city'] . '-' . $addr_data['isp'];
                    } else {
                        $address = $val['ip'];
                    }

                    $edit_data = [
                        'guestbook_id' => $val['guestbook_id'],
                        'set' => ['address' => $address],
                    ];
                    $service_info = [
                        'service_name'   => 'member.guestbook.update_by_params',
                        'service_params' => $edit_data,
                    ];
                    $result = $this->requester->request($service_info);
                }
            }
        }

        exit('===========SUCCESS!!!============');
    }
}