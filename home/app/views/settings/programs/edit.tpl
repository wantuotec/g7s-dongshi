<div id="main-content"> <!-- Main Content Section with everything -->
			
            <div class="content-box">
            
                <div class="content-box-header">
                
                    <h3>编辑菜单                <?php if(intval($filter['systemId']) == 0){ echo "<span style='color:#FF0000;'>【一级菜单】</span>";}else{echo '<span>【二级菜单】</span>';}?></h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="property_add" method="post" action="<?php echo HOME_DOMAIN ?>programs/edit_program?id=<?php echo$filter['id']?>" class="search-content" onSubmit="return check_submit()" enctype="multipart/form-data">
                            <input type="hidden" name="act" value="edit" />
                            <input type="hidden" name="id" value="<?php echo $filter['id'] ?>" />
                            <input type="hidden" name="sysGroupId" value="<?php echo $filter['sysGroupId'] ?>" />
                            <table>
                            <colgroup>
                                    <col width="10%"/>
                                    <col width="30%"/>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <td>菜单名称：</td>
                                    
                                    <td>
                                        <input type="text" name="progName" id="progName" class="text-input large-input" value="<?php echo $filter['progName'] ?>"/>
                                    </td>
                                 </tr>
                                <tr>
                                    <td>菜单路径：</td>
                                    
                                    <td>
                                        <input type="text" name="funcName" id="funcName" class="text-input large-input" value="<?php echo $filter['funcName'] ?>"/>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>排序：</td>
                                    
                                    <td>
                                        <input type="text" name="sort" id="sort" class="text-input large-input" value="<?php echo $filter['sort'] ?>"/>
                                    </td>
                                 </tr>
                                  <tr>
                                    <td>状态：</td>
                                    
                                    <td>
                                        <select name="is_display">
                                        <?php if($displays){foreach($displays as $key => $value){?>
                                            <option value="<?php echo $key ?>" <?php if($filter['is_display'] == $key){?>selected="selected"<?php }?>><?php echo $value ?></option>
                                        <?}}?>
                                        </select>
                                    </td>
                                 </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                <td colspan="4">
                                    <input class="submit" value="修改" type="submit"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="submit" value="返回" onclick="return_path('<?php echo HOME_DOMAIN ?>programs/index')"/>
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
        if ($('#progName').val() == '') {
            alert("请填写菜单名称");
            $("#progName").focus();
            return false;
        }
        else if ($('#funcName').val() == '') {
            alert("请填写方法入口");
            $("#funcName").focus();
            return false;
        }
        else if ($('#sort').val() == '') {
            alert("请填写排 序");
            $("#sort").focus();
            return false;
        }
        else {
            return true;
        }
    }
    function return_path($path)
    {
        $.BKD.redirect($path);
    }
-->
</script>