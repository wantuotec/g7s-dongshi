    <div id="main-content"> <!-- Main Content Section with everything -->
    <!-- Page Head -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>搜 索</h3>
        </div><!-- End .content-box-header -->

        <div class="content-box-content">
            <form method="get" class="search-content">
                <table>
                    <tbody>
                        <tr>
                            <td>留言时间：&nbsp;&nbsp;
                                <input type="text" name="ge_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　－　
                                <input type="text" name="le_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　
                            </td>
                            <td align="center">
                                <input type="reset" class="submit" value="清除搜索"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" class="submit" name="select" value="搜   索"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div><!-- End .content-box-content -->
    </div><!-- End .content-box -->
    <div class="content-box"><!-- Start Content Box -->
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>留言ID</th>
                       <th>用户ID</th>
                       <th>IP地址</th>
                       <th>留言类型</th>
                       <th>审核状态</th>
                       <th>留言内容</th>
                       <th>留言时间</th>
                       <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['guestbook_id']; ?></td>
                            <td><?php if (empty($val['member_id'])){echo "匿名用户";} else {echo $val['member_id'];} ?></td>
                            <td><?php echo $val['ip']; ?></td>
                            <td><?php echo $val['type_title']; ?></td>
                            <td><?php echo $val['audit_status_title']; ?></td>
                            <td><a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN;?>adm_guestbook/message_detail?guestbook_id=<?php echo $val['guestbook_id'];?>&nobar=1', '800px', '400px')">查看内容</a></td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td>
                                <a href="#" data="<?php echo $val['guestbook_id'].'|1'?>" onclick="set_audit(this)">通过审核</a> | 
                                <a href="#" data="<?php echo $val['guestbook_id'].'|2'?>" onclick="set_audit(this)">不予通过</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="8"><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">
// 设置有效性
function set_audit(obj){
    // 获取原值
    var data  = $(obj).attr('data');
    var datas = data.split("|");
    if ('undefined' == typeof datas[0] || 'undefined' == typeof datas[1] || '' == datas[0] || '' == datas[1]) {
        $.BKD.msg('数据错误！');
        return false;
    }

    // 有效性值转换
    var notice = '';
    if (1 == datas[1]) {
        audit_status = 2;
        notice       = '审核通过';
    } else {
        audit_status = 3;
        notice       = '审核不通过';
    }
    if (confirm('确定要将该用户留言设置'+notice+'吗？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_guestbook/set_audit', "guestbook_id=" + datas[0] + "&audit_status=" + audit_status, function (response) {
            if(true === response.success) {
                layer.alert('该留言已设置'+notice, {
                    skin: 'layui-layer-lan',
                    closeBtn: 1,
                    anim: 1
                }, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2});
            }
        },'JSON');
    }
}
</script>