<div id="main-content"> <!-- Main Content Section with everything -->
			
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>新增用户</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="form" method="post" action="<?php echo HOME_DOMAIN ?>adm_user/addUser" class="search-content" onSubmit="return check_submit()" enctype="multipart/form-data">
                            <input type="hidden" name="act" value="add" />
                            <table>
                                <colgroup>
 		                             <col width="15%"/>
                                     <col width="20%"/>
 		                             <col width="15%"/>
                                     <col width="20%"/>
                                     <col width="15%"/>
                                     <col width="15%"/>
 	                          </colgroup>
                                <tbody>
                                 <tr>
                                     <td>用户帐号：</td>
                                     <td><input type="text" value="" class="text-input" name="user" id="user"/></td>
                                     <td>用户姓名：</td>
                                     <td><input type="text" value="" class="text-input" name="userName" id="userName"/></td>
                                     <td>用户密码：</td>
                                     <td><input type="text" value="" class="text-input" name="pass" id="pass"/></td>
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
                                     <td>角色权限：</td>
                                     <td colspan="3">
                                        <select name="groupId">
                                        <?php foreach($search_option['auth'] as $item): ?>
                                            <option value="<?php echo $item['id'] ?>">
                                            <?php echo $item['groupName'] ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                     </td>
                                 </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                <td colspan="4">
                                    <input class="submit" value="确定" type="submit"/>
                                </tr>
                                </tfoot>
                            </table>  
                        </form>  
                </div><!-- End .content-box-content -->        
                      
            </div><!-- End .content-box -->
			
</div><!-- End Main Content -->

<script type="text/javascript">
    function check_submit(){
        if ($('#user').val() == '') {
            alert("请填写用户帐号");
            $("#user").focus();
            return false;
        }
        else if ($('#userName').val() == '') {
            alert("请填写用户姓名");
            $("#userName").focus();
            return false;
        }
        else if ($('#pass').val() == '') {
            alert("请填写用户密码");
            $("#userPass").focus();
            return false;
        }
        else {
            return true;
        }
    }
</script>