<?php echo  css_tag(array('admin/uploadify.css'),$css) ?>
<?php echo script_tag(array('kindeditor/kindeditor-min.js', 'kindeditor/lang/zh_CN.js', 'uploadify/jquery.uploadify-3.1.min.js')) ?>
<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>新增内容</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" enctype="multipart/form-data" method="POST"
                  action="<?php echo BKD_DOMAIN; ?>content_manage/add">
                <table>
                    <tbody>
                        <tr>
                            <td>文章标题：
                                <input type="text" maxlength="50" name="article_title" class="text-input large-input">
                            </td>
                            <td>
                                <input type="file" id="upload_cover_photo" />
                                <input type="hidden" name="cover_photo" id="cover_photo" value="">
                                <img src="<?php echo HOME_DOMAIN;?>public/images/add_round.png" id="cover" style="width:40px; height:40px; margin-left:150px;margin-top:-40px;" />
                            </td>
                        </tr>
                        <tr>
                            <td>文章分类：
                                <select name="article_category_id">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($category_list) && !empty($category_list)):
                                        foreach ($category_list as $category):
                                    ?>
                                        <option value="<?php echo $category['article_category_id'];?>"><?php echo $category['category_name'];?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>文章来源：
                                <select name="origin_type">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($origin_type) && !empty($origin_type)):
                                        foreach ($origin_type as $key => $origin):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $origin;?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">封面文字：</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left:20px;"><textarea name="cover_words" rows="5" maxlength="100"></textarea></td>
                        </tr>
                        <tr>
                            <td colspan="2">文章内容：</td>
                        </tr>
                    </tbody>
                </table>

                <table>
                    <tbody>
                        <div class="content-box-content">
                            <textarea id="editor" name="content" style="width:100%;height:500px;"></textarea>
                        </div>
                        <tr>
                            <td colspan="10" align="center">
                                <input class="submit" type="button" name="add_submit" value="保   存" onclick="add()"/>　
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
    // 添加封面图
    $(function(){
        $("#upload_cover_photo").uploadify({
            'swf'         : "<?php echo HOME_DOMAIN; ?>public/js/uploadify/uploadify.swf",
            'uploader'    : "<?php echo HOME_DOMAIN; ?>upload/upload_image",
            'fileTypeExts': '*.gif; *.jpg; *.png',
            'fileTypeDesc': 'Image Files',
            'fileObjName' : 'imgFile',
            'buttonText'  : '封面图片：',
            'auto'        : true,
            'multi'       : false,
            'method'      : 'get',
            'formData'    : { 'origin_type': "article" },
            'onUploadSuccess': function (file,data,response) {
                var result = $.parseJSON(data);
                $("#cover").attr({src:result.full_origin_url});
                $("#cover_photo").val(result.origin_url);
            }
        });
    })

    // 编辑器
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('textarea[name="content"]', {
            allowFileManager: true,
            uploadJson: "<?php echo HOME_DOMAIN; ?>upload/index?origin_type=article",
        });
    });

    // 保存数据
    var add = function () {
        if ('' == $("input[name=article_title]").val()) {
            layer.alert('请填写文章标题', {icon: 7});
            $('input[name=title]').focus();
            return false;
        }

        if ('' == $("select[name=article_category_id]").val()) {
            layer.alert('请选择文章分类', {icon: 7});
            return false;
        }

        if ('' == $("select[name=origin_type]").val()) {
            $.BKD.msg('请选择文章来源');
            layer.alert('请填写文章标题', {icon: 7});
            return false;
        }

        if ('' == $("textarea[name=cover_words]").val()) {
            layer.alert('请填写封面文字', {icon: 7});
            return false;
        }

        if (editor.html() == '') {
            layer.alert('请填写文章内容', {icon: 7});
            $('input[name=content]').focus();
            return false;
        }

        var content = editor.html();
        // 此处加一个内容的文本框，防止出现 CI提示 'Disallowed Key Characters.' 的错误
        $('<input type="hidden" name="content">').appendTo('form');
        $('input[name=content]').val(content);

        $('input[name=add_submit]').attr('disabled', true).removeClass('submit').val('正在保存中...');
        $.post('<?php echo HOME_DOMAIN; ?>adm_article/add_article', $('form').serialize(), function (response) {
            if (true === response.success) {
                if ($.BKD.confirm('文章添加成功！是否继续添加？')) {
                    $.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_article/add_article');
                } else {
                    $.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_article/index');
                } 
            } else {
                layer.alert(response.message, {icon: 2});
                $('input[name=add_submit]').attr('disabled', false).addClass('submit').val('保   存');
            }
        }, 'JSON');
    };
</script>