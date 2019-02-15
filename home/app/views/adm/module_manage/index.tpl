    <div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>功能模块列表 &nbsp;&nbsp;<span class="c-red"> （涉及点较少，数据为数据库直接添加） </span></h3>
        </div>
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>ID</th>
                       <th>模块说明</th>
                       <th>模块标记</th>
                       <th>是否开启</th>
                       <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['module_manage_id'];?></td>
                            <td><?php echo $val['module_explain'];?></td>
                            <td><?php echo $val['module_mark']; ?></td>
                            <td><?php if ($val['is_open'] == 1) { echo "<span class='c-green'>已开启</span>"; } else { echo "<span class='c-red'>已关闭</span>"; }?>
                            </td>
                            <td>
                                <a href="#" data="<?php echo $val['module_manage_id'].'|'.$val['is_open'];?>" onclick="set_open(this)"> <?php if ($val['is_open'] == 1) { echo '设为关闭'; } else { echo '设为开启'; }?> </a>
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
function set_open(obj){
    // 获取原值
    var data  = $(obj).attr('data');
    var datas = data.split("|");
    if ('undefined' == typeof datas[0] || 'undefined' == typeof datas[1] || '' == datas[0] || '' == datas[1]) {
        $.BKD.msg('数据错误！');
        return false;
    }

    // 有效性值转换
    is_open = datas[1] == 1 ? 2 : 1;
    $.post('<?php echo HOME_DOMAIN; ?>adm_module_manage/set_open', "module_manage_id=" + datas[0] + "&is_open=" + is_open, function (response) {
        if(true === response.success) {
            $.BKD.refresh();
        } else {
            $.BKD.msg(response.message);
        }
    },'JSON');
}
</script>