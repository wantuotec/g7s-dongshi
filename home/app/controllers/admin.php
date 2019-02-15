<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 网站后台管理
 *
 * @author      madesheng
 * @date        2017-01-22
 * @category    Admin.php
 * @copyright   Copyright(c) 2017
 *
 * @version     $Id:$
 */
class Admin extends CI_Controller
{
    function index()
    {
        if(!isset($_SESSION['admin']['user_id'])) {
            header("location: " . HOME_DOMAIN . 'adm_auth/');
        }

        $this->load->view('adm/base/index.tpl');
    }

    /* 侧边栏 */
    function siderbar()
    {
        if(!isset($_SESSION['admin']['user_id'])) {
            header("location: " . HOME_DOMAIN . 'adm_auth/');
        }

        $data = array();
        $data['userName'] = $_SESSION['admin']['userName'];
        $this->load->model('adm/User_model');
        $data['menu'] = $this->exchange_sort($this->User_model->get_user_menu());

        foreach($data['menu'] as $k=>$v)
        {
           if(!$data['menu'][$k]['is_display'])
           {
                unset($data['menu'][$k]['is_display']);
           }
           else
           {
                foreach ($data['menu'][$k]['nav'] as $k2=>$v2)
                {
                     if(!$data['menu'][$k]['nav'] [$k2]['is_display'])
                       {
                            unset($data['menu'][$k]['nav'] [$k2]);
                       }
                }
           }
        }
        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/base/siderbar.tpl',$data);
        $this->load->view('adm/base/foot.tpl');
    }

    // 交换法排序
    function exchange_sort($arr) {
        $arr = array_slice($arr,0,count($arr));
        $num = count($arr);
        for ($i = 0; $i < $num; $i++ )
        {
            for ($j = $i + 1; $j < $num ; $j++ )
            {
                if($arr[$i]['sort']>$arr[$j]['sort'])
                {
                    $temp    = $arr[$i];
                    $arr[$i] = $arr[$j];
                    $arr[$j] = $temp;
                }
            }
        }
        return $arr;
    }

    /* 后台首页 */
    function info()
    {
        $this->load->model('adm/User_model');
        $data['menu'] = $this->exchange_sort($this->User_model->get_user_menu());

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/base/info.tpl',$data); 
        $this->load->view('adm/base/foot.tpl');
    }

    /* 主体部分 */
    function template()
    {
        validate_priv('access','template');

        // 图表展示
        $chart = array(
            'title' => '每日配送报表',
            'xaxis' => ['data' => ['1号','2号','3号','4号','5号','6号','7号','8号','9号','10号','11号','12号','13号','14号','15号','16号','17号']],
            'yaxis' => ['formatter' => '单'],
            'datas'  => [
                ['legend' => '上海总单量', 'data' => [2144, 2376, 2114, 2149, 2285, 2902, 3002, 3073, 3240, 3541, 3429, 2906, 3050, 4656, 9457, 9494, 9239]],
                ['legend' => '南京总单量', 'data' => [960, 865, 726, 453, 481, 1042, 1173, 1195, 1199, 1137, 957, 757, 767, 1193, 1182, 1143, 1308]],
                ['legend' => '杭州总单量', 'data' => [1106, 897, 630, 717, 547, 1008, 1251, 1410, 1387, 1404, 1315, 1035, 1002, 1677, 1985, 1841, 2250]],
                ['legend' => '全部总单量', 'data' => [6499, 6621, 5443, 4967, 5289, 7739, 8768, 9245, 9614, 9819, 9405, 7561, 7760, 10757, 12587, 12359, 12709]],
                ['legend' => '目标总单量', 'data' => [8090, 9395, 10120, 11690, 10370, 11115, 14860, 16005, 17680, 18550, 19595, 17430, 17665, 21300, 22340, 23105, 24430]],
            ],
        );

        $this->load->library('chart');

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/base/template.tpl', array('chart' => $this->chart->line($chart)));
        $this->load->view('adm/base/foot.tpl');
    }
}