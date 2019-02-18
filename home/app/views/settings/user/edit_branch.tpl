<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box">

        <div class="content-box-header">
            <h3>平台信息</h3>
        </div>
        <div class="content-box-content">
            <form>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <table class="list">
                    <colgroup>
                        <col width="28%"/>
                        <col width="80%"/>
                    </colgroup>
                    <thead>
                        <tr>
                           <th><input type="checkbox" class="check_all"> 全选</th>
                           <th>站点名称</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($list)){ foreach($list as $val){ ?>
                            <tr>
                                <td>
                                    <?php if(empty($val['exist'])){ ?>
                                        <input type="checkbox" name="check_all[]" value="<?php echo $val['branch_id'] ?>">
                                    <?php } else { ?>
                                        已绑定
                                    <?php } ?>
                                </td>
                                <td><?php echo $val['branch_name'] ?></td>
                            </tr>
                        <?php }} ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <input type="button" value="绑定" class="submit" name="add_submit"/> &nbsp;  &nbsp; 
                                <input type="button" onclick="window.parent.$.BKD.close_current();" value="关闭" class="submit">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>  
        </div><!-- End .content-box-content -->

    </div><!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">
<!--
$(document).ready(function(){

     $("input[name=add_submit]").click(function(){

        $('input[name=add_submit]').attr('disabled', true).removeClass('submit').val('正在添加中...');
        $.post('<?php echo HOME_DOMAIN; ?>user/editBranch', $('form').serialize(), function (response) {
            if (true === response.success) {
                alert("绑定成功!");
                parent.location.reload();
            } else {
                $.BKD.msg(response.message);
                $('input[name=add_submit]').attr('disabled', false).addClass('submit').val('确定添加');
            }
        } ,'JSON');
    })

})
-->
</script>