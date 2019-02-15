<div id="main-content"> <!-- Main Content Section with everything -->
            
            <div class="content-box">        
            
                <div class="content-box-header">
                
                    <h3>重设密码</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form name="property_add" method="post" action="/adm_auth/do_reset_password" class="search-content">
                            <table>
                                <colgroup>
                                    <col width="70px"/>
                                    <col width=""/>
                                </colgroup>

                                <tbody>
                                    <tr>
                                        <td>原 密 码：</td>
                                        <td>
                                            <input type="password" name="password" class="text-input large-input" value="" />
                                        </td>
                                    </tr>
    
                                    <tr>
                                        <td>新 密 码：</td>
                                        <td>
                                            <input type="password" value="" class="text-input large-input" name="password1" />
                                        </td>
                                     </tr>
    
                                     <tr>
                                        <td>重复密码：</td>
                                        <td>
                                            <input type="password" value="" class="text-input large-input" name="password2" />
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <input type="submit" class="submit" value=" 确定 "  />
                                            &nbsp;&nbsp;
                                            <input type="button" class="submit" onclick="window.parent.$.BKD.close_current()" value=" 关闭 " />
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </form>  
                </div><!-- End .content-box-content -->        
                      
            </div><!-- End .content-box -->

</div><!-- End Main Content -->