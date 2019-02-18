<div id="main-content"> <!-- Main Content Section with everything -->

            <!-- Page Head -->

            <div class="content-box">

                <div class="content-box-header">

                    <h3>查询</h3>

                </div><!-- End .content-box-header -->

                <div class="content-box-content">
                        <form name="search_form" method="get" action="<?php echo HOME_DOMAIN; ?>role/" class="search-content">
                            <table>
                            <colgroup>
                                    <col width="10%"/>
                                    <col width="20%"/>
                                    <col width="10%"/>
                                    <col width="20%"/>
                                    <col width="10%"/>
                                    <col width="20%"/>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <td>角色编号：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['id']; ?>" class="text-input" name="id"/>
                                    </td>

                                    </td>
                                    <td>角色名称：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['groupName']; ?>" class="text-input" name="groupName"/>
                                    </td>
                                    <td>角色状态：</td>
                                    <td>
                                        <select name="status">
                                        <option value="" selected="selected">请选择...</option>
                                            <?php foreach($search_option['status'] as $key=>$value): ?>
                                                <option value="<?php echo $key; ?>" <?php if($filter['status'] == $key): ?>selected="selected"<?php endif; ?>>
                                                <?php echo $value; ?></option>
                                             <?php endforeach; ?>
                                         </select>
                                    </td>
                                </tr>

                                </tbody>
                                <tfoot>
                                <tr>
                                <td colspan="6" align="center">
                                    <input class="submit" value="查询" type="submit"/>
                                    &nbsp;
                                    <input id="addRole" type="button" value="新增角色" class="submit"/>
                                </td>
                                </tr>
                                </tfoot>
                            </table>
                        </form>
                </div><!-- End .content-box-content -->

            </div><!-- End .content-box -->

            <div class="content-box"><!-- Start Content Box -->

                <div class="content-box-content">

                        <table class="list">
                            <thead>
                                <tr>
                                   <th>编号</th>
                                   <th>角色名称</th>
                                   <th>成员数量</th>
                                   <th>角色状态</th>
                                   <th>操作</th>
                                </tr>

                            </thead>

                            <tbody>
                                <?php foreach($list as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['groupName']; ?></td>
                                    <td><?php echo $item['user_count']; ?></td>
                                    <td><?php echo $search_option['status'][$item['status']]; ?></td>
                                    <td>
                                        <a href="<?php echo HOME_DOMAIN; ?>role/editRole/?id=<?php echo $item['id']; ?>"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/pencil.png" alt="编辑"/>编辑</a>
                                        <a href="<?php echo HOME_DOMAIN; ?>role/manageUser/?id=<?php echo $item['id']; ?>"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/user.png" alt="编辑"/>成员管理</a>
                                        <a href="<?php echo HOME_DOMAIN; ?>role/priv/?id=<?php echo $item['id']; ?>"><img src="<?php echo HOME_DOMAIN; ?>public/images/admin/icons/priv.png" alt="编辑"/>权限管理</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                <td colspan="8"><?php echo $pageination; ?></td>
                                </tr>
                            </tfoot>

                        </table>

                </div> <!-- End .content-box-content -->

            </div> <!-- End .content-box -->

</div><!-- End Main Content -->

<script type="text/javascript">
    <!--
        $(document).ready(function(){

           /* 二级联动菜单 */
           $('#companyId').change(function(){
               var value = $(this).val();
               var option = '<option value="-1" selected="selected">*</option>';
               $.getJSON("<?php echo HOME_DOMAIN; ?>dept/ajaxGetDept/?companyId="+value, function(data){
                        $.each(data.items, function(i,item){
                            option += "<option value="+ item.id +">"+ item.deptName +"</option>";
                        });
                        $("#deptId").html(option);
               });
           });
        });
           $('#addRole').click(function(){
                window.location = '<?php echo HOME_DOMAIN; ?>role/addRole';
           });
    -->
</script>