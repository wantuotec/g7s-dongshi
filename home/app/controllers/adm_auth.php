<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author : Spike
 * @program: adm_auth.php
 * @create : Mar. 23, 2012
 * @update : Mar. 23, 2012
 */
class Adm_auth extends CI_Controller
{
    function index()
    {
        // 如果已经登录
        $operate_user = get_operate_user();
        if ($operate_user && $operate_user['userName'] != 'system') {
            url_redirect(HOME_DOMAIN . 'admin/');
        }

        /* 生成验证码 */
        $data = array();

        if (isset($_COOKIE['admin']['username']) && !empty($_COOKIE['admin']['username'])) {
            $data['username'] = $_COOKIE['admin']['username'];
        }

        $data['mode'] = 1; // 后台登录模式：1表示用username登录 2表示其它登录方式

        $this->load->view('adm/base/login_head.tpl');
        $this->load->view('adm/base/login.tpl', $data);
        $this->load->view('adm/base/foot.tpl');
    }

    /* 登录验证 */
    function login()
    {
        $params = $this->input->post();
        $this->load->helper('common');
        data_filter($params);

        if (empty($params['username'])) {
            json_exit('用户名不能为空', false, 'username');
        }

        if (empty($params['password'])) {
            json_exit('密码不能为空', false, 'password');
        }

        if (empty($params['captcha'])) {
            json_exit('验证码不能为空', false, 'captcha');
        }

        // 验证码验证
        $this->load->library('captcha');
        $result = $this->captcha->is_valid($params['captcha'], 'backend', 300);
        if (false === $result) {
            json_exit($this->captcha->get_error(), false, 'captcha');
        }

        // 进行登录
        $username = $params['username'];
        $password = $params['password'];
        $this->load->model('adm/User_model');
        if ($this->User_model->loginin($username, $password)) {
            // 记住用户名
            if(!isset($_COOKIE['admin']['username']) || $_COOKIE['admin']['username'] != $username) {
                setcookie('username', $username, time()+3600*24*30);
            }
            // 登录成功，跳转后台首页
            $url = HOME_DOMAIN . 'admin/';

            json_exit('成功', true, $url);
        } else {
            json_exit($this->User_model->get_error(), false, 'password');
        }
    }
    
    /* 用户登出 */
    function loginout()
    {
        $this->load->model('adm/User_model');
        $this->User_model->loginout("location: " . HOME_DOMAIN . 'adm_auth/');
    }
    
    /*返回当前用户 id */
    function get_current_user_id()
    {
        return $_SESSION['admin']['user_id'];
    }
    
     /*返回用户资料*/
    function get_current_user_name()
    {
     // $this->load->model('adm/User_model');
      //  return $this->User_model->get_by_user_id() $_SESSION['user_id'];
    }
    
    /* 重设密码 */
    function reset_password()
    {
        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/base/reset_password.tpl');
        $this->load->view('adm/base/foot.tpl');
    }
    
     /* 重设密码 */
    function do_reset_password()
    {
        validate_priv('all');

        $params = $this->input->post();

        if (empty($params['is_ajax'])) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('password',  '原密码', 'required|min_length[6]');
            $this->form_validation->set_rules('password1', '新密码', 'required|min_length[6]');
            $this->form_validation->set_rules('password2', '确认密码', 'required|min_length[6]');
            if ($this->form_validation->run() == false)
                 show_msg(validation_errors(), 'javascript:history.back();');

            $password  = $this->input->post('password', true);
            $password1 = $this->input->post('password1',true);
            $password2 = $this->input->post('password2',true);
     
            if($password1 != $password2){
                show_msg('两次输入的密码不一致', 'javascript:history.back();');
            }

            $this->load->model('adm/User_model');
            if ($this->User_model->is_weak_password($password1)) {
                show_msg('您的密码过于简单，密码应该为8位以上的英文数字组合', 'javascript:history.back();');
            }

            if(!$this->User_model->change_pass($password, $password1)){
                show_msg($this->User_model->get_error(), 'javascript:history.back();');
            }
            show_msg('密码修改已成功,请重新登录, <a href="/adm_auth/loginout" target="_top">重新登录</a>');
        } else {
            $message = '';

            if (empty($params['password']) || empty($params['password1']) || empty($params['password2'])) {
                $message[] = '密码不能为空';
            }

            if ($params['password1'] != $params['password2']) {
                $message[] = '两次输入的密码不一致';
            }

            $this->load->model('adm/User_model');
            if ($this->User_model->is_weak_password($params['password1'])) {
                $message[] = '您的密码过于简单，密码应该为8位以上的英文数字组合';
            }

            if (!empty($message)) {
                json_exit(implode("\n", $message));
            }

            if(!$this->User_model->change_pass($params['password'], $params['password1'])){
                json_exit($this->User_model->get_error());
            } else {
                json_exit('成功', true, HOME_DOMAIN . 'adm_auth/loginout');
            }
        }
    }

    /**
     * 获取验证码
     * 
     * @access  public
     *
     * @return  void
     */
    public function captcha()
    {
        $useage = $this->input->get('useage');
        $this->load->helper('common');
        data_filter($useage);

        $this->__config = array(
            // 后台登陆验证码
            'backend' => array(
                'useage' => 'backend',
                'width'  => 80,
                'height' => 30,
                'expire' => 300,
            ),
        );

        // 现在只有 注册 reg 用到了注册码
        if (!isset($this->__config[$useage])) {
            url_404();
        }

        $this->load->library('captcha');
        $this->captcha->create($this->__config[$useage]);
        exit;
    }

    /**
     * 弱密码更正
     *
     * @access  public
     *
     * @return  void
     */
    public function weak_password()
    {
        validate_priv('all');

        $this->load->view('adm/base/head.tpl');
        $this->load->view('adm/base/weak_password.tpl');
        $this->load->view('adm/base/foot.tpl');
    }

    /**
     * 获取短信验证码
     * 
     * @access  public
     *
     * @return  void
     */
    public function get_sms_captcha()
    {
        if ($this->input->is_post_request()) {
            $params = $this->input->post();

            $this->load->model('adm/User_model');
            $result = $this->User_model->get_sms_captcha($params);

            if (false === $result) {
                json_exit($this->User_model->get_error());
            } else {
                json_exit('成功', true, $result);
            }
        } else {
            json_exit('请求异常');
        }
    }

    /* 短信登录验证 */
    function sms_login()
    {
        $params = $this->input->post();
        $this->load->helper('common');
        data_filter($params);

        if (empty($params['phone_number'])) {
            json_exit('手机号不能为空', false, 'phone_number');
        }

        if (empty($params['captcha'])) {
            json_exit('验证码不能为空', false, 'captcha');
        }

        if (empty($params['captcha_key'])) {
            json_exit('请获取验证码', false, 'captcha');
        }

        $this->load->model('adm/User_model');
        if ($this->User_model->sms_login($params)) {
            // 记住用户名
            if(!isset($_COOKIE['admin']['phone_number']) || $_COOKIE['admin']['phone_number'] != $params['phone_number'])
                setcookie('phone_number', $params['phone_number'], time()+3600*24*30);

            json_exit('成功', true, HOME_DOMAIN . 'home/');
        } else {
            json_exit($this->User_model->get_error(), false, 'password');
        }
    }
}
