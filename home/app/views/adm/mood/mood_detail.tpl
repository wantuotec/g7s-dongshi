<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>心情内容 - 点击图片可查看大图</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <tbody>
                        <tr>
                            <td class="img-box" style="background-color: #ededed;"><?php echo $list['content'];?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td align="center">
                                <input type="button" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>
<script>
    //弹出图片相册
    layer.photos({
      photos: '.img-box'
      ,anim: 1 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
</script>