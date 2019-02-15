<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>文章封面</h3>
        </div>
        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <tbody>
                        <tr>
                            <td class="img-box"><img src="<?php echo $list['cover_photo']; ?>" style="width:50px;height:50px;border:1px solid #ededed;cursor:pointer;"></td>
                            <td><?php echo $list['cover_words'];?></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="content-box">
        <div class="content-box-header">
            <h3>文章主体</h3>
        </div>
        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <tbody>
                        <tr>
                            <td class="img-box"><?php echo $list['content'];?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td align="center">
                                <?php if (1 == intval($search['nobar'])) { ?>
                                    <input type="button" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();"/>
                                <?php } else { ?>
                                    <input type="button" name="return" class="submit" value="返   回" onclick="javascript:history.back(-1);"/>
                                <?php } ?>
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