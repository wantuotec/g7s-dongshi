<script type="text/javascript">
function add() {
    var configure_name  = $.BKD.value('configure_name');
    var configure_value = $.BKD.value('configure_value');
    var type            = $('select[name=type] option:selected').val();
    var description     = $.BKD.value('description');

    if ('' == configure_name) {
        $.BKD.msg('配置名称为空');
        $('input[name=configure_name]').focus();
        return false;
    } else if ('' == configure_value) {
        $.BKD.msg('配置值为空');
        $('input[name=configure_value]').focus();
        return false;
    } else if ('' == type) {
        $.BKD.msg('请选择类型');
        return false;
    } else if ('' == description) {
        $.BKD.msg('请填写描述说明');
        $('input[name=description]').focus();
        return false;
    }

    $('input[name=add_submit]').attr('disabled', true).removeClass('submit').val('正在添加中...');
    $.post('<?php echo HOME_DOMAIN; ?>adm_configure/add', $('form').serialize(), function (response) {
        if (true === response.success) {
            if ($.BKD.confirm('添加成功！是否继续添加？')) {
                $.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_configure/add');
            } else {
                $.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_configure');
            }
        } else {
            $.BKD.msg(response.message);
            $('input[name=add_submit]').attr('disabled', false).addClass('submit').val('确定添加');
        }
    } ,'JSON');
}
</script>
<div id="main-content"> <!-- Main Content Section with everything -->
    <form name="form" method="post">
        <input type="hidden" name="submit_type" value="add"/>
        <div class="content-box">
            <div class="content-box-header">
                <h3>添加配置</h3>
            </div><!-- End .content-box-header -->
            <div class="content-box-content">
                <table>
                    <colgroup>
                        <col width="20%"/>
                        <col width="40%"/>
                        <col width="40%"/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <td>配置名称：</td>
                        <td><input type="text" name="configure_name" value="" class="text-input large-input"/><br/></td>
                        <td><span class="c-red">例：user_update_password</span></td>
                    </tr>
                    <tr>
                        <td>配置值：</td>
                        <td><input type="text" name="configure_value" value="" class="text-input large-input"/><br/></td>
                        <td><span class="c-red">例：如果有数据要动态展示-->更改密码验证码：{0}请确认本人操作{1}！</span></td>
                    </tr>
                    <tr>
                        <td>类型：</td>
                        <td colspan="2">
                            <select name="type">
                                <option value="">全部</option>
                                <option value="10">客户端</option>
                                <option value="20">服务端</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>描述：</td>
                        <td>
                            <textarea rows="5" type="text" name="description" value="" class="text-input large-input"></textarea>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>

                    </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="10" align="center">
                                <input type="button" name="add_submit" class="submit" value="确定添加" onclick="add()" />　　
                                <input type="button" name="return" class="submit" value="返回" onclick="javascript:history.back(-1);" />
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- End .content-box-content -->
        </div><!-- End .content-box -->
    </form>
</div><!-- End Main Content -->