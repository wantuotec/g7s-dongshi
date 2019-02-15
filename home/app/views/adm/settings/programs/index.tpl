<style>
.input{width:150px;height:20px;line-height:20px;padding-left:3px;}
.err_msg{border:1px solid red;}
#main-content .content-box .menu table tbody tr:hover{background:beige;}
</style>
<script language="javascript" type="text/javascript">

// 获得数组中的最大值
Array.prototype.max = function(){
    return Math.max.apply({},this)
}

/**
 * 删除
 */
function del(obj) {
    var group_id = obj.parent().parent().find("input[name='group_id[]']").val();
    if ($(".sub_"+group_id).length > 0) {
        $.BKD.msg('该菜单下存在二级菜单，不能删除！');
        return false;
    } else {
        obj.parent().parent().remove();
    }
}


/**
 * 展开或收缩二级菜单
 */
function play_show(obj, group_id) {
    var icon_url = obj.find("img").attr("src");

    if (icon_url == "<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/play_show.png") {
       obj.find("img").attr("alt", "收缩二级菜单");
        obj.find("img").attr("src", "<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/play.png");
        $(".sub_"+group_id).hide();
    } else {
       obj.find("img").attr("alt", "展开二级菜单");
        obj.find("img").attr("src", "<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/play_show.png");
        $(".sub_"+group_id).show();
    }
}

/**
 * 是否显示
 */
function update_display(obj, group_id) {
    var is_display = obj.parent().find("input[type=hidden]");

    if (is_display.val() == 1) {
       is_display.val('0');
       obj.html('<img alt="隐藏" src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/cross_circle.png">隐藏');
    } else {
       is_display.val('1');
        obj.html('<img alt="显示" src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/tick_circle.png">显示');
    }
}

/**
 * 添加一级栏目
 */
function add_menu()
{
    var htmls = '';

    // 防止页面组 ID 重复
    var group_id = $(".item").length + 1000;

    htmls += '<tr class="item">';
        htmls += '<td>';
            htmls += '<input type="hidden" name="group_id[]" value="'+group_id+'" />';
            htmls += '<input type="hidden" name="menu['+group_id+'][systemId]" value="0" />';
            htmls += '<input type="text" name="menu['+group_id+'][progName]" class="input" value="" />';
            htmls += '<input type="hidden" name="menu['+group_id+'][sysGroupId]" value="'+group_id+'" />';
        htmls += '</td>';
        htmls += '<td><input type="text" name="menu['+group_id+'][funcName]" class="input" value="" /></td>';
        htmls += '<td><input type="text" name="menu['+group_id+'][sort]" class="input" style="width:30px;" maxlength="" value="" /></td>';
        htmls += '<td><a href="javascript:;" onclick="update_display($(this), '+group_id+')" ><img alt="显示" src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/tick_circle.png">显示</a>';
            htmls += '<input type="hidden" name="menu['+group_id+'][is_display]" value="1" />';
        htmls += '</td>';
        htmls += '<td>';
            htmls += '<a href="javascript:;" onclick="add_column($(this), '+group_id+')"><img src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/add.png" alt="Add"/>添加二级菜单</a>';
            htmls += '&nbsp;<a style="cursor:pointer;" href="javascript:;" onclick="play_show($(this), '+group_id+');"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/16/play.png" alt="展开菜单"/>展开二级菜单</a>';
            htmls += '&nbsp;<a href="javascript:;" onclick="del($(this))"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/cross.png" alt="删除" />删除</a>';
        htmls += '</td>';
    htmls += '</tr>';

    $("table tbody tr").last().after(htmls);
}

/**
 * 添加二级栏目
 */
function add_column(obj, group_id)
{
    var htmls = '';
    var num = $(".sub_"+group_id).length;

    num++;

    htmls += '<tr class="sub_'+group_id+'" menuid="'+group_id+'" style="background:#ccc;" >';
        htmls += '<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="text" name="menu['+group_id+'][sub]['+num+'][progName]" class="input" value="" /></td>';
        htmls += '<td><input type="text" name="menu['+group_id+'][sub]['+num+'][funcName]" class="input" value="" /></td>';
        htmls += '<td><input type="text" name="menu['+group_id+'][sub]['+num+'][sort]" class="input" style="width:30px;" maxlength="" value="" /></td>';
        htmls += '<td><a href="javascript:;" onclick="update_display($(this), '+group_id+')" ><img alt="显示" src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/tick_circle.png">显示</a>';
            htmls += '<input type="hidden" name="menu['+group_id+'][sub]['+num+'][is_display]" value="1" />';
        htmls += '</td>';
        htmls += '<td>';
            htmls += '<a href="javascript:;" onclick="$(this).parent().parent().remove()"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/cross.png" alt="删除" />删除</a>';
            htmls += '<input type="hidden" name="menu['+group_id+'][sub]['+num+'][systemId]" value="1" />';
            htmls += '<input type="hidden" name="menu['+group_id+'][sub]['+num+'][sysGroupId]" value="'+group_id+'" />';
        htmls += '</td>';
    htmls += '</tr>';

    if ($(".sub_"+group_id).length > 0) {
        $(".sub_"+group_id).show();
        $(".sub_"+group_id).last().after(htmls);
    } else {
        $(obj).parent().parent().after(htmls);
    }

    obj.parent().find("img").eq(1).attr("alt", "展开二级菜单");
    obj.parent().find("img").eq(1).attr("src", "<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/play_show.png");
}

/**
 * 保存数据
 */
function save_all()
{
    var err_msg = '';
    for(var i=0; i < $(".item").length; i++) {
        var group_id = $(".item").eq(i).find("input").val();
        if ($("input[name='menu["+group_id+"][progName]']").val() == '') {
            err_msg = "一级菜单数据填写不完整！\n\n 菜单名称为必填项！\n"
            $("input[name='menu["+group_id+"][progName]']").addClass("err_msg");
        } else {
            $("input[name='menu["+group_id+"][progName]']").removeClass("err_msg");
        }
    }

    if(err_msg) {
        $.BKD.msg(err_msg);
        return false;
    }

    if(!$.BKD.confirm('确认该操作并保存数据吗？')) {
        return false;
    }

    $.post('<?php echo HOME_DOMAIN ?>adm_programs/do_save', $('form').serialize(), function(ret) {
         if(ret.success == false) {
             $.BKD.msg(ret.message);
             return false;
         } else {
             $.BKD.msg(ret.message);
             $.BKD.refresh();
         }
    }, 'json');

}

</script>

<div id="main-content"> <!-- Main Content Section with everything -->

    <!-- Page Head -->
    <h2>菜单管理&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="submit" value="添加一级菜单" onclick="add_menu()"></h2>
    <div class="content-box"><!-- Start Content Box -->

        <div class="menu content-box-content">
           <form onsubmit="return false;">
            <table>
                <thead>
                    <tr>
                        <th width="15%">菜单名称</th>
                        <th width="10%">菜单路径</th>
                        <th width="8%">菜单排序</th>
                        <th width="10%">是否显示</th>
                        <th width="20%">操作</th>
                    </tr>
                </thead>

                <tbody>

                   <?php if(count($list)){ foreach($list as $item){ ?>

                     <tr class="item">
                        <td><input type="hidden" name="group_id[]" value="<?php echo $item['sysGroupId']; ?>" />
                            <span style='color:#FF0000;'>
                                <input type="text" name="menu[<?php echo $item['sysGroupId']; ?>][progName]" class="input" value="<?php echo $item['progName']; ?>" />
                                <input type="hidden" name="menu[<?php echo $item['sysGroupId']; ?>][id]" class="input" value="<?php echo $item['id']; ?>" />
                            </span>
                        </td>
                        <td><input type="text" name="menu[<?php echo $item['sysGroupId']; ?>][funcName]" class="input" value="<?php echo $item['funcName']; ?>" /></td>
                        <td><input type="text" name="menu[<?php echo $item['sysGroupId']; ?>][sort]" class="input" style="width:30px;" maxlength="2" value="<?php echo $item['sort']; ?>" /></td>

                        <td>
                           <a href="javascript:;" onclick="update_display($(this), <?php echo $item['sysGroupId']?>)" >
                               <?php if($item['is_display'] == 1){?>
                                    <img alt="显示" src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/tick_circle.png">显示
                                <?php }else{ ?>
                                    <img alt="隐藏" src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/cross_circle.png">隐藏
                                <?php } ?>
                            </a>
                            <input type="hidden" name="menu[<?php echo $item['sysGroupId']; ?>][is_display]" value="<?php echo $item['is_display']; ?>" />
                        </td>

                        <td>
                            <input type="hidden" value="<?php echo HOME_DOMAIN.'adm_programs/edit_program/?id='.$item['id'] ?>">
                            <a href="javascript:;" onclick="add_column($(this), <?php echo $item['sysGroupId']; ?>)"><img src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/16/add.png" alt="Add"/>添加二级菜单</a>

                            <a style="cursor:pointer;" href="javascript:;" onclick="play_show($(this), <?php echo $item['sysGroupId']; ?>);">
                                <img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/16/play.png" alt="展开菜单"/>展开二级菜单
                            </a>
                            <a href="javascript:;" onclick="del($(this));"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/cross.png" alt="删除" />删除</a></td>
                        </td>
                    </tr>

                    <?php if(is_array($item['sub'])) {  foreach($item['sub'] as $key=>$val) { ?>

                   <tr class="sub_<?php echo $item['sysGroupId']; ?>" menuid="<?php echo $item['sysGroupId']; ?>" style="display:none;background:#ccc;" >
                       <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="text" name="menu[<?php echo $val['sysGroupId']; ?>][sub][<?php echo $key; ?>][progName]" class="input" value="<?php echo $val['progName']; ?>" /></td>
                       <td><input type="text" name="menu[<?php echo $val['sysGroupId']; ?>][sub][<?php echo $key; ?>][funcName]" class="input" value="<?php echo $val['funcName']; ?>" /></td>
                       <td><input type="text" name="menu[<?php echo $val['sysGroupId']; ?>][sub][<?php echo $key; ?>][sort]" class="input" style="width:30px;" maxlength="2" value="<?php echo $val['sort']; ?>" /></td>

                        <td>
                           <a href="javascript:;" onclick="update_display($(this), <?php echo $item['sysGroupId']?>)" >
                               <?php if($val['is_display'] == 1){?>
                                    <img alt="显示" src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/tick_circle.png">显示
                                <?php }else{ ?>
                                    <img alt="隐藏" src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/cross_circle.png">隐藏
                                <?php } ?>
                            </a>
                            <input type="hidden" name="menu[<?php echo $val['sysGroupId']; ?>][sub][<?php echo $key; ?>][is_display]" value="<?php echo $val['is_display']; ?>" />
                        </td>

                        <td>
                           <input type="hidden" name="menu[<?php echo $val['sysGroupId']; ?>][sub][<?php echo $key; ?>][id]" value="<?php echo $val['id']; ?>" />
                            <a href="javascript:;" onclick="$(this).parent().parent().remove()"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/cross.png" alt="删除" />删除</a>
                        </td>
                   </tr>

                    <?php } } ?>

                <?php } } ?>

                </tbody>

                <tfoot>
                    <tr>
                       <td colspan="5">
                            <input type="button" name="save" class="submit" onclick="save_all();" value=" 保 存 " />
                        </td>
                    </tr>
                </tfoot>

            </table>
            </form>

        </div> <!-- End .content-box-content -->

    </div> <!-- End .content-box -->

</div><!-- End Main Content -->