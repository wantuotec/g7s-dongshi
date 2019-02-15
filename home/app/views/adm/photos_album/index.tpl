<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box"><!-- Start Content Box -->
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>相册ID</th>
                       <th>相册封面</th>
                       <th>相册名称</th>
                       <th>相册描述</th>
                       <th>照片数量</th>
                       <th>添加时间</th>
                       <th>是否有效</th>
                       <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['photos_album_id']; ?></td>
                            <td class="img-box"><img src="<?php echo $val['cover_url']; ?>" style="width:50px;height:50px;border:2px solid #fff;"></td>
                            <td><?php echo $val['album_name']; ?></td>
                            <td><?php echo $val['album_describe']; ?></td>
                            <td><?php echo $val['photos_num']; ?></td>
                            <td><?php echo $val['date']; ?></td>
                            <td><?php if ($val['is_enabled'] == 1) { echo "<span class='c-green'>有效</span>"; } else { echo "<span class='c-red'>无效</span>"; }?></td>
                            <td>
                                <a href="#" data="<?php echo $val['photos_album_id'].'|'.$val['is_enabled'];?>" onclick="set_enabled(this)"> <?php if ($val['is_enabled'] == 1) { echo '设为无效'; } else { echo '设为有效'; }?> </a> | 
                                <a href="#" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_album/edit_album?photos_album_id=<?php echo $val['photos_album_id']; ?>&nobar=1','600px', '400px')">修 改</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" align="center">
                            <input type="button" class="submit" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_album/add_album?nobar=1','600px', '400px')" value="新增相册"/>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">
// 设置有效性
function set_enabled(obj){
    // 获取原值
    var data  = $(obj).attr('data');
    var datas = data.split("|");
    if ('undefined' == typeof datas[0] || 'undefined' == typeof datas[1] || '' == datas[0] || '' == datas[1]) {
        $.BKD.msg('数据错误！');
        return false;
    }

    // 有效性值转换
    var notice = '';
    if (1 == datas[1]) {
        is_enabled = 2;
        notice     = '无效';
    } else {
        is_enabled = 1;
        notice     = '有效';
    }
    if (confirm('确定要将该相册设置为'+notice+'吗？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_album/set_enabled', "photos_album_id=" + datas[0] + "&is_enabled=" + is_enabled, function (response) {
        if(true === response.success) {
            $.BKD.refresh();
        } else {
            $.BKD.msg(response.message);
        }
    },'JSON');
    }
}
</script>