<script type="text/javascript">
function edit() {
    var configure_value = $.BKD.value('configure_value');
    var type            = $('select[name=type] option:selected').val();
    var description     = $.BKD.value('description');

    if ('' == configure_value) {
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

    $('input[name=edit_submit]').attr('disabled', true).removeClass('submit').val('正在编辑中...');
    $.post('<?php echo HOME_DOMAIN; ?>adm_configure/edit', $('form').serialize(), function (response) {
        if (true === response.success) {
            $.BKD.msg('编辑成功!');
            javascript:history.back(-1);
        } else {
            $.BKD.msg(response.message);
            $('input[name=edit_submit]').attr('disabled', false).addClass('submit').val('确定编辑');
        }
    } ,'JSON');
}
</script>
<div id="main-content"> <!-- Main Content Section with everything -->
    <form name="form" method="post">
        <input type="hidden" value="<?php echo $configure['configure_id']; ?>" name="configure_id"/>
        <div class="content-box">
            <div class="content-box-header">
                <h3>修改配置</h3>
            </div><!-- End .content-box-header -->
            <div class="content-box-content">
                <table>
                    <colgroup>
                        <col width="10%"/>
                        <col width="40%"/>
                        <col width="40%"/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <td>配置名称：</td>
                        <td>
                            <?php echo $configure['configure_name']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>配置值：</td>
                        <td><input type="text" name="configure_value" value="" class="text-input large-input"/><br/></td>
                        <td><span class="c-red">例：如果有数据要动态展示---->更改密码验证码：{0}请保密并确认本人操作{1}！</span></td>
                    </tr>
                    <tr>
                        <td>类型：</td>
                        <td colspan="2">
                            <select name="type">
                                <option value="">全部</option>
                                <option value="10" <?php if(10 == $configure['type']){?>selected<?php }?>>客户端</option>
                                <option value="20" <?php if(20 == $configure['type']){?>selected<?php }?>>服务端</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>描述：</td>
                        <td>
                            <!-- <input type="text" name="description" class="text-input large-input" style="width: 80% !important;"/> -->
                            <textarea rows="5" type="text" name="description" class="text-input large-input" style="width: 80% !important;"></textarea>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>

                    </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="10" align="center">
                                <input type="button" name="edit_submit" class="submit" value="确定编辑" onclick="edit()" />　　
                                <input type="button" name="return" class="submit" value="返回" onclick="javascript:history.back(-1);" />
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- End .content-box-content -->
        </div><!-- End .content-box -->
    </form>
</div><!-- End Main Content -->