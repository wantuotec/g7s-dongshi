<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3><?php if(1 == $list['type']){echo "留言内容";}else{echo "回复内容";}?> &nbsp;&nbsp;&nbsp;&nbsp;【ID：<span class="c-red"><?php echo $list['guestbook_id'];?></span>】</h3>
        </div>
        <div class="content-box-content">
            <table>
                <tbody>
                    <?php echo $list['message'];?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($list['reply_list'])): 
        foreach ($list['reply_list'] as $reply):
    ?>
        <div class="content-box">
            <div class="content-box-header">
                <h3>管理员回复</h3>
            </div>
            <div class="content-box-content">
                <table>
                    <tbody>
                        <?php echo $reply['message'];?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach;endif;?>

    <div align="center">
        <?php if (1 == intval($search['nobar'])) { ?>
            <input type="button" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();"/>
        <?php } else { ?>
            <input type="button" name="return" class="submit" value="返   回" onclick="javascript:history.back(-1);"/>
        <?php } ?>
    </div>
</div>
<script>
    //弹出图片相册
    layer.photos({
      photos: '.img-box'
      ,anim: 1 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
</script>