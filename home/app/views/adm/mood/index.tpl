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
                            <td>是否有效：</td>
                            <td>
                                <select name="is_enabled">
                                    <option value="">请选择...</option>
                                    <option value="1">有效</option>
                                    <option value="2">无效</option>
                                </select>
                            </td>
                            <td>添加时间：</td>
                            <td>
                                <input type="text" name="ge_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　－　
                                <input type="text" name="le_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　
                            </td>
                        </tr>
                        <tr style="border-top:1px solid #EAEAEA">
                            <td colspan="4" align="center">
                                <input type="button" class="submit" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_mood/add_mood?nobar=1','800px', '550px')" value="新增心情"/>&nbsp;&nbsp;&nbsp;&nbsp;
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
                       <th>心情ID</th>
                       <th>心情内容</th>
                       <th>添加时间</th>
                       <th>是否有效</th>
                       <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['mood_id']; ?></td>
                            <td><a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN;?>adm_mood/mood_detail?mood_id=<?php echo $val['mood_id'];?>&nobar=1', '800px', '500px')">查看内容</a></td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td><?php if ($val['is_enabled'] == 1) { echo "<span class='c-green'>有效</span>"; } else { echo "<span class='c-red'>无效</span>"; }?></td>
                            <td>
                                <a href="#" data="<?php echo $val['mood_id'].'|'.$val['is_enabled'];?>" onclick="set_enabled(this)"> <?php if ($val['is_enabled'] == 1) { echo '设为无效'; } else { echo '设为有效'; }?> </a> | 
                                <a href="javascript:void(0)" onclick="$.BKD.open('iframe','<?php echo HOME_DOMAIN;?>adm_mood/edit_mood?mood_id=<?php echo $val['mood_id']; ?>&nobar=1','800px', '550px')">修改</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="6"><?php echo $pagination; ?></td>
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
    if (confirm('确定要将该条心情设置为'+notice+'吗？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_mood/set_enabled', "mood_id=" + datas[0] + "&is_enabled=" + is_enabled, function (response) {
            if(true === response.success) {
                $.BKD.refresh();
            } else {
                $.BKD.msg(response.message);
            }
        },'JSON');
    }
}
</script>