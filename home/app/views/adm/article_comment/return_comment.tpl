<?php echo  css_tag(array('uploadify.css'),$css) ?>
<?php echo script_tag(array('kindeditor/kindeditor-min.js', 'kindeditor/lang/zh_CN.js', 'uploadify/jquery.uploadify-3.1.min.js')) ?>
<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>用户：<span class="c-green"><?php echo $list['customer_name'];?></span>
                &nbsp;&nbsp;&nbsp;&nbsp; 时间：<span class="c-green"><?php echo $list['create_time'];?></span>
            </h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" enctype="multipart/form-data" method="POST"
                  action="<?php echo HOME_DOMAIN; ?>adm_article_comment/return_comment">
                <table>
                    <input type="hidden" name="article_id" value="<?php echo $list['article_id'];?>">
                    <input type="hidden" name="parent_comment_id" value="<?php echo $list['article_comment_id'];?>">
                    <input type="hidden" name="level" value="<?php echo $list['level'];?>">
                    <tbody>
                        <tr>
                            <td colspan="2">对象内容：</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left:20px;"><textarea disabled="true" rows="5"><?php echo $list['comment'];?></textarea></td>
                        </tr>
                        <tr>
                            <td colspan="2">回复内容：</td>
                        </tr>
                    </tbody>
                </table>

                <table>
                    <tbody>
                        <div class="content-box-content">
                            <textarea id="editor" name="comment" style="width:100%;height:100px;"></textarea>
                        </div>
                        <tr>
                            <td colspan="10" align="center">
                                <input class="submit" type="button" name="add_submit" value="回   复" onclick="to_return()"/>　
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
    // 编辑器
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('textarea[name="comment"]', {
            allowFileManager: true,
            uploadJson: "<?php echo HOME_DOMAIN; ?>upload/index?origin_type=article",
        });
    });

    // 执行回复
    function to_return() {
        var comment = editor.html();
        if ('' == comment) {
            layer.alert('请填写评论回复内容', {icon: 7});
            return false;
        }
        if ('' == $('input[name=article_id]').val()) {
            layer.alert('文章ID为空', {icon: 7});
            return false;
        }
        if ('' == $('input[name=parent_comment_id]').val()) {
            layer.alert('回复评论对象的ID为空', {icon: 7});
            return false;
        }
        if ('' === $('input[name=level]').val()) {
            layer.alert('回复评论对象的等级为空', {icon: 7});
            return false;
        }

        // 此处加一个内容的文本框，防止出现 CI提示 'Disallowed Key Characters.' 的错误
        $('<input type="hidden" name="comment">').appendTo('form');
        $('input[name=comment]').val(comment);

        layer.confirm('确定要回复文章评论吗？', {
          btn: ['确 认','算 了']
        }, function(){
            $.post('<?php echo HOME_DOMAIN; ?>adm_article_comment/return_comment', $('form').serialize(), function (response) {
                if (true === response.success) {
                    layer.alert('评论回复成功', {icon: 1}, function(){
                        $.BKD.refresh();
                    });
                } else {
                    layer.alert(response.message, {icon: 2});
                }
            } ,'JSON');
        });
    }
</script>