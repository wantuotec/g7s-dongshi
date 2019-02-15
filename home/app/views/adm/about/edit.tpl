<?php echo script_tag(array('kindeditor/kindeditor-min.js', 'kindeditor/lang/zh_CN.js')) ?>
<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>编辑信息</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <colgroup>
                        <col width="50%"/>
                        <col width="50%"/>
                    </colgroup>
                    <tbody>
                        <input type="hidden" name="about_id" value="<?php echo $list['about_id'];?>">
                        <tr>
                            <td>网站域名：
                                <input type="text" maxlength="30" class="text-input large-input" name="domain_name" value="">
                            </td>
                            <td>创建时间：
                                <input type="text" maxlength="30" class="text-input large-input" name="website_create" value="">
                            </td>
                        </tr>
                        <tr>
                            <td>当前版本：
                                <input type="text" maxlength="30" class="text-input large-input" name="version" value="">
                            </td>
                            <td>源服务器：
                                <input type="text" maxlength="60" class="text-input large-input" name="servicer" value="">
                            </td>
                        </tr>
                        <tr style="border-bottom:1px solid #ccc">
                            <td>服务程序：
                                <input type="text" maxlength="60" class="text-input large-input" name="program" value="">
                            </td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>站长昵称：
                                <input type="text" maxlength="50" class="text-input large-input" name="nickname" value="">
                            </td>
                            <td>真实姓名：
                                <input type="text" maxlength="50" class="text-input large-input" name="real_name" value="">
                            </td>
                        </tr>
                        <tr>
                            <td>站长性别：
                                <select name="sex">
                                    <option value="1" selected="selected">--- 男 ---</option>
                                    <option value="2">--- 女 ---</option>
                                </select>
                            </td>
                            <td>当前年龄：
                                <input type="text" maxlength="50" placeholder="如：1993-11-26" class="text-input large-input" name="birthday">
                            </td>
                        </tr>
                        <tr>
                            <td>户籍地址：
                                <input type="text" maxlength="50" class="text-input large-input" name="register_address" value="">
                            </td>
                            <td>现居住地：
                                <input type="text" maxlength="50" class="text-input large-input" name="live_address" value="">
                            </td>
                        </tr>
                        <tr>
                            <td>当前职业：
                                <input type="text" maxlength="50" class="text-input large-input" name="job" value="">
                            </td>
                            <td>联系方式：
                                <input type="text" maxlength="50" class="text-input large-input" name="contact" value="">
                            </td>
                        </tr>
                        <tr>
                            <td>喜欢书籍：
                                <input type="text" maxlength="50" class="text-input large-input" name="like_books" value="">
                            </td>
                            <td>喜欢音乐：
                                <input type="text" maxlength="50" class="text-input large-input" name="like_musics" value="">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">站长描述：</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <textarea id="editor" name="master_describe" style="width:100%; height:300px;"></textarea>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <input class="submit" value="保 存" onclick="save_edit()" type="button">&nbsp;&nbsp;&nbsp;
                                <input class="submit" name="return" value="返 回" onclick="javascript:history.back(-1);" type="button">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('textarea[name="master_describe"]', {
            allowFileManager: true,
            uploadJson: "<?php echo HOME_DOMAIN; ?>upload/index?origin_type=website_info",
        });
    });

    var save_edit = function () {
        if (editor.html() == '') {
            $.BKD.msg('请填写内容');
            $('textarea[name=master_describe]').focus();
            return false;
        }

        var content = editor.html();
        // 此处加一个内容的文本框，防止出现 CI提示 'Disallowed Key Characters.' 的错误
        $('<input type="hidden" name="master_describe">').appendTo('form');
        $('input[name=master_describe]').val(content);

        $('input[name=add_submit]').attr('disabled', true).removeClass('submit').val('正在添加中...');
        $.post('<?php echo HOME_DOMAIN; ?>adm_about/edit_website_info', $('form').serialize(), function (response) {
            if (true === response.success) {
                layer.alert('修改成功', {icon: 1}, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2}, function(){
                    $('input[name=edit_submit]').attr('disabled', false).addClass('submit').val('保 存');
                }); 
            }
        }, 'JSON');

    };
</script>