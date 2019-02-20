<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box">		
        <div class="content-box-header">
        <h3>添加一级菜单</h3>
        </div><!-- End .content-box-header -->
        <div class="content-box-content">
                <form name="property_add" method="post" action="<?php echo HOME_DOMAIN ?>programs/add_first_programs" class="search-content" onSubmit="return check_submit()" enctype="multipart/form-data">
                    <input type="hidden" name="act" value="add" />
                    <table>
                    <colgroup>
                            <col width="10%"/>
                            <col width="30%"/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <td>菜单名称：</td>
                            
                            <td>
                            <input type="text" value="" class="text-input large-input" name="progName" id="progName">
                            </td>
                         </tr>
                         <tr>
                            <td>方法入口：</td>
                            
                            <td>
                            <input type="text" value="" class="text-input large-input" name="funcName" id="funcName">
                            </td>
                         </tr>
                         <tr>
                            <td>排 序：</td>
                            
                            <td>
                            <input type="text" value="" class="text-input large-input" name="sort" id="sort">
                            </td>
                         </tr>
                         <tr>
                            <td>状 态：</td>
                            
                            <td>
                            <select name="is_display">
                                <?php if($displays){foreach($displays as $key => $value){?>
                                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
                                <?}}?>
                            </select>
                            </td>
                         </tr>
                         
                        </tbody>
                        <tfoot>
                        <tr>
                        <td colspan="4">
                            <input class="submit" value="添加" type="submit"/>
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
-->
</script>