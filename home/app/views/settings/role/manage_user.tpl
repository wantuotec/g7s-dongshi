<div id="main-content"> <!-- Main Content Section with everything -->
			
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>成员管理</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="commit" method="post" action="<?php echo HOME_DOMAIN ?>adm_role/moveUser" class="search-content" onSubmit="return check_submit()" enctype="multipart/form-data">
                            <table>
                           	<colgroup>
                         		<col width="100px"/>
                         	</colgroup>
                            <thead>
                                <tr>
                                    <th>选择</th>
                                    <th>账户名</th>
                                    <th>用户名</th>
								</tr>
                            </thead>
                            <tbody>
                                <?php foreach($list as $item): ?>
								<tr>
                                    <td><input type="checkbox" name="ids[]" value="<?php echo $item['id'] ?>"/></td>
                                    <td><?php echo $item['user'] ?></td>
                                    <td><?php echo $item['userName'] ?></td>
								</tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                            <td>移动到：</td>
                                <td>
                                        <select name="groupId">
                                        <?php foreach($auth_groups as $item): ?>
            				                <option value="<?php echo $item['id'] ?>">
                                            <?php echo $item['groupName'] ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                </td>
                                <td>
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
    function check_submit(){
        var num = 0;
        $("input[type=checkbox]").each(function(i) {
            if (this.checked == true)
            {
                num = 1;
            }
        })
        if (num == 0)
        {
            alert("请选择账户进行移动");
            return false;
        }
        if (confirm("你确定要把已经选择的人删除吗？"))
        {
            return true;
        }
        return false;
    }
-->
</script>