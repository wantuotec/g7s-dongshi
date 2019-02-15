<?php echo script_tag(array('kindeditor/kindeditor-min.js', 'kindeditor/lang/zh_CN.js')) ?>
<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>用户留言</h3>
        </div>
        <div class="content-box-content">
            <table>
                <tbody>
                    <?php echo $search['message'];?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-box">
        <div class="content-box-header">
            <h3>回复留言</h3>
        </div>
        <div class="content-box-content">
            <form class="search-content" enctype="multipart/form-data" method="post"
                  action="<?php echo BKD_DOMAIN; ?>adm_mood/add_mood">
                <table>
                    <tbody>

                    <input type="hidden" name="guestbook_id" value="<?php echo $search['guestbook_id'];?>">
                    <textarea id="editor" name="content" style="width:100%; height:200px;"></textarea>

                    <tr>
                        <td colspan="10" align="center">
                            <input class="submit" type="button" name="to_reply" value="回   复" onclick="edit()"/>　
                            <?php if (1 == intval($search['nobar'])) { ?>
                                <input type="button" class="submit" value="关   闭"
                                       onclick="window.parent.$.BKD.close_current();"/>
                            <?php } else { ?>
                                <input type="button" name="return" class="submit" value="返   回"
                                       onclick="javascript:history.back(-1);"/>
                            <?php } ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('textarea[name="content"]', {
            allowFileManager: true,
            uploadJson: "<?php echo HOME_DOMAIN; ?>upload/index?origin_type=guestbook",
        });
    });

    var edit = function () {
        if (editor.html() == '') {
            $.BKD.msg('请填写回复内容');
            $('input[name=content]').focus();
            return false;
        }

        var content = editor.html();
        // 此处加一个内容的文本框，防止出现 CI提示 'Disallowed Key Characters.' 的错误
        $('<input type="hidden" name="content">').appendTo('form');
        $('input[name=content]').val(content);

        $('input[name=to_reply]').attr('disabled', true).removeClass('submit').val('正在回复中...');
        $.post('<?php echo HOME_DOMAIN; ?>adm_guestbook/admin_reply', $('form').serialize(), function (response) {
            if (true === response.success) {
                layer.alert('回复用户留言成功', {icon: 1}, function(){
                    $.BKD.refresh_parent();
                });
            } else {
                layer.alert(response.message, {icon: 2});
                $('input[name=to_reply]').attr('disabled', false).addClass('submit').val('回   复');
            }
        }, 'JSON');

    };
</script>