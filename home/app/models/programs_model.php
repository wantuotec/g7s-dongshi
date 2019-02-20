<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 后台菜单
 *
 * @author      liunian
 * @date        2013-04-16
 * @category    programs
 * @copyright   Copyright(c) 2013
 * @version     $Id: programs_model.php 1607 2014-08-04 07:42:13Z 熊飞龙 $
 */
class Programs_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 获得菜单列表
     *
     * @access  public
     *
     * @return  array
     */
    public function get_list(array $params = array())
    {
        $where = array(
            'fields'   => empty($params['fields']) ? null : $params['fields'],
            'where'    => array(
                           'is_deleted' => empty($params['is_deleted']) ? 0 : intval($params['is_deleted']),
                       ),
            'order_by' => '`sort` ASC,`systemId` ASC,`sysGroupId` ASC',
        );
        invalid_data_filter($where);

        $this->load->dao('Programs_dao');
        $data = $this->Programs_dao->get($where);

        $result = array();
        foreach($data['list'] as $val) {
            if ($val['systemId'] ==0) {
                $result[$val['sysGroupId']] = $val;
            }
        }

        foreach($data['list'] as $val) {
            if ($val['systemId'] != 0) {
                $result[$val['sysGroupId']]['sub'][] = $val;
            }
        }

        if (false === $result) {
            $this->set_error($this->Item_dao->get_error());
        } else {
            return $result;
        }
    }

    /**
     * 根据 ID 修改菜单的信息
     *
     * @access  public
     * @param   $id        int      菜单ID
     * @param   $param     array    修改的数据信息
     * @return  array|false
     */
     public function update_program_by_id($id, array $params)
     {
        $this->load->helper('common');
        data_filter($id);
        data_filter($params);

        $result = false;
        if (!isset($id) || intval($id) < 1) {
            $this->set_error('参数错误');
            return $result;
        }

        $this->load->dao('Programs_dao');
        $result = $this->Programs_dao->update_program_by_id($id, $params);

        if (false === $result) {
            $this->set_error('修改错误');
        } else {
            return $result;
        }
     }

     /**
      * 根据 id 查询一条菜单信息
      *
      * @access     public
      * @param      $id       int     菜单ID
      * @return     $result   array   获取的结果
      */
    public function get_program_by_id($id)
    {
        $this->load->helper('common');
        data_filter($id);

        if (!isset($id) || $id < 1) {
            return $result;
        }

        $this->load->dao('Programs_dao');
        $result = $this->Programs_dao->get_program_by_id($id);

        if (false === $result) {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 状态数据
     *
     * @access  public
     * @return  $result  array   数据信息
     */
    public function get_all_status()
    {
        $this->load->dao('Programs_dao');
        $all_status = $this->Programs_dao->get_all_status();
        return $all_status;
    }

    /**
     * 获取隐藏菜单结果MSG
     *
     * @access      public
     *
     * @param       $id         int     菜单ID
     * @param       $is_display int     显示状态
     *
     * @return      $result     string  结果集
     */
    public function get_display_msg_by_id($id, $is_display)
    {
        $result = false;
        $this->load->helper('common');
        data_filter($id);
        data_filter($is_display);

        $this->load->dao('Programs_dao');
        $result  = $this->Programs_dao->update_display_by_id($id, $is_display);

        if (false === $result) {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 添加二级菜单
     *
     * @access public
     *
     * @param   $params array   添加的数据
     *
     * @return  array
     */
    public function add_second_programs($params)
    {
        $this->load->helper('common');
        data_filter($params);

        if ($params['id'] < 1 || $params['systemId'] != 0 || $params['sysGroupId'] < 1) {
            $this->set_error('参数错误');
            return false;
        }

        $this->load->dao('Programs_dao');
        $result_systemId      = $this->Programs_dao->get_maxSystemId_by_sysGroupId($params['sysGroupId']);

        //拼装修改的数据
        $params_add['systemId']     = intval($result_systemId['systemId'] + 1);
        $params_add['sysGroupId']   = intval($params['sysGroupId']);
        $params_add['progName']     = $params['progName'];
        $params_add['funcName']     = $params['funcName'];
        $params_add['createTime']   = date('Y-m-d H:i:s');
        $params_add['sort']         = intval($params['sort']);
        $params_add['is_display']   = intval($params['is_display']);
        data_filter($params_add);
        if ($this->Programs_dao->add_programs($params_add)) {
            return true;
        } else {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        }
    }

    /**
     * 添加一级菜单
     *
     * @access  public
     * @param   $params  array  添加的数据
     * @reurn   $result  array  返回数据
     */
    public function add_first_programs($params)
    {
        $this->load->helper('common');
        data_filter($params);

        $this->load->dao('Programs_dao');
        $result = $this->Programs_dao->get_max_sysGroupId();

        if (false === $result) {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        }

        $params['systemId']   = 0;
        $params['sysGroupId'] = intval($result['sysGroupId'] + 1); // 一个新的一级菜单 sysGroupId 是这个组中最大的加上1
        $params['createTime'] = date('Y-m-d H:i:s');

        if ($this->Programs_dao->add_programs($params)) {
            return true;
        } else {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        }
    }


    /**
     * 保存菜单数据
     *
     * @param    array    $params
     *
     * @return   array|bool
     */
    public function do_save(array $params)
    {
        data_filter($params);

        $update = array();
        $insert = array();
        $delete = array();
        $groupId= array();

        // 区分数据-更新、新增
        foreach($params as $key=>$val) {

            $val['sort']       = intval($val['sort']);
            $val['sysGroupId'] = intval($val['sysGroupId']);
            $val['systemId']   = (intval($val['systemId']) == 0 ) ? 0 : 1 ;
            $val['is_display'] = (intval($val['is_display']) == 0 ) ? 0 : 1 ;

            if (is_array($val['sub'])) {
                foreach($val['sub'] as $skey=>$sval) {

                    $sval['sort']       = intval($sval['sort']);
                    $sval['sysGroupId'] = intval($sval['sysGroupId']);
                    $sval['systemId']   = (intval($sval['systemId']) == 0 ) ? 0 : $sval['systemId'] ;
                    $sval['is_display'] = (intval($sval['is_display']) == 0 ) ? 0 : 1 ;

                    if(intval($sval['id']) > 0) {
                        $update[$sval['id']] = $sval;
                    } else {
                        $insert[] = $sval;
                    }
                }
            }

            if(intval($val['id']) > 0) {
                $update[$val['id']] = $val;
            } else {
                $insert[] = $val;
            }
        }

        // 过滤未修改的数据
        $server_data = array();
        $programs_where = array(
            'where' => array(
                    'is_deleted' => 0,
                ),
        );
        $this->load->dao('Programs_dao');
        $data = $this->Programs_dao->get($programs_where);
        foreach($data['list'] as $val) {

            !isset($groupId[$val['sysGroupId']]) && $groupId[$val['sysGroupId']] = $val['sysGroupId'];

            if(!$update[$val['id']]) {
                $delete[] = $val['id'];
            } else {
                $temp = $update[$val['id']];
                if($val['progName'] == $temp['progName']
                        && $val['funcName'] == $temp['funcName']
                        && $val['sort'] == $temp['sort']
                        && $val['is_display'] == $temp['is_display']) {
                    unset($update[$val['id']]);
                }
            }

            $server_data[$val['sysGroupId']][] = $val;
        }

        // 如果有新增栏目则从数据库中获得最大组ID 号
        $max_groupid = 1;
        if (!empty($insert)) {
            $sql = "SELECT max(sysGroupId) max FROM `programs`;";
            $max = $this->Programs_dao->query($sql)->row_array();

            if (intval($max['max'])) {
                $max_groupid = (intval($max['max']) + 1);
            }
        }

        // 过滤不规范的数据-新增
        $insert_groupId = array();
        $insert_systemId = array();
        foreach ($insert as $key => $val) {
            if((empty($insert[$key]['progName']) || empty($insert[$key]['funcName']) || empty($insert[$key]['sysGroupId'])) && $insert[$key]['systemId'] != 0) {
                unset($insert[$key]);
            } else {
                unset($insert[$key]['sub']);
            }

            if ($insert[$key]) {
                // 原有一级菜单上添加二级菜单
                if(count($server_data[$insert[$key]['sysGroupId']])) {
                    if($insert[$key]['systemId'] != 0 ) {
                        if (empty($insert_systemId[$insert[$key]['sysGroupId']])) {
                            $insert_systemId[$insert[$key]['sysGroupId']] = count($server_data[$insert[$key]['sysGroupId']]);
                        } else {
                            $insert_systemId[$insert[$key]['sysGroupId']] ++;
                        }
                        $insert[$key]['systemId'] = $insert_systemId[$insert[$key]['sysGroupId']];
                    }
                } else {
                    // 新增一级及二级菜单
                    if(!isset($insert_groupId[$insert[$key]['sysGroupId']])) {
                        $insert_groupId[$insert[$key]['sysGroupId']] = $max_groupid;
                    }

                    $insert[$key]['sysGroupId'] = $insert_groupId[$insert[$key]['sysGroupId']];
                    
                    if($insert[$key]['systemId'] != 0) {
                        if (empty($insert_systemId[$insert[$key]['sysGroupId']])) {
                            $insert_systemId[$insert[$key]['sysGroupId']] = 1;
                        } else {
                            $insert_systemId[$insert[$key]['sysGroupId']] ++;
                        }
                        $insert[$key]['systemId'] = $insert_systemId[$insert[$key]['sysGroupId']];
                    }
                }

                $insert[$key]['createTime'] = date('Y-m-d H:i:s');

                if ($val['systemId'] == 0) {
                    $max_groupid++ ;
                }
            }
        }

        if($update) {
            foreach($update as $val) {
                $set = array(
                    'progName'   => empty($val['progName']) ? '' : $val['progName'],
                    'funcName'   => empty($val['funcName']) ? '' : $val['funcName'],
                    'sort'       => intval($val['sort']),
                    'is_display' => ($val['is_display'] == 0) ? 0 : 1,
                );
                $this->Programs_dao->update($set, array('is_deleted'=>0 , 'id'=> empty($val['id']) ? '0' : $val['id']), 1);
            }
        }

        if($delete) {
            $set = array(
                'is_deleted' => 1,
            );
            $this->Programs_dao->update($set, array('is_deleted'=>0), null, array('where_in'=> array( 'id' => $delete) ));
        }

        if($insert) {
            $this->Programs_dao->insert_batch($insert);
        }

        if($this->Programs_dao->get_error()) {
            $this->set_error($this->Programs_dao->get_error());
            return false;
        }

        return true;
    }
}
