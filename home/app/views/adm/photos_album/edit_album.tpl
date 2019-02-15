<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>修改相册</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <input type="hidden" name="photos_album_id" value="<?php $search['photos_album_id'];?>">
                    <tbody>
                        <tr>
                            <td>相册名称：</td>
                            <td><input type="text" maxlength="20" class="text-input" name="album_name"></td>
                        </tr>
                        <tr>
                            <td>相册描述：</td>
                            <td><input type="text" maxlength="50" class="text-input large-input" name="album_describe"></td>
                        </tr>
                        <tr>
                            <td>是否有效：</td>
                            <td>
                                有效 <input type="radio" name="is_enabled" value="1">&nbsp;&nbsp;&nbsp;&nbsp;
                                无效 <input type="radio" name="is_enabled" value="2" checked="true">
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" align="center">
                                <input class="submit" name="add_submit" value="修   改" type="button" onclick="add_category();"/>&nbsp;&nbsp;
                                <input type="button" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function add_category(){
    $.post('<?php echo HOME_DOMAIN; ?>adm_album/edit_album', $('form').serialize(), function (response) {
        if (true === response.success) {
            layer.alert('修改成功', {icon: 1}, function(){
                $.BKD.refresh_parent();
            });
        } else {
            layer.alert(response.message, {icon: 2});
        }
    } ,'JSON');
}
</script>
