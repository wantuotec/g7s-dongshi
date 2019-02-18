<div id="main-content"> <!-- Main Content Section with everything -->
			
			<!-- Page Head -->
			<h2>编辑角色</h2>
		
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>查询</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="search_form" method="post" action="<?php echo HOME_DOMAIN ?>adm_role/editRole" class="search-content" onSubmit="return check_submit()" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $filter['id'] ?>"/>
                            <input type="hidden" name="act" value="edit"/>
                            <table>
                            <colgroup>
                                    <col width="100px"/>
                                    <col width="80%"/>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <td>角色名称：</td>
                                    <td>
                                        <input type="text" value="<?php echo $filter['groupName'] ?>" class="text-input" name="groupName" id="groupName"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>状态：</td>
                                    <td>
                                        <select name="status">
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
                                <td colspan="4">
                                    <input class="submit" value="确定" type="submit"/>
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
        function check_submit(){
            if ($('#groupName').val() == '') {
                alert("请填写角色名称");
                $("#groupName").focus();
                return false;
            }
            else {
                return true;
            }
        }
    -->
</script>