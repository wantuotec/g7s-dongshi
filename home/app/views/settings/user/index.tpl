<div id="main-content"> <!-- Main Content Section with everything -->
			
			<!-- Page Head -->

            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>查询</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="search_form" method="get" action="<?php echo HOME_DOMAIN ?>user/" class="search-content">
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
                                    <td>用户帐号：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['user'] ?>" class="text-input" name="user"/>
                                    </td>
                                    <td>真实姓名：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['userName'] ?>" class="text-input" name="userName"/>
                                    </td>
                                    <td>手机号码：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['phone_number'] ?>" class="text-input" name="phone_number"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>用户角色：</td>
                                    <td>
                                        <select id="groupId" name="groupId">
                                        <option value="" selected="selected">请选择...</option>
                                        <?php foreach($group as $item): ?>
                                            <option value="<?php echo $item['id'] ?>" <?php if($filter['groupId'] == $item['id']): ?>selected="selected"<?php endif; ?>>
                                            <?php echo $item['groupName'] ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>用户状态：</td>
                                    <td>
                                        <select name="status">
                                        <option value="" selected="selected">请选择...</option>
                                            <?php foreach($search_option['status'] as $key=>$value): ?>
                                                <option value="<?php echo $key ?>" <?php if($filter['status'] == $key): ?>selected="selected"<?php endif; ?>>
                                                <?php echo $value ?></option>
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
                                    <input id="addUser" type="button" value="新增用户" class="submit"/>
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
                                   <th>ID</th>
                                   <th>用户帐号</th>
								   <th>真实姓名</th>
                                   <th>用户角色</th>
                                   <th>用户状态</th>
                                   <th>操作</th>
								</tr>
								
							</thead>
						 
							<tbody>
                                <?php foreach($list as $item): ?>
								<tr>
                                   <td><?php echo $item['id'] ?></td>
                                   <td><?php echo $item['user'] ?></td>
								   <td><?php echo $item['userName'] ?></td>
                                   <td><?php echo $group[$item['groupId']]['groupName'] ?></td>
                                   <td><?php echo $search_option['status'][$item['status']] ?></td>
                                   <td>
                                   <a href="<?php echo HOME_DOMAIN ?>user/editUser/?id=<?php echo $item['id'] ?>"><img src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/pencil.png" alt="编辑"/>编辑</a>&nbsp;
                                   <a onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN ?>user/editPass/?id=<?php echo $item['id'] ?>&nobar=1', '500px', '400px')" alt="修改密码" href="#"><img src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/pencil.png" alt="修改密码"/>修改密码</a>&nbsp;
                                   <a href="<?php echo HOME_DOMAIN ?>user/priv/?id=<?php echo $item['id'] ?>"><img src="<?php echo HOME_DOMAIN ?>public/images/admin/icons/priv.png" alt="编辑"/>权限</a>
                                   </td>
								</tr>
                                <?php endforeach; ?>
							</tbody>
							
                            <tfoot>
								<tr>
                                <td colspan="9"><?php echo $pageination ?></td>
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
               $.getJSON("<?php echo HOME_DOMAIN ?>dept/ajaxGetDept/?companyId="+value, function(data){
                        if(data.status != 'error'){
                            $.each(data.items, function(i,item){
                                option += "<option value="+ item.id +">"+ item.deptName +"</option>";
                            });
                        }
                        $("#deptId").html(option);
               }); 
    	   });
    	   $('#addUser').click(function(){
    	   		window.location = '<?php echo HOME_DOMAIN ?>user/addUser';
    	   });
    	});
    -->
</script>
