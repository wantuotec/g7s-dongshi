<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>添加照片【可批量添加】</h3>
        </div>
        <div class="content-box-content">
            <form method="post" class="search-content">
                <div style="width:50%;float:left">
                    <table>
                        <tr>
                            <td>所属相册：
                                <select name="photos_album_id" id="photos_album_id">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($photos_album) && !empty($photos_album)):
                                        foreach ($photos_album as $album):
                                    ?>
                                        <option value="<?php echo $album['photos_album_id'];?>"><?php echo $album['album_name'];?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="float:left;">
                    <table>
                        <tr>
                            <td>
                                <input type="file" name="file_upload" id="file_upload" />
                                <input type="hidden" name="photo_url" id="photo_url" value="">
                                <img src="<?php echo HOME_DOMAIN;?>public/images/add_round.png" id="photo" style="width:40px; height:40px; margin-left:140px;margin-top:-45px;" />
                            </td>
                        </tr>
                    </table>
                </div>

                <table>
                    <tr>
                        <td>照片标题：
                            <input type="text" maxlength="50" name="photo_title" class="text-input">
                        </td>
                    </tr>
                </table>

                <table>
                    <tr>
                        <td>照片描述：
                            <textarea rows="5" name="photo_describe"></textarea>
                        </td>
                    </tr>
                </table>
            </form>

            <div style="width:100%;text-align:center;border-top:1px solid #E7E7E7">
                <table>
                    <tr>
                        <td>
                            <input type="button" name='add_submit' class="submit" value="上   传" onclick="add_image();">&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" value="1215154" name="tmpdir" id="id_file">
                            <input type="button" name="return" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();" />
                        </td>
                    </tr>
                </table>
            </div>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->

<?php echo  css_tag(array('admin/uploadify.css'),$css) ?>
<?php echo  script_tag(array('uploadify/jquery.uploadify-3.1.min.js'), $js) ?>

<script type="text/javascript">
$(function(){
    $("#file_upload").uploadify({
        'swf'         : "<?php echo HOME_DOMAIN; ?>public/js/uploadify/uploadify.swf",
        'uploader'    : "<?php echo HOME_DOMAIN; ?>upload/upload_image",
        'fileTypeExts': '*.gif; *.jpg; *.png; *.jpeg;',
        'fileTypeDesc': 'Image Files',
        'fileObjName' : 'imgFile',
        'buttonText'  : '选择照片',
        'auto'        : true,
        'multi'       : false,
        'method'      : 'get',
        'formData'    : { 'origin_type': "photos" },
        'onUploadSuccess': function (file,data,response) {
            var result = $.parseJSON(data);

            $("#photo").attr({src:result.full_origin_url});
            $("#photo_url").val(result.origin_url);
        }
    });
})

function add_image()
{
    // 验证信息不能为空
    if ($("select[name=photos_album_id]").val() == '') {
        layer.alert('请选择所属相册！', {icon: 7});
        return false;
    }
    if ($("input[name=photo_url]").val() == '') {
        layer.alert('请选择要上传的照片！', {icon: 7});
        return false;
    }
    if ($("input[name=photo_title]").val() == '') {
        layer.alert('请填写照片标题！', {icon: 7});
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>adm_photos/add_photos', $('form').serialize(), function (response) {
        if (true === response.success) {
            if ($.BKD.confirm('照片添加成功！是否继续添加？')) {
                $.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_photos/add_photos');
            } else {
                $.BKD.refresh_parent();
            } 
        } else {
            layer.alert(response.message, {icon: 2});
        }
    }, 'JSON');
}
</script>