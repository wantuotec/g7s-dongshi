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
                            <td>留言类型：
                                <select name="type">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($cfg['type']) && !empty($cfg['type'])):
                                        foreach ($cfg['type'] as $key => $val):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $val;?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>审核状态：
                                <select name="audit_status">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($cfg['audit_status']) && !empty($cfg['audit_status'])):
                                        foreach ($cfg['audit_status'] as $key => $val):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $val;?></option>
                                    <?php endforeach;endif;?>
                                </select>
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
                       <th>回复状态</th>
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
                            <td><?php if (1 == $val['is_reply']) {echo "<span class='c-green'>";} else {echo '<span>';}?><?php echo $val['is_reply_title']; ?></span></td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td>
                                <a href="javascript:void(0)" onclick="delete_guestbook(<?php echo $val['guestbook_id']; ?>)">删除</a>&nbsp;&nbsp;
                                <?php if (2 == $val['is_reply'] && $val['type'] == 1): ?>
                                    <a href="javascript:void(0)" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_guestbook/admin_reply?guestbook_id=<?php echo $val['guestbook_id']; ?>&nobar=1','800px', '550px')">回复留言</a>
                                <?php endif;?>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="9"><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->
<script>
    function delete_guestbook(id) {
        if (confirm('确定是否要删除这条留言？')) {
            $.post('<?php echo HOME_DOMAIN; ?>adm_guestbook/delete_guestbook', "guestbook_id=" + id, function (response) {
                if(true === response.success) {
                    layer.alert('已删除留言！', {
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