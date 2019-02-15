    <div id="main-content"> <!-- Main Content Section with everything -->
    <!-- Page Head -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>搜 索</h3>
        </div><!-- End .content-box-header -->

        <div class="content-box-content">
            <form method="get" class="search-content">
                <table>
                    <tbody>
                        <tr>
                            <td>照片标题：</td>
                            <td>
                                <input type="text" name="like_photo_title" class="text-input">
                            </td>
                            <td>所属相册：</td>
                            <td>
                                <select name="photos_album_id">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($photos_album) && !empty($photos_album)):
                                        foreach ($photos_album as $album):
                                    ?>
                                        <option value="<?php echo $album['photos_album_id'];?>"><?php echo $album['album_name'];?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>是否有效：</td>
                            <td>
                                <select name="is_enabled">
                                    <option value="">请选择...</option>
                                    <option value="1">有效</option>
                                    <option value="2">无效</option>
                                </select>
                            </td>
                            <td>点赞排序：</td>
                            <td>
                                <select name="order_by_like_num">
                                    <option value="">请选择...</option>
                                    <option value="1">升 序 ↑</option>
                                    <option value="2">降 序 ↓</option>
                                </select>
                            </td>
                        </tr>
                        <tr style="border-top:1px solid #EAEAEA">
                            <td colspan="8" align="center">
                                <input type="button" class="submit" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_photos/add_photos?nobar=1','800px', '500px')" value="新增照片"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="reset" class="submit" value="清除搜索"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" class="submit" name="select" value="搜   索"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div><!-- End .content-box-content -->
    </div><!-- End .content-box -->
    <div class="content-box"><!-- Start Content Box -->
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>照片预览</th>
                       <th>照片ID</th>
                       <th>所属相册</th>
                       <th>照片标题</th>
                       <th>照片描述</th>
                       <th>点赞数量</th>
                       <th>添加时间</th>
                       <th>是否有效</th>
                       <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td class="img-box"><img src="<?php echo $val['photo_url']; ?>" style="width:50px;height:50px;border:2px solid #fff;"></td>
                            <td><?php echo $val['photos_id']; ?></td>
                            <td><?php echo $val['album_name']; ?></td>
                            <td><?php echo $val['photo_title']; ?></td>
                            <td><?php echo mb_substr($val['photo_describe'], 0, 20); ?></td>
                            <td><?php echo $val['like_num']; ?></td>
                            <td><?php echo $val['date']; ?></td>
                            <td><?php if ($val['is_enabled'] == 1) { echo "<span class='c-green'>有效</span>"; } else { echo "<span class='c-red'>无效</span>"; }?></td>
                            <td>
                                <a href="#" data="<?php echo $val['photos_id'].'|'.$val['is_enabled'];?>" onclick="set_enabled(this)"> <?php if ($val['is_enabled'] == 1) { echo '设为无效'; } else { echo '设为有效'; }?> </a> | 
                                <a href="#" onclick="set_cover(<?php echo $val['photos_id'];?>)">设为封面</a> | 
                                <a href="#" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_photos/edit_photos?photos_id=<?php echo $val['photos_id'];?>&nobar=1','800px', '500px')">修改</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="9"><?php echo $pagination; ?></td>
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
        layer.alert('数据错误！', {icon: 7});
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
    if (confirm('确定要将该照片设置为'+notice+'吗？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_photos/set_enabled', "photos_id=" + datas[0] + "&is_enabled=" + is_enabled, function (response) {
            if(true === response.success) {
                $.BKD.refresh();
            } else {
                $.BKD.msg(response.message);
            }
        },'JSON');
    }
}

// 设置相册封面
function set_cover(photos_id){
    if ('' == photos_id) {
        layer.alert('异常：照片ID为空', {icon: 7});
        return false;
    }

    $.post('<?php echo HOME_DOMAIN; ?>adm_photos/set_cover', "photos_id=" + photos_id, function (response) {
        if(true === response.success) {
            layer.alert('设置相册封面成功！', {icon: 1});
        } else {
            layer.alert(response.message, {icon: 2});
        }
    },'JSON');
}

//弹出图片相册
layer.photos({
  photos: '.img-box'
  ,anim: 1 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
});
</script>