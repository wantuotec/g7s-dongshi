    <div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>站点Slogan列表</h3>
        </div>
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>ID</th>
                       <th>说明</th>
                       <th>内容</th>
                       <th>有效性</th>
                       <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['slogan_id'];?></td>
                            <td><?php echo $val['item_explain'];?></td>
                            <td><?php echo $val['content']; ?></td>
                            <td><?php if ($val['is_enabled'] == 1) { echo "<span class='c-green'>有效</span>"; } else { echo "<span class='c-red'>无效</span>"; }?>
                            </td>
                            <td>
                                <a href="#" data="<?php echo $val['slogan_id'].'|'.$val['is_enabled'];?>" onclick="set_enabled(this)"> <?php if ($val['is_enabled'] == 1) { echo '设为无效'; } else { echo '设为有效'; }?> </a> | 
                                <a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN;?>adm_slogan/edit_slogan?slogan_id=<?php echo $val['slogan_id'];?>&nobar=1', '800px', '300px')">修改内容</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
    is_enabled = datas[1] == 1 ? 2 : 1;
    $.post('<?php echo HOME_DOMAIN; ?>adm_slogan/set_enabled', "slogan_id=" + datas[0] + "&is_enabled=" + is_enabled, function (response) {
        if(true === response.success) {
            $.BKD.refresh();
        } else {
            $.BKD.msg(response.message);
        }
    },'JSON');
}
</script>