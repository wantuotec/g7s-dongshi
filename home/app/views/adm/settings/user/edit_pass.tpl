<div id="main-content"> <!-- Main Content Section with everything -->
			
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>修改密码</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="form" method="post"class="search-content" >
                            <input type="hidden" name="act" value="editPass" />
                            <input type="hidden" name="id" value="<?php echo $filter['id'] ?>" />
                            <table>
                                <colgroup>
                                    <col width="28%"/>
                                    <col width="80%"/>
 	                          </colgroup>
                                <tbody>
                                    <tr>
                                        <td>用户帐号：</td>
                                        <td><?php echo $filter['user'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>新密码：</td>
                                        <td><input type="text" value="" name="password" class="text-input"/></td>
                                    </tr>
                                    <tr>
                                        <td>再次输入密码：</td>
                                        <td><input type="text" value="" name="last_password" class="text-input"/></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <input id="editPass" type="button" value="确定修改密码" class="submit" onclick="eidtPass()" name="editPassword"/> 　　
                                            <input type="button" onclick="window.parent.$.BKD.close_current();" value="关闭" class="submit">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </form>  
                </div><!-- End .content-box-content -->        
                      
            </div><!-- End .content-box -->
			
</div><!-- End Main Content -->
<?php echo script_tag(array('address.js')); ?>
<script type="text/javascript">
// 添加站点
function eidtPass() {
    if ('' == $('input[name=password]').val()) {
        $.BKD.msg("请输入新密码");
        $('input[name=password]').focus();
        return false;
    }

    if ('' == $('input[name=last_password]').val()) {
        $.BKD.msg("请再次输入新密码");
        $('input[name=last_password]').focus();
        return false;
    }

    if ($('input[name=password]').val() != $('input[name=last_password]').val()) {
        $.BKD.msg("两次密码不一致");
        $('input[name=last_password]').focus();
        return false;
    }

    $('input[name=editPassword]').attr('disabled', true).removeClass('submit').val('正在修改密码...');
    $.post('<?php echo HOME_DOMAIN; ?>adm_user/editPass', $('form').serialize(), function (response) {
        if (true === response.success) {
            $.BKD.confirm('密码修改成功！');
            window.parent.$.BKD.close_current();
        } else {
            $.BKD.msg(response.message);
            $('input[name=editPassword]').attr('disabled', false).addClass('submit').val('确定修改密码');
        }
    } ,'JSON');
}
</script>