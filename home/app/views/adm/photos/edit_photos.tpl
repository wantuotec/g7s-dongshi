<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>修改照片【照片不可更换】</h3>
        </div>
        <div class="content-box-content">
            <form method="post" class="search-content">
                <div style="width:50%;float:left">
                    <table>
                        <input type="hidden" name="photos_id" value="<?php echo $search['photos_id'];?>">
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
                            <td class="img-box">
                                <img src="<?php echo $search['full_photo_url'];?>" id="photo" style="width:40px; height:40px;" />
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
                            <input type="button" name='add_submit' class="submit" value="修   改" onclick="add_image();">&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" value="1215154" name="tmpdir" id="id_file">
                            <input type="button" name="return" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();" />
                        </td>
                    </tr>
                </table>
            </div>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">
function add_image()
{
    // 验证信息不能为空
    if ($("input[name=photos_id]").val() == '') {
        layer.alert('异常：相片ID为空！', {icon: 7});
        return false;
    }
    if ($("select[name=photos_album_id]").val() == '') {
        layer.alert('请选择所属相册！', {icon: 7});
        return false;
    }
    if ($("input[name=photo_title]").val() == '') {
        layer.alert('请填写照片标题！', {icon: 7});
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>adm_photos/edit_photos', $('form').serialize(), function (response) {
        if (true === response.success) {
            layer.alert('照片修改成功', {icon: 1}, function(){
                $.BKD.refresh_parent();
            });
        } else {
            layer.alert(response.message, {icon: 2});
        }
    }, 'JSON');
}

//弹出图片相册
layer.photos({
  photos: '.img-box'
  ,anim: 1 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
});
</script>