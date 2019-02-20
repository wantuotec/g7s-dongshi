<?php

/**
 * 用户管理
 *
 * @author      madesheng
 * @date        2016-12-15
 * @category    member_model
 *
 * @version     $Id$
 */
class Member_model extends CI_Model
{
    /**
     * 通过member_id获取信息
     *
     * @param    $id        int       ID
     * @param    $fields    string    查询字段
     *
     * @return   array|bool
     */
    public function get_by_id($id = null, $fields = null)
    {
        data_filter($id);
        data_filter($fields);

        if (empty($id)) {
            $this->set_error(60122);
            return false;
        }

        // 获取项目信息
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_id' => intval($id),
                'fields'    => $fields,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['message']);
            return false;
        }

        // 可用积分
        $result['data']['usable_credit'] = $result['data']['credit'] - $result['data']['used_credit'];

        return $result['data'];
    }

    /**
     * 用户身份验证(是否已注册)
     *
     * @access   public
     *
     * @param    array    $params    登录数据
     *
     * @return   bool|int
     */
    public function check_registered($params = [])
    {
        data_filter($params);

        if (empty($params['member_phone'])) {
            $this->set_error(60121);
            return false;
        }

        // 验证是否有这个用户(此手机号是否存在记录)
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_phone' => $params['member_phone'],
                'fields'       => 'member_id, member_phone',
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        if (empty($result['data']['member_id']) || 0 == intval($result['data']['member_id'])) {
            return ['is_registered' => 2];
        } else {
            return array_merge($result['data'], ['is_registered' => 1]);
        }
    }

    /**
     * 用户快捷登录（包含登录跟注册）
     *
     * @access   public
     *
     * @param    array    $params    登录数据
     *
     * @return   bool|int
     */
    public function quick_login($params = [])
    {
        data_filter($params);

        // 是否通过菜场优惠协议
        if (empty($params['is_access_protocol']) || 1 != (int)$params['is_access_protocol']) {
            $this->set_error(60107);
            return false;
        }

        // 手机验证码
        if (empty($params['captcha_content'])) {
            $this->set_error(47003);
            return false;
        }

        // 检查是否是手机号或者座机号
        if (!is_phone($params['member_phone']) || empty($params['member_phone'])) {
            $this->set_error(60005);
            return false;
        }

        // 9951 为白名单用户通用验证码,不再对验证码相关去做验证
        if ('9951' == $params['captcha_content'] && $params['member_phone'] == '13500001111') {
            $params['is_check_captcha'] = 2;
        } else {
            if (empty($params['captcha_key'])) {
                $this->set_error(47001);
                return false;
            }
        }

        // 验证是否有这个用户(此手机号是否存在记录)
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_phone' => $params['member_phone'],
                'fields'       => 'member_id, member_phone, is_enabled',
            ],
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        $params['login_type'] = 1;  //默认走登录程序

        // 如果手机号码不存在就去注册
        if (empty($result['data']['member_id']) || 0 == intval($result['data']['member_id'])) {
            $service_info = [
                'service_name'   => 'member.member.register_member',
                'service_params' => $params,
            ];
            $register_result = $this->requester->request($service_info);

            if (false === $register_result['success']) {
                $this->set_error($register_result['errcode']);
                return false;
            }

            $params['is_check_captcha'] = 2; // 注册成功以后不需要再次验证验证码,因为在注册时候已经验证过
            $params['login_type']       = 2; // 新注册的用户

        // 如果手机号存在，且来自微信端，则返回用户已存在不能领券的提示
        } else {
            if ($params['is_weixin']) {
                $this->set_error(42026);
                return false;
            }
        }

        // 如果手机号码存在 或 注册成功就走登录流程
        $service_info = [
            'service_name'   => 'member.member.login',
            'service_params' => $params,
        ];
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return [
            'member_id'           => $result['data']['member_id'],
            'member_phone'        => $result['data']['member_phone'],
            'member_session'      => $result['data']['member_session'],
            'session_expire_time' => $result['data']['session_expire_time'],
            'login_type'          => $params['login_type'], // 登录类型
            'coupons'             => $register_result['data']['coupons'],
        ];
    }

    /**
     * 新用户注册后填写邀请码操作
     *
     * @param   array   $params   输入参数
     *
     * @return  bool
     */
    public function input_invite_code($params = array())
    {
        data_filter($params);

        if (empty($params['member_id'])) {
            $this->set_error(60101);
            return false;
        }

        if (empty($params['invite_code'])) {
            $this->set_error(60103);
            return false;
        }

        $service_info = [
            'service_name'   => 'member.member.input_invite_code',
            'service_params' => $params,
        ];
        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return !empty($result['data']) ? $result['data'] : [];
    }

    /**
     * 检验是否能直接微信登录
     *
     * @access  public
     *
     * @param   array   $params     登录数据
     *
     * @return  bool|int
     */
    public function weixin_check_login($params = [])
    {
        data_filter($params);

        if (empty($params['weixin_uid'])) {
            $this->set_error(60108);
            return false;
        }

        // 根据微信的UID查找用户信息
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'weixin_uid' => $params['weixin_uid'],
                'fields'     => 'member_id, member_phone, weixin_uid',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 微信有记录的话，就直接登录
        if (!empty($result['data']) && is_array($result['data'])) {
            $service_info = [
                'service_name'   => 'member.member.login',
                'service_params' => [
                    'member_phone'     => $result['data']['member_phone'],
                    'app_type'         => $params['app_type'],
                    'app_version'      => $params['app_version'],
                    'is_check_captcha' => 2,                     // 不用验证手机号
                    'weixin_uid'       => $params['weixin_uid'], // 微信uid
                ],
            ];

            $result = $this->requester->request($service_info);
            if (false === $result['success']) {
                $this->set_error($result['errcode']);
                return false;
            }

            $result['data']['login_type'] = 1; // 标记直接登录
            return $result['data'];

        // 微信没有记录，返回去绑定手机的标识
        } else {
            $params['login_type'] = 2; // 默认为新的微信登录用户
            return $params;
        }
    }

    /**
     * 微信绑定手机&登录（可能走注册流程）
     *
     * @access  public
     *
     * @param   array   $params     登录数据
     *
     * @return  bool|int
     */
    public function weixin_login($params = [])
    {
        data_filter($params);

        if (empty($params['weixin_uid'])) {
            $this->set_error(60108);
            return false;
        }

        // 1.根据微信的UID查找用户信息
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'weixin_uid' => $params['weixin_uid'],
                'fields'     => 'member_id, member_phone, weixin_uid',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 微信已有记录的话，就不能再绑定其它手机
        if (!empty($result['data']) && is_array($result['data'])) {
            $this->set_error(60109);
            return false;
        }

        // 2.检查是否输入合法手机号，手机验证码
        if (!is_phone($params['member_phone']) || empty($params['member_phone'])) {
            $this->set_error(60005);
            return false;
        }

        if (empty($params['captcha_key'])) {
            $this->set_error(47001);
            return false;
        }

        if (empty($params['captcha_content'])) {
            $this->set_error(47003);
            return false;
        }

        // 3.处理微信头像，外网拉到本站(CDN)
        if (isset($params['head_image']) && !empty($params['head_image'])) {
            $service_info = [
                'service_name'   => 'base.upload.upload_image_url',
                'service_params' => [
                    'image_url' => $params['head_image'],
                    'action'    => 'm_header',
                ],
            ];

            $result = $this->requester->request($service_info);
            if (false === $result['success']) {
                $this->set_error($result['errcode']);
                return false;
            }

            $params['head_image'] = isset($result['data']['image_path']) ? $result['data']['image_path'] : null;
        }

        // 4.验证输入手机号是否已存在
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_phone' => $params['member_phone'],
                'fields'       => 'member_id, member_phone, is_enabled, weixin_uid',
            ],
        ];

        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // -----------若有数据：此手机号已经被注册过-----------
        if (!empty($result['data'])) {
            // 手机还没有绑定微信，走手机绑定微信的流程
            if ('' == $result['data']['weixin_uid']) {
                $service_info = [
                    'service_name'   => 'member.member.bind_weixin_login',
                    'service_params' => $params,
                ];

                $result = $this->requester->request($service_info);
                if (false === $result['success']) {
                    $this->set_error($result['errcode']);
                    return false;
                }

                return $result['data'];

            // 手机已注册且绑定微信，给出提示信息
            } else {
                $this->set_error(60110);
                return false;
            }

        // -------------没有数据：先注册再绑定-------------
        } else {
            // 先去注册
            $service_info = [
                'service_name'   => 'member.member.register_member',
                'service_params' => $params,
            ];

            $result = $this->requester->request($service_info);
            if (false === $result['success']) {
                $this->set_error($result['errcode']);
                return false;
            }

            // 注册完成后，再走登录接口
            $params['is_check_captcha'] = 2; // 不再检验验证码(注册时已验)

            $service_info = [
                'service_name'   => 'member.member.login',
                'service_params' => $params,
            ];
            $result = $this->requester->request($service_info);

            if (false === $result['success']) {
                $this->set_error($result['errcode']);
                return false;
            }

            return [
                'member_id'           => $result['data']['member_id'],
                'member_phone'        => $result['data']['member_phone'],
                'member_session'      => $result['data']['member_session'],
                'session_expire_time' => $result['data']['session_expire_time'],
            ];
        }
    }

    /**
     * 用户登出
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function member_logout($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        // 清空对应用户的 push_token member_session session_expire_time 字段记录
        $set = [
            'push_token'          => '',
            'member_session'      => '',
            'session_expire_time' => '',
        ];
        append_update_info($set);

        $service_info = [
            'service_name'   => 'member.member.update_by_params',
            'service_params' => [ 'member_id' => $params['member_id'], 'set' => $set ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 上传push_token
     *
     * @param   array   $params   输入参数
     *
     * @return  bool
     */
    public function upload_push_token($params = [])
    {
        data_filter($params);

        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        if (empty($params['push_token'])) {
            return [];
            $this->set_error(47302);
            return false;
        }

        append_update_info($params);
        $service_info = [
            'service_name'   => 'member.member.update_by_params',
            'service_params' => [
                'member_id' => $params['member_id'],
                'set'       => $params,
            ],
        ];

        $this->load->library('requester');
        $this->requester->request($service_info);

        return [];
    }

    /**
     * 用户获取地址列表
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function get_address_list($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        $service_info = [
            'service_name'   => 'member.address.get_address_list',
            'service_params' => $params,
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 地址-详细地址-门牌号做拼接
        if (!empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$val) {
                $val['address'] = $val['address'] . $val['address_detail'] . $val['room_number'];
            }
        }

        return $result['data'];
    }

    /**
     * 用户新增收货地址
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function add_address($params = [])
    {
        data_filter($params);

        // 参数验证
        if (!$this->validate_params($params)) {
            $this->set_error($this->get_error());
            return false;
        }

        // 按GPS获取省市区信息
        $this->load->library('baidumap');
        $address = $this->baidumap->get_address_detail_by_gbs($params['latitude'], $params['longitude']);
        $params['province_name'] = $address['province'];
        $params['city_name']     = $address['city'];
        $params['district_name'] = $address['district'];

        unset($params['validate_type']);
        append_create_update($params);

        $service_info = [
            'service_name'   => 'member.address.add_address',
            'service_params' => [ 'list' => $params, 'is_batch' => false, 'is_insert_id' => true ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        } else {
            return $result['data'];
        }
    }

    /**
     * 用户获取收货地址详情数据
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function get_address_detail($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['address_id'])) {
            $this->set_error(60116);
            return false;
        }

        $service_info = [
            'service_name'   => 'member.address.get_by_params',
            'service_params' => [
                'address_id' => $params['address_id'],
                'fields'     => 'address_id, name, phone, address, address_detail, room_number, latitude, longitude',
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        } else {
            return $result['data'];
        }
    }

    /**
     * 保存用户编辑的收货地址
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function save_edit_address($params = [])
    {
        data_filter($params);

        // 参数验证
        if (!$this->validate_params($params)) {
            $this->set_error($this->get_error());
            return false;
        }

        // 按GPS获取省市区信息
        $this->load->library('baidumap');
        $address = $this->baidumap->get_address_detail_by_gbs($params['latitude'], $params['longitude']);
        $params['province_name'] = $address['province'];
        $params['city_name']     = $address['city'];
        $params['district_name'] = $address['district'];

        unset($params['validate_type']);
        append_update_info($params);

        $service_info = [
            'service_name'   => 'member.address.edit_address',
            'service_params' => [ 'address_id' => $params['address_id'], 'set' => $params ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        } else {
            return $result['data'];
        }
    }

    /**
     * 用户删除收货地址
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function delete_address($params = [])
    {
        data_filter($params);

        // 参数验证
        if (is_array($params['address_ids']) && empty($params['address_ids'])) {
            $this->set_error(60116);
            return false;
        }

        // 循环删除地址
        $this->load->library('requester');
        foreach ($params['address_ids'] as $address_id) {
            $service_info = [
                'service_name'   => 'member.address.edit_address',
                'service_params' => [ 'address_id' => $address_id, 'set' => ['is_deleted' => 1] ],
            ];

            $result = $this->requester->request($service_info);
            unset($service_info);
            if (false === $result['success']) {
                $this->set_error($result['errcode']);
                return false;
            }
        }

        return [];
    }

    /**
     * 收货地址管理(新增/修改)-数据验证
     *
     * @param   array   $params 参数
     *
     * @return  bool|array
     */
    public function validate_params(&$params = [])
    {
        data_filter($params);

        // 如果是修改地址，则要判断地址ID是否为空
        if (isset($params['validate_type']) && $params['validate_type'] == 'edit' && !filter_empty('address_id', $params)) {
            $this->set_error(40002);
            return false;
        }
        if (isset($params['validate_type']) && $params['validate_type'] == 'add' && !filter_empty('member_id', $params)) {
            $this->set_error(60122);
            return false;
        }
        if (!filter_empty('name', $params)) {
            $this->set_error(60111);
            return false;
        }
        if (!filter_empty('phone', $params) || !is_mobile_phone($params['phone'])) {
            $this->set_error(60112);
            return false;
        }
        if (!filter_empty('address', $params)) {
            $this->set_error(60113);
            return false;
        }
        if (!filter_empty('address_detail', $params)) {
            $this->set_error(60114);
            return false;
        }
        if (!filter_empty('latitude', $params) || !filter_empty('longitude', $params)) {
            $this->set_error(60115);
            return false;
        }
        // 过滤null值
        invalid_data_filter($params);

        return true;
    }

    /**
     * 获取用户相关配置项
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function get_member_config($params = [])
    {
        data_filter($params);

        $service_info = [
            'service_name'   => 'member.member.get_member_config',
            'service_params' => [],
        ];

        $this->load->library('requester');
        $config_result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 处理数据格式
        $result['family_population'] = $config_result['data']['family_population'];
        if (!empty($config_result['data']['annual_salary_level'])) {
            foreach ($config_result['data']['annual_salary_level'] as $key => $val) {
                $result['annual_salary_level'][] = ['year_level' => intval($key), 'title' => $val['title']];
            }
        }

        return $result;
    }

    /**
     * 获取用户资料详情
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function get_info($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [ 'member_id' => $params['member_id'] ],
        ];

        $this->load->library('requester');
        $result      = $this->requester->request($service_info);
        $member_info = $result['data'];
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        if (empty($member_info)) {
            return [];
        }

        $member_info['is_clock'] = 2; // 用户今日是否签到（1已签到 2未签到）
        $member_info['is_edit']  = 2; // 没有完成用户资料信息

        if (!empty($member_info['head_image']) && !empty($member_info['member_name']) && !empty($member_info['profession']) && $member_info['sex'] > 0
            && '0000-00-00' != $member_info['birthday'] && $member_info['annual_salary_level'] > 0 && $member_info['family_population'] > 0) {
            $member_info['is_edit'] = 1;
        }

        // 处理用户头像
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        $member_info['head_image'] = $cdn_domain . $member_info['head_image'];

        // 处理年收入
        $service_info = [
            'service_name'   => 'member.member.get_member_config',
            'service_params' => [],
        ];
        $member_config = $this->requester->request($service_info);
        $annual_salary_level = $member_info['annual_salary_level'];
        if (isset($member_config['data']['annual_salary_level'][$annual_salary_level])) {
            $member_info['annual_salary_level'] = $member_config['data']['annual_salary_level'][$annual_salary_level]['title'];
        } else {
            $member_info['annual_salary_level'] = '';
        }

        // 处理家庭人口
        if ($member_info['family_population'] == 0) {
            $member_info['family_population'] = '';
        }

        // 拼接分享地址链接
        $member_info['share_url'] = M_DOMAIN . 'share/index?share_type=banner&activity_extra_id=4&invite_code='. $member_info['invite_code'];
        
        // 获得用户账号数
        $service_info = [
            'service_name'   => 'member.member_bank.get_list',
            'service_params' => ['member_id' => $params['member_id']],
        ];

        $bank_result = $this->requester->request($service_info);
        $member_info['bank_total'] = $bank_result['data']['total'];

        // 处理生日转年龄
        if ($member_info['birthday'] == '0000-00-00') {
            $member_info['age'] = '';
        } else {
            $member_info['age'] = get_age($member_info['birthday'])['age'];
        }

        // 判断用户是否今日签到
        $service_info = [
            'service_name'   => 'member.clock.get_list',
            'service_params' => [
                'member_id'      => $params['member_id'],
                'ge_create_time' => date('Y-m-d') . ' 00:00:00',
                'le_create_time' => date('Y-m-d') . ' 23:59:59',
            ],
        ];
        $clock_result = $this->requester->request($service_info);
        if (!empty($clock_result['data']['list'])) {
            $member_info['is_clock'] = 1;
        }

        // 查找用户有多少优惠券是没有使用（没有过期）
        $service_info = [
            'service_name'   => 'coupon.coupon.get_list',
            'service_params' => [
                'is_pages'      => false,
                'project_id'    => 3,
                'status'        => 1,
                'member_id'     => $params['member_id'],
                //'le_start_time' => date('Y-m-d H:i:s'),
                'ge_end_time'   => date('Y-m-d H:i:s'),
                'fields'        => 'coupon_id, coupon_sn',
            ],
        ];
        $coupon_result = $this->requester->request($service_info);
        $member_info['coupon_total'] = intval($coupon_result['data']['total']);

        return $member_info;
    }

    /**
     * 保存用户资料修改
     *
     * @param    $params    array    参数
     *
     * @return   array|bool
     */
    public function save_edit_info($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }
        // 选择生日必须是今天以前
        if (strtotime($params['birthday']) > strtotime(date('Y-m-d 23:59:59'))) {
            $this->set_error(60129);
            return false;
        }

        //通过生日计算年龄
        $age_result = get_age($params['birthday']);

        $set = [
            'head_image'          => $params['head_image'],
            'member_name'         => $params['member_name'],
            'birthday'            => $params['birthday'],
            'sex'                 => $params['sex'],
            'profession'          => $params['profession'],
            'family_population'   => $params['family_population'],
            'annual_salary_level' => $params['annual_salary_level'],
        ];
        append_update_info($set);
        invalid_data_filter($set);

        $service_info = [
            'service_name'   => 'member.member.update_by_params',
            'service_params' => [
                'member_id' => $params['member_id'],
                'set'       => $set,
            ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 用户编辑完成会有积分变动
        $result = $this->edit_member_add_credit($params);
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        // 如果用户名称有变动，则传给蛙笑
        if ($params['member_name'] != $params['member_name_in_db']) {
            $service_info = [
                'service_name'   => 'member.member.update_member_to_waxiao',
                'service_params' => [
                    'member_id'        => $params['member_id'],
                    'waxiao_member_id' => $params['waxiao_member_id'],
                    'member_name'      => $params['member_name'],
                ]
            ];

            $this->load->library('requester');
            $update_waxiao_member_result = $this->requester->request($service_info);
            if (false === $update_waxiao_member_result['success']) {
                $this->set_error($update_waxiao_member_result['errcode']);
                return false;
            }
        }

        return [
            'member_id' => $params['member_id'],
            'age'       => !empty($age_result['age']) ? $age_result['age'] : 0,
        ];
    }

    /**
     * 用户编辑完成会有积分变动
     *
     * @param    $params    array   用户数据
     *
     * @return   bool|array
     */
    public function edit_member_add_credit($params = [])
    {
        data_filter($params);

        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        // 获取用户信息
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_id' => $params['member_id'],
                'fields'    => 'sex, birthday, profession, annual_salary_level, family_population, head_image, member_name',
            ],
        ];

        $this->load->library('requester');
        $member_result = $this->requester->request($service_info);
        $member_info   = $member_result['data'];

        // 资料都填写了
        if (empty($member_info['head_image']) || empty($member_info['member_name']) || empty($member_info['birthday']) 
            || empty($member_info['sex']) || empty($member_info['profession']) || empty($member_info['family_population']) 
            || empty($member_info['annual_salary_level'])) {
            return [];
        }

        // 查看此人是否已经完善信息得到过积分
        $service_info = [
            'service_name'   => 'member.member_credit.get_list',
            'service_params' => [
                'member_id' => $params['member_id'],
                'type'      => 1,// 完善信息奖励积分
                'fields'    => 'member_credit_id, credit',
            ],
        ];

        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 已经奖励过的就不需要再次奖励了
        if (!empty($result['data']['list'])) {
            return [];
        }

        // 获取配置数据
        $service_info = [
            'service_name'   => 'member.credit_conf.get_by_params',
            'service_params' => [
                'type'   => 1, // 完善信息奖励积分
                'fields' => 'type, credit, limit_num, is_enabled',
            ],
        ];
        $result = $this->requester->request($service_info);

        // 配置有效，执行增加积分
        if ($result['data']['is_enabled'] == 1 && $result['data']['credit'] > 0) {
            $service_info = [
                'service_name'   => 'member.member_credit.add_credit',
                'service_params' => [
                    'type'      => 1,
                    'credit'    => trim_zero($result['data']['credit']),
                    'member_id' => $params['member_id'],
                    'explain'   => '完善资料获取' . trim_zero($result['data']['credit']) . '积分',
                ],
            ];
            $this->requester->request($service_info);
        }

        return [];
    }

    /**
     * 保存用户意见反馈
     *
     * @param    $params    array    参数
     *
     * @return   bool
     */
    public function save_feedback($params = [])
    {
        data_filter($params);

        // 参数验证
        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        if (empty($params['content'])) {
            $this->set_error(60120);
            return false;
        }

        append_create_update($params);

        $service_info = [
            'service_name'   => 'member.feedback.add_member_feedback',
            'service_params' => [ 'list' => $params, 'is_batch' => false, 'is_insert_id' => true ],
        ];

        $this->load->library('requester');
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        return ['feedback_id' => $result['data']];
    }

    /**
     * C端用户收藏店铺列表
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function get_favorite_list($params = [])
    {
        data_filter($params);

        if(!filter_empty('market_id',$params)){
            $this->set_error(60301);
            return false;
        }

        $this->load->library('requester');

        $service_info = [
            'service_name'   => 'member.favorite.shop_list',
            'service_params' => $params,
        ];

        $result = $this->requester->request($service_info);

        // 拼接完整CDN地址
        $cdn_domain = get_cdn_domain(ENVIRONMENT);
        if (!empty($result['data']['shop_list'])) {
            foreach ($result['data']['shop_list'] as $key => $val) {
                $result['data']['shop_list'][$key]['door_image'] = $cdn_domain . $val['door_image'];
            }
        }

        if ($result['success']) {
            return $result['data'];
        } else {
            $this->set_error($result['errcode']);
            return false;
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
    //     data_filter($params);

    //     if(empty($params['member_id'])){
    //         $this->set_error(60122);
    //         return false;
    //     }

    //     if(empty($params['shop_id'])){
    //         $this->set_error(60201);
    //         return false;
    //     }

    //     $service_info = [
    //         'service_name'   => 'member.favorite.add_shop',
    //         'service_params' => $params,
    //     ];

    //     $result = $this->requester->request($service_info);
    //     if (false === $result['success']) {
    //         $this->set_error($result['errcode']);
    //         return false;
    //     }

    //     return $result;
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
    //     data_filter($params);

    //     if(empty($params['member_id'])){
    //         $this->set_error(60122);
    //         return false;
    //     }

    //     if(empty($params['shop_id'])){
    //         $this->set_error(60201);
    //         return false;
    //     }

    //     $service_info = [
    //         'service_name'   => 'member.favorite.cancel_shop',
    //         'service_params' => $params,
    //     ];

    //     $result = $this->requester->request($service_info);
    //     if (false === $result['success']) {
    //         $this->set_error($result['errcode']);
    //         return false;
    //     }

    //     return $result;
    // }

    /**
     * 获取邀请人列表
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function get_invitees_list($params = [])
    {
        data_filter($params);

        if (empty($params['member_id'])){
            $this->set_error(60122);
            return false;
        }

        $this->load->library('requester');
        // 查询用户信息
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => ['member_id' => $params['member_id']],
        ];

        $member_result = $this->requester->request($service_info);

        if (false === $member_result['success']) {
            $this->set_error($member_result['errcode']);
            return false;
        }
        // 获取用户邀请人列表
        $service_info = [
            'service_name'   => 'member.invite_record.get_list',
            'service_params' => [
                'is_pages'      => $params['is_pages'],
                'page_no'       => $params['page_no'],
                'page_size'     => $params['page_size'],
                'invite_code'   => $member_result['data']['invite_code'],
                'invitees_type' => 1, // 只取邀请C端的人
                'order_by'      => 'create_time DESC',
            ],
        ];

        $result = $this->requester->request($service_info);

        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        if ($result['data']['list'] && !empty($result['data']['list'])) {
            foreach ($result['data']['list'] as &$value) {
                $member_ids[]  = $value['invitees_id'];
                // 屏蔽部分受邀人手机号
                $value['invitees_phone'] = substr($value['invitees_phone'],0,3) . '****' . substr($value['invitees_phone'],-4);
            }


            //获取邀请人首单数
            if (!empty($member_ids)) {
                $service_info = [
                    'service_name'   => 'order.order_show.get_list',
                    'service_params' => [
                        'is_pages' => false,
                        'status'   => 50,
                        'group_by' => 'member_id',
                        'where_in' => ['member_id' => $member_ids],
                    ],
                ];
                $order_result = $this->requester->request($service_info);

                if (false === $order_result['success']) {
                    $this->set_error($order_result['errcode']);
                    return false;
                }

                $order_total  = $order_result['data']['total'];

                $result['data']['order_total'] = $order_total;
            }
        } else {
            $result['data']['order_total'] = 0;
        }

        return $result['data'];
    }

    /**
     * 获取用户邀请分享(app个人邀请记录中分享)
     *
     * @param    array    $params
     *
     * @return   array|bool
     */
    public function get_share_activity($params = [])
    {
        data_filter($params);

        // 正式环境下，分享活动ID固定为4
        $activity_extra_id = 4;

        // 获取用户邀请分享的活动详情
        $this->load->model('Activity_model');
        $activity_extra = $this->Activity_model->get_activity_extra(['activity_extra_id' => $activity_extra_id]);
        if (false === $activity_extra) {
            $this->set_error($this->Activity_model->get_error());
            return false;
        }

        // 拼接分享需要的数据(目前：微信)
        $result = [
            'weixin' => [
                'title' => !empty($activity_extra['title'])       ? $activity_extra['title']       : '',
                'logo'  => !empty($activity_extra['logo_img'])    ? $activity_extra['logo_img']    : '',
                'desc'  => !empty($activity_extra['description']) ? $activity_extra['description'] : '',
                'url'   => M_DOMAIN . 'share/index?share_type=banner&activity_extra_id='. $activity_extra_id .'&invite_code='. $params['invite_code'],
            ]
        ];

        return $result;
    }

    /**
     * 用户每日签到记录
     *
     * @param    array    $params   用户数据
     *
     * @return   array|bool
     */
    public function clock($params = [])
    {
        data_filter($params);

        if (empty($params['member_id'])) {
            $this->set_error(60122);
            return false;
        }

        // 先查找今天是否已经签到
        $service_info = [
            'service_name'   => 'member.clock.get_list',
            'service_params' => [
                'member_id'      => $params['member_id'],
                'ge_create_time' => date('Y-m-d') . ' 00:00:00',
                'le_create_time' => date('Y-m-d') . ' 23:59:59',
            ],
        ];

        $this->load->library('requester');
        $clock_result = $this->requester->request($service_info);
        if (!empty($clock_result['data']['list'])) {
            return [];
        }

        // 获取用户数据
        $service_info = [
            'service_name'   => 'member.member.get_by_params',
            'service_params' => [
                'member_id' => $params['member_id'],
                'fields'    => 'member_id, member_phone',
            ],
        ];
        $member_result = $this->requester->request($service_info);

        // 添加签到记录
        $service_info = [
            'service_name'   => 'member.clock.add_clock',
            'service_params' => [
                'list'         => [
                    'member_id'    => $params['member_id'],
                    'member_phone' => $member_result['data']['member_phone'],
                ],
                'is_batch'     => false,
                'is_insert_id' => true,
            ],
        ];
        append_create_info($service_info['service_params']['list']);

        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        // 添加积分操作
        $service_info = [
            'service_name'   => 'member.credit_conf.get_by_params',
            'service_params' => [
                'type'   => 2,
                'fields' => 'type, credit, credit_max, limit_num, is_enabled',
            ],
        ];
        $result = $this->requester->request($service_info);

        // 配置有效，执行增加积分
        if ($result['data']['is_enabled'] == 1 && $result['data']['credit'] > 0) {
            $service_info = [
                'service_name'   => 'member.member_credit.add_credit',
                'service_params' => [
                    'type'      => 2,
                    'credit'    => $credit,
                    'member_id' => $params['member_id'],
                    'explain'   => '每天签到奖励' . $credit . '积分',
                ],
            ];
            $this->requester->request($service_info);
        }

        return [];
    }

    /**
     * 获取我的奖品列表
     *
     * @param   array   $params
     *
     * @return  array|bool
     */
    public function get_award_list($params = [])
    {
        data_filter($params);

        if (empty($params['member_id'])){
            $this->set_error(60122);
            return false;
        }

        $this->load->library('requester');
        // 查询用户信息
        $service_info = [
            'service_name'   => 'member.member_award.get_member_award_list',
            'service_params' => $params,
        ];
        $result = $this->requester->request($service_info);
        if (false === $result['success']) {
            $this->set_error($result['errcode']);
            return false;
        }

        foreach ($result['data']['list'] as &$item) {
            $item['create_time'] = date('Y-m-d', strtotime($item['create_time']));
        }

        return $result['data'];
    }
}
