<?php
/**
 * Member Request Factory
 *
 * @author      madesheng
 * @date        2016-12-13
 * @category    Member
 *
 * @version     $Id$
 */
class MY_Member_request extends MY_Request
{
    /**
     * 线上测试接口连通
     */
    public function test_api($params = [])
    {
        $this->access(array('public'));

        $out_params = [
            'name' => 'testApi',
            'age'  => 25,
            'sex'  => 'boy',
            'desc' => 'this is testing api...',
            'time' => date('Y-m-d'),
        ];

        return $out_params;

    }

    public function createSign()
    {
        $time = time();

        $arr = ['dreamma1993xiaoma1126', $time, 'kk445566jj'];
        sort($arr, SORT_STRING);
        $arr = implode($arr);
        $arr = sha1($arr);

        dump($arr, $time);
    }

    /**
     * 用户身份验证(是否已注册)
     *
     * @param    array    $params    输入参数
     *
     * @return   array
     */
    public function check_registered($params = [])
    {
        $this->access(array('public'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'member_phone' => empty($params['member_phone']) ? null : $params['member_phone'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'member_id',
                'member_phone',
                'is_registered',  // 是否已注册(1|2)
            ),
        );

        // 查询会员信息
        $this->load->model('Member_model');
        $result = $this->Member_model->check_registered($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 用户快捷登录(手机号+验证码,若是新注册用户，后续有填写邀请码操作)
     *
     * @param    array    $params    输入参数
     *
     * @return   array
     */
    public function quick_login($params = [])
    {
        $this->access(array('public'));
        $app_info = $this->get_app_info();
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'is_check_captcha'   => 1,  // APP和微信端均需要验证手机验证码
                'app_version'        => $app_info['app_version'],
                'app_type'           => empty($app_info['app_type'])         ? null : $app_info['app_type'],
                'uuid'               => empty($app_info['uuid'])             ? null : $app_info['uuid'],
                'captcha_key'        => empty($params['captcha_key'])        ? null : $params['captcha_key'],
                'captcha_content'    => empty($params['captcha_content'])    ? null : $params['captcha_content'],
                'member_phone'       => empty($params['member_phone'])       ? null : $params['member_phone'],
                'invite_code'        => empty($params['invite_code'])        ? null : $params['invite_code'],
                'push_token'         => empty($params['push_token'])         ? null : $params['push_token'],
                'devices_sn'         => empty($params['devices_sn'])         ? null : $params['devices_sn'],
                'is_access_protocol' => empty($params['is_access_protocol']) ? 2    : $params['is_access_protocol'],
                'is_weixin'          => isset($params['is_weixin']) && $params['is_weixin'] ? true : false,  // 是否来自微信端
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'member_id',
                'member_session',
                'session_expire_time',
                'member_phone',
                'login_type',  //登录类型(新注册/老用户)
                'coupons',     //领取的优惠券列表
            ),
        );

        // 查询会员信息
        $this->load->model('Member_model');
        $result = $this->Member_model->quick_login($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 新用户注册完后，填写邀请码操作
     *
     * @param    array    $params    输入参数
     *
     * @return   array
     */
    public function input_invite_code($params = array())
    {
        $this->access(array('member'));
        $app_info = $this->get_app_info();
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'     => intval($userinfo['member_id']),
                'member_phone'  => isset($params['member_phone']) ? $params['member_phone']      : null,
                'invite_code'   => isset($params['invite_code'])  ? $params['invite_code']       : null,
                'app_type'      => isset($app_info['app_type'])   ? $app_info['app_type']        : null,
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => array(
                'member_id',
                'member_phone',
                'member_session',
                'session_expire_time',
            ),
        );

        // 执行邀请码操作
        $this->load->model('Member_model');
        $result = $this->Member_model->input_invite_code($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 检验是否能直接微信登录
     *
     * @param    array    $params    输入参数
     *
     * @return   array
     */
    public function weixin_check_login($params = [])
    {
        $this->access(array('public'));
        $app_info = $this->get_app_info();
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'weixin_uid'  => empty($params['weixin_uid'])    ? null : $params['weixin_uid'],
                'app_type'    => empty($app_info['app_type'])    ? null : $app_info['app_type'],
                'app_version' => empty($app_info['app_version']) ? null : $app_info['app_version'],
                'uuid'        => empty($app_info['uuid'])        ? null : $app_info['uuid'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'login_type',  // 登录类型（1.已直接登录，2.去绑定手机）
                'member_id',
                'member_session',
                'session_expire_time',
                'member_phone',
            ),
        );

        // 查询会员信息
        $this->load->model('Member_model');
        $result = $this->Member_model->weixin_check_login($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 微信绑定手机&登录(微信直接登录失败后,跳到此步)
     *
     * @param    array    $params    输入参数
     *
     * @return array
     */
    public function weixin_login($params = [])
    {
        $this->access(array('public'));
        $app_info = $this->get_app_info();
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'public' => array(
                'weixin_uid'      => empty($params['weixin_uid'])      ? null : $params['weixin_uid'],
                'nickname'        => empty($params['nickname'])        ? null : $params['nickname'],
                'sex'             => empty($params['sex'])             ? null : $params['sex'],
                'head_image'      => empty($params['head_image'])      ? null : $params['head_image'],
                'captcha_key'     => empty($params['captcha_key'])     ? null : $params['captcha_key'],
                'captcha_content' => empty($params['captcha_content']) ? null : $params['captcha_content'],
                'member_phone'    => empty($params['member_phone'])    ? null : $params['member_phone'],
                'push_token'      => empty($params['push_token'])      ? null : $params['push_token'],
                'invite_code'     => empty($params['invite_code'])     ? null : $params['invite_code'],
                'devices_sn'      => empty($params['devices_sn'])      ? null : $params['devices_sn'],
                'app_type'        => empty($app_info['app_type'])      ? null : $app_info['app_type'],
                'app_version'     => empty($app_info['app_version'])   ? null : $app_info['app_version'],
                'uuid'            => empty($app_info['uuid'])          ? null : $app_info['uuid'],
            ),
        );

        // 输出参数
        $output_format = array(
            'public' => array(
                'member_id',
                'member_session',
                'session_expire_time',
                'member_phone',
            ),
        );

        // 查询会员信息
        $this->load->model('Member_model');
        $result = $this->Member_model->weixin_login($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            return $this->group_params($output_format, $result, 1);
        }
    }

    /**
     * 用户登出
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function logout($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'push_token'     => filter_empty('push_token', $params),
                'member_id'      => filter_empty('member_id', $params),
                'member_session' => filter_empty('member_session', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => array(),
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->member_logout($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 用户上传push_token
     *
     * @param   array   $params   输入参数
     *
     * @return  bool
     */
    public function upload_push_token($params = array())
    {
        $this->access(array('member'));
        $app_info = $this->get_app_info();
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'   => intval($userinfo['member_id']),
                'push_token'  => filter_empty('push_token', $params),
                'app_version' => $app_info['app_version'],
            ),
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->upload_push_token($params[$userinfo['group']]);

        // 正确或者错误都返回 false
        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        }

        return [];
    }

    /**
     * 用户获取地址列表
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_address_list($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'        => filter_empty('member_id', $params),
                'market_id'        => filter_empty('market_id', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'address_id',
                'name',
                'phone',
                'address',
                'longitude',
                'latitude',
                'is_usable',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_address_list($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                $result['list'] = $this->group_params($output_format, $result['list'], 2);
                return $result;
            }
        }
    }

    /**
     * 用户新增收货地址
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function add_address($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'      => filter_empty('member_id', $params),
                'name'           => filter_empty('name', $params),
                'phone'          => filter_empty('phone', $params),
                'address'        => filter_empty('address', $params),
                'address_detail' => filter_empty('address_detail', $params),
                'room_number'    => filter_empty('room_number', $params),
                'latitude'       => filter_empty('latitude', $params),
                'longitude'      => filter_empty('longitude', $params),
                'validate_type'  => 'add',  // 数据检验类型为添加，后面对一些参数做验证
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'address_id'
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->add_address($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 用户获取收货地址详情数据（修改时用）
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_address_detail($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'address_id' => filter_empty('address_id', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'address_id',
                'name',
                'phone',
                'address',
                'address_detail',
                'room_number',
                'latitude',
                'longitude',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_address_detail($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 保存用户编辑的收货地址
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function save_edit_address($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'address_id'     => filter_empty('address_id', $params),
                'name'           => filter_empty('name', $params),
                'phone'          => filter_empty('phone', $params),
                'address'        => filter_empty('address', $params),
                'address_detail' => filter_empty('address_detail', $params),
                'room_number'    => filter_empty('room_number', $params),
                'latitude'       => filter_empty('latitude', $params),
                'longitude'      => filter_empty('longitude', $params),
                'validate_type'  => 'edit',  // 数据检验类型为编辑，后面对一些参数做验证
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'address_id'
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->save_edit_address($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 用户删除收货地址（可批量删除）
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function delete_address($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'   => filter_empty('member_id', $params),
                'address_ids' => filter_empty('address_ids', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->delete_address($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 获取用户相关配置项(年薪等级、家庭人口)
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_member_config($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id' => filter_empty('member_id', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'annual_salary_level',
                'family_population',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_member_config($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }


    /**
     * 获取用户资料详情
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_info($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id' => filter_empty('member_id', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'member_id',
                'member_name',
                'member_phone',
                'type',
                'is_enabled',
                'balance',
                'invite_code',
                'invite_total',
                'total_spending',
                'bind_market_id',
                'age',
                'birthday',
                'sex',
                'profession',
                'head_image',
                'family_population',
                'annual_salary_level',
                'share_url',
                'bank_total',
                'coupon_total',
                'is_clock',
                'is_edit',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_info($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 编辑用户个人资料
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function save_edit_info($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'           => filter_empty('member_id', $params),
                'member_name'         => filter_empty('member_name', $params),
                'birthday'            => filter_empty('birthday', $params),
                'sex'                 => filter_empty('sex', $params),
                'profession'          => filter_empty('profession', $params),
                'head_image'          => filter_empty('head_image', $params),
                'family_population'   => filter_empty('family_population', $params),
                'annual_salary_level' => filter_empty('annual_salary_level', $params),
                'member_name_in_db'   => $userinfo['member_name'],
                'waxiao_member_id'    => $userinfo['waxiao_member_id'],
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'member_id',
                'age',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->save_edit_info($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * 保存用户意见反馈
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function save_feedback($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'    => filter_empty('member_id', $params),
                'member_phone' => $userinfo['member_phone'],
                'content'      => filter_empty('content', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'feedback_id',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->save_feedback($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!is_array($result)) {
                return [];
            } else {
                return $this->group_params($output_format, $result, 1);
            }
        }
    }

    /**
     * C端用户收藏店铺列表
     *
     * @param array $params
     *          int market_id 菜场id
     *          int member_id 用户id
     *          string member_session 用户session
     *          int page_no 当前页数
     *          int page_size 每页显示条数
     *
     * @return array|bool
     * @throws Exception
     */
    public function get_favorite_list($params = [])
    {
        // 权限验证
        $this->access(array('member'));

        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'market_id'         => empty($params['market_id'])      ? null : intval($params['market_id']),
                'member_id'         => empty($params['member_id'])      ? null : $params['member_id'],
                'member_session'    => empty($params['member_session']) ? null : $params['member_session'],
                'page_no'           => empty($params['page_no'])        ? 1    : $params['page_no'],
                'page_size'         => empty($params['page_size'])      ? 10   : $params['page_size'],
                'is_pages'          => true
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => array(
                'shop_list','total','page_no','page_size'
            ),
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_favorite_list($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            $result = $this->group_params($output_format, $result,1);
            return $result;
        }
    }

    // /**
    //  * 用户收藏店铺
    //  *
    //  * @param array $params
    //  *          int shop_id 店铺id
    //  *          int member_id 用户id
    //  *          string member_session 用户session
    //  *
    //  * @return array|bool
    //  * @throws Exception
    //  */
    // public function favorite_shop($params = [])
    // {
    //     // 权限验证
    //     $this->access(array('member'));
    //     $userinfo = $this->get_user_info();

    //     // 输入参数
    //     $params = array(
    //         'member' => array(
    //             'shop_id'        => empty($params['shop_id'])        ? null : intval($params['shop_id']),
    //             'member_id'      => empty($params['member_id'])      ? null : $params['member_id'],
    //             'member_session' => empty($params['member_session']) ? null : $params['member_session']
    //         ),
    //     );

    //     // 输出参数
    //     $output_format = array(
    //         'member' => array(),
    //     );

    //     $this->load->model('Member_model');
    //     $result = $this->Member_model->favorite_shop($params[$userinfo['group']]);
    //     if (false === $result) {
    //         $this->set_error($this->Member_model->get_error());
    //         return false;
    //     }

    //     return $this->group_params($output_format, [], 1);
    // }

    // /**
    //  * 用户取消店铺收藏
    //  *
    //  * @param array $params
    //  *          int shop_id 店铺id
    //  *          int member_id 用户id
    //  *          string member_session 用户session
    //  *
    //  * @return array|bool
    //  * @throws Exception
    //  */
    // public function cancel_favorite_shop($params = [])
    // {
    //     // 权限验证
    //     $this->access(array('member'));
    //     $userinfo = $this->get_user_info();

    //     // 输入参数
    //     $params = array(
    //         'member' => array(
    //             'shop_id'        => empty($params['shop_id'])        ? null : intval($params['shop_id']),
    //             'member_id'      => empty($params['member_id'])      ? null : $params['member_id'],
    //             'member_session' => empty($params['member_session']) ? null : $params['member_session']
    //         ),
    //     );

    //     // 输出参数
    //     $output_format = array(
    //         'member' => array(),
    //     );

    //     $this->load->model('Member_model');
    //     $result = $this->Member_model->cancel_favorite_shop($params[$userinfo['group']]);
    //     if (false === $result) {
    //         $this->set_error($this->Member_model->get_error());
    //         return false;
    //     }

    //     return $this->group_params($output_format, [], 1);
    // }

    /**
     * 用户(合伙人)邀请人列表
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_invitees_list($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id' => filter_empty('member_id', $params),
                'page_no'   => empty($params['page_no'])        ? 1    : $params['page_no'],
                'page_size' => empty($params['page_size'])      ? 10   : $params['page_size'],
                'is_pages'  => true,
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'list',
                'total',
                'order_total',
                'pages'
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_invitees_list($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
        } else {
            if (is_array($result) && $result) {
                return $this->group_params($output_format, $result, 1);
            }

            return $result;
        }
    }

    /**
     * 获取用户邀请分享(app个人邀请记录中分享)
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function get_share_activity($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id'   => filter_empty('member_id', $params),
                'invite_code' => $userinfo['invite_code'],
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'weixin',
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_share_activity($params[$userinfo['group']]);

        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
        } else {
            if (is_array($result) && $result) {
                return $this->group_params($output_format, $result, 1);
            }
            return $result;
        }
    }

    /**
     * 用户每日签到操作
     *
     * @param    array    $params    参数
     *
     * @return   array
     */
    public function clock($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id' => filter_empty('member_id', $params),
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->clock($params[$userinfo['group']]);
        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
        }

        return $this->group_params($output_format, $result, 1);
    }

    /**
     * 我的奖品列表接口
     *
     * @param    array    $params    参数
     *
     * @return   array|bool
     */
    public function award_list($params = [])
    {
        $this->access(array('member'));
        $userinfo = $this->get_user_info();

        // 输入参数
        $params = array(
            'member' => array(
                'member_id' => filter_empty('member_id', $params),
                'page_no'   => empty($params['page_no'])   ? 1  : $params['page_no'],
                'page_size' => empty($params['page_size']) ? 10 : $params['page_size'],
                'is_pages'  => true
            ),
        );

        // 输出参数
        $output_format = array(
            'member' => [
                'title',        // 奖品名称
                'create_time'   // 获奖时间
            ],
        );

        $this->load->model('Member_model');
        $result = $this->Member_model->get_award_list($params[$userinfo['group']]);
        if (false === $result) {
            $this->set_error($this->Member_model->get_error());
            return false;
        } else {
            if (!isset($params[$userinfo['group']]['page_no']) || $params[$userinfo['group']]['page_no'] == 1) {
                $result['head_image'] = get_cdn_domain(ENVIRONMENT) . $userinfo['head_image'];

                $this->load->model('Award_model');
                $image_result = $this->Award_model->get_images();
                if (false === $image_result) {
                    $this->set_error($this->Award_model->get_error());
                    return false;
                }

                $result = array_merge($result, $image_result);
            }

            if (empty($result['list'])) {
                return $result;
            } else {
                $result['list'] = $this->group_params($output_format, $result['list'], 2);
                return $result;
            }
        }
    }
}
