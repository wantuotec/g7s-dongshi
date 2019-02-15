<div id="main-content"> <!-- Main Content Section with everything -->

            <!-- Page Head -->

            <div class="content-box">

                <div class="content-box-header">

                    <h3>权限管理</h3>

                </div><!-- End .content-box-header -->

                <div class="content-box-content">
                        <form name="search_form" method="post" action="<?php echo HOME_DOMAIN ?>adm_role/priv_act" class="search-content">
                            <input type="hidden" name="groupId" value="<?php echo $groupId ?>"/>
                            <table class="line">
                            <thead>
                                <tr>
                                    <th>模块</th>
                                    <th>功能</th>
                                    <th colspan="6">权限</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list)): ?>
                                <?php foreach($list as $item): ?>
                                    <?php if(is_array($item['nav'])): ?>
                                        <?php foreach($item['nav'] as $key=>$nav): ?>
                                        <tr>
                                            <?php if($key==0): ?>
                                                <td rowspan="<?php echo count($item['nav']) ?>"><?php echo $item['progName'] ?></td>
                                            <?php endif; ?>
                                                <td><input class="select_row align-right" type="checkbox" title="选中/反选"/><?php echo $nav['progName'] ?></td>
                                            <?php foreach($priv_option as $key=>$value): ?>
                                                <td>
                                                <input class="priv_opt" name="<?php echo $nav['systemId'] ?>-<?php echo $nav['sysGroupId'] ?>[]" value="<?php echo $key ?>" type="checkbox"<?php if($nav[$key] === 'allow'): ?>checked="checked"<?php endif; ?>/> <?php echo $value ?> 
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                                <tfoot>
                                <tr>
                                <td colspan="8">
                                    <a href="#" id="select_all">选中/反选</a>
                                    &nbsp;
                                    <input class="submit" value="确定" type="submit"/>                                    
                                    &nbsp;
                                    <input type="button" class="submit" onclick="history.back();" value="返回" />
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
           /* 选中一行 */
           $('.select_row').click(function(){
                var obj = $(this).parent('td').parent('tr').find('.priv_opt');
                if($(this).attr('checked')){
                    obj.attr('checked','checked');
                }else{
                    obj.removeAttr('checked');
                }
           })

           /* 全选 */
           $('#select_all').toggle(function(){
               $('input:checkbox').attr('checked','checked');
               return false;
           },function(){
               $('input:checkbox').removeAttr('checked');
               return false;
           })

           /* 二级联动菜单 */
           $('#companyId').change(function(){
               var value = $(this).val();
               var option = '<option value="-1" selected="selected">*</option>';
               $.getJSON("<?php echo HOME_DOMAIN ?>dept/ajaxGetDept/?companyId="+value, function(data){
                        $.each(data.items, function(i,item){
                            option += "<option value="+ item.id +">"+ item.deptName +"</option>";
                        });
                        $("#deptId").html(option);
               }); 
           })
        })
    -->
</script>