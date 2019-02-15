<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>修改 &nbsp;&nbsp;&nbsp;&nbsp;<span class="c-green"> <?php echo $search['item_explain'];?> </span></h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <tbody>
                        <tr>
                            <input type="hidden" name="slogan_id" value="<?php echo $search['slogan_id'];?>">
                            <td>Slogan内容：
                                <input type="text" maxlength="50" class="text-input large-input" name="content"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td align="center">
                                <input class="submit" name="add_submit" value="修   改" type="button" onclick="edit_slogan();"/>&nbsp;&nbsp;
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
function edit_slogan(){
    $.post('<?php echo HOME_DOMAIN; ?>adm_slogan/edit_slogan', $('form').serialize(), function (response) {
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
