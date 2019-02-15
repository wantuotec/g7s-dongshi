<?php 
    $CI =& get_instance();
    $is_branch = false;                 // 默认是没有绑定站点的
    $user_info = get_operate_user();    // 获取登录数据

    // 后台没有绑定站点
    if (is_array($user_info['userBranchId']) && !empty($user_info['userBranchId'])) {
        $is_branch = true;
        $search    = array(
            'is_pages'   => false,
            'is_enabled' => 1,
            'where_in'   => array(
                'branch_id' => $user_info['userBranchId'],
            ),
            'fields' => 'branch_id, branch_name, type',
        );

        $CI->load->model('Branch_model');
        $branch_result = $CI->Branch_model->get_list($search);
        $branch_list   = $branch_result['list'];

    } else {
        $is_branch = false;
    }
?>

<!--
站点列表
配送员管理列表
站点日考勤
出勤记录异常
商家结算列表
结算汇总查询
每日结算查询
订单看板
-->
<?php if (1 == intval($type)) { ?>

    <?php if (false === $is_branch) { ?>
        <td>省-市-区 ：</td>
        <td class="address_selector" callback="get_branch_list" colspan="2"></td>
        <td>站点 ：</td>
        <td>
            <select name="branch_id"><option value="" >请选择...</option></select>
        </td>

    <?php } else { ?>
        <td>所有管理站点 ：</td>
        <td colspan="4">
            <input type="hidden" name="is_brancher" value="1"/>
            <?php if(count($branch_list) > 1){ ?>
                <select name="branch_id">
                    <option value="">请选择...</option>
                    <?php if ($branch_list && is_array($branch_list)) { foreach ($branch_list as $value) { ?>
                        <option value="<?php echo $value['branch_id']; ?>"><?php echo $value['branch_name']; ?></option>
                    <?php }} ?>
                </select>
            <?php }else{ ?>
                <input type="hidden" name="branch_id" value="<?php echo $branch_list[0]['branch_id']; ?>">
                <?php echo $branch_list[0]['branch_name']; ?>
            <?php } ?> 
        </td>

    <?php } ?>

<?php } else if (2 == intval($type)) { ?>
<!--
考勤记录列表
薪资管理 
配送员打卡 
配送员调配 
配送员调配日志
添加配送员出勤配送记录
-->
    <?php if (false === $is_branch) { ?>
        <td>省-市-区 ：</td>
        <td class="address_selector" callback="get_branch_list"></td>
        <td>站点 ：</td>
        <td>
            <select name="branch_id"><option value="" >请选择...</option></select>
        </td>

    <?php } else { ?>
        <td>所有管理站点 ：</td>
        <td colspan="3">
            <input type="hidden" name="is_brancher" value="1"/>
            <?php if(count($branch_list) > 1){ ?>
                <select name="branch_id">
                    <option value="">请选择...</option>
                    <?php if ($branch_list && is_array($branch_list)) { foreach ($branch_list as $value) { ?>
                        <option value="<?php echo $value['branch_id']; ?>"><?php echo $value['branch_name']; ?></option>
                    <?php }} ?>
                </select>
            <?php }else{ ?>
                <input type="hidden" name="branch_id" value="<?php echo $branch_list[0]['branch_id']; ?>">
                <?php echo $branch_list[0]['branch_name']; ?>
            <?php } ?> 
        </td>
    <?php } ?>

<?php } else if (3 == intval($type)) { ?>

    <?php if (false === $is_branch) { ?>
        <tr>
        <?php if(!isset($user_branch_name)){ ?>
            <td>省-市-区：</td>
            <td class="address_selector2" callback="get_branch_list2"></td>
        <?php } ?>

            <td>省-市-区：</td>
            <td class="address_selector" callback="get_branch_list"></td>
        </tr>

        <tr>
            <?php if(!isset($user_branch_name)){ ?>
                <td>出发站点：</td>
                <td>
                    <select name="branch_id2"></select>
                    <span class="c-red">*</span>
                </td>
            <?php } ?>

            <td>目标站点：</td>
            <td>
                <select name="branch_id"></select>
                <span class="c-red">*</span>
            </td>
        </tr>
    <?php } else { ?>
        <tr>
            <td>出发站点：</td>
            <td>
                <input type="hidden" name="is_brancher" value="1"/>
                <select name="branch_id2">
                    <option value="">请选择...</option>
                    <?php if ($branch_list && is_array($branch_list)) { foreach ($branch_list as $value) { ?>
                        <option value="<?php echo $value['branch_id']; ?>"><?php echo $value['branch_name']; ?></option>
                    <?php }} ?>
                </select>
            </td>

            <td>省-市-区：</td>
            <td class="address_selector" callback="get_branch_list"></td>
        </tr>

        <tr>
            <td></td>
            <td></td>
            <td>目标站点：</td>
            <td>
                <select name="branch_id"></select>
                <span class="c-red">*</span>
            </td>
        </tr>

        
    <?php } ?>

<?php } else if (4 == intval($type)) { ?>
<!-- 
    商家添加 和 编辑 
    shop_branch 是在模板中传过来的，商家编辑的时候不需要选择站点，
-->
    <?php if (!isset($shop_branch['branch_id']) || empty($shop_branch['branch_id'])) { ?>
        <tr>
            <td>省-市-区：</td>
            <td class="address_selector" callback="get_branch_list"></td>
        </tr>
        <tr>
            <td>站点：</td>
            <td>
               <select name="branch_id"></select>
               <span class="c-red">*</span>
            </td>
        </tr>
    <?php } else { ?>
        <tr>
            <input type="hidden" name="branch_id" value="<?php echo $shop_branch['branch_id']; ?>"/>
            <td>站点 ：</td>
            <td><?php echo $shop_branch['branch_name']; ?></td>
        </tr>
    <?php } ?>

<?php } else if (5 == intval($type)) { ?>
    <!--
        省市区联动
    -->
    <td>省-市-区：</td>
    <td class="address_selector"></td>

<?php } else { ?>
<!--
订单列表
-->
    <?php if (false === $is_branch) { ?>
        <td>省-市-区 ：</td>
        <td class="address_selector" callback="get_branch_list"></td>
        <td>站点 ：</td>
        <td><select name="branch_id"></select></td>
    <?php } else { ?>
        <td>所有管理站点 ：</td>
        <td>
            <input type="hidden" name="is_brancher" value="1"/>
            <?php if(count($branch_list) > 1){ ?>
            <select name="branch_id">
                <option value="">请选择...</option>
                <?php if ($branch_list && is_array($branch_list)) { foreach ($branch_list as $value) { ?>
                    <option value="<?php echo $value['branch_id']; ?>"><?php echo $value['branch_name']; ?></option>
                <?php }} ?>
            </select>
            <?php }else{ ?>
                <input type="hidden" name="branch_id" value="<?php echo $branch_list[0]['branch_id']; ?>">
                <?php echo $branch_list[0]['branch_name']; ?>
            <?php } ?>
        </td>
        <td></td>
        <td></td>
    <?php } ?>
<?php } ?>