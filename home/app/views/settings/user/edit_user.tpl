<div id="main-content"> <!-- Main Content Section with everything -->
			
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>编辑用户</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="form" method="post" action="<?php echo HOME_DOMAIN ?>adm_user/editUser" class="search-content" enctype="multipart/form-data" >
                            <input type="hidden" name="act" value="edit" />
                            <input type="hidden" name="id" value="<?php echo $filter['id'] ?>" />
                            <table>
                                <colgroup>
 		                             <col width="15%"/>
                                     <col width="35%"/>
 		                             <col width="15%"/>
                                     <col width="35%"/>
 	                          </colgroup>
                                <tbody>
                                 <tr>
                                     <td>用户帐号：</td>
                                     <td><?php echo $filter['user'] ?></td>
                                     <td>用户姓名：</td>
                                     <td><input type="text"  value="<?php echo $filter['userName'] ?>" class="text-input" name="userName" id="userName"/></td>
                                 </tr>
                                 <tr>
                                     <td>用户状态：</td>
                                     <td>
                                        <select name="status">
                                        <?php foreach($search_option['status'] as $key=>$value): ?>
            				                <option value="<?php echo $key ?>" <?php if($filter['status'] == $key): ?>selected="selected"<?php endif; ?>>
                                            <?php echo $value ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                     </td>
                                     <td>手机号：</td>
                                     <td><input type="text" value="<?php echo $filter['phone_number']  ?>" class="text-input" name="phone_number" id="phone_number" maxlength="11"/></td>
                                 </tr>
                                 <tr>
                                     <td>角色权限：</td>
                                     <td>
                                        <select name="groupId">
                                        <?php foreach($search_option['auth'] as $item): ?>
            				                <option value="<?php echo $item['id'] ?>" <?php if($filter['groupId'] == $item['id']): ?>selected="selected"<?php endif; ?>>
                                            <?php echo $item['groupName'] ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                     </td>
                                     <td>Email：</td>
                                     <td><input type="text" value="<?php echo $filter['email']  ?>" class="text-input large-input" name="email" id="email"/></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <input class="submit" value="确 定" type="submit"/>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </form>  
                </div><!-- End .content-box-content -->        
                
            </div><!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">

</script>