<?php echo script_tag(array('kindeditor/kindeditor-min.js', 'kindeditor/lang/zh_CN.js')) ?>
<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>添加心情 &nbsp;&nbsp;&nbsp;&nbsp;<span class="c-green"> 提示：每条心情最多只一张配图哦 </span></h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" enctype="multipart/form-data" method="post"
                  action="<?php echo BKD_DOMAIN; ?>adm_mood/add_mood">
                <table>
                    <input type="hidden" name="mood_id">
                    <tbody>
                    <div class="content-box-content">
                        <textarea id="editor" name="content" style="width:100%; height:300px;"></textarea>
                    </div>
                    <tr>
                        <td colspan="10" align="center">
                            <input class="submit" type="button" name="add_submit" value="修   改" onclick="add()"/>　
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
            uploadJson: "<?php echo HOME_DOMAIN; ?>upload/index?origin_type=mood",
        });
    });

    var add = function () {
        if (editor.html() == '') {
            $.BKD.msg('请填写内容');
            $('input[name=content]').focus();
            return false;
        }

        var content = editor.html();
        // 此处加一个内容的文本框，防止出现 CI提示 'Disallowed Key Characters.' 的错误
        $('<input type="hidden" name="content">').appendTo('form');
        $('input[name=content]').val(content);

        $.post('<?php echo HOME_DOMAIN; ?>adm_mood/edit_mood', $('form').serialize(), function (response) {
            if (true === response.success) {
                layer.alert('心情修改成功', {icon: 1}, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2});
            }
        }, 'JSON');

    };
</script>