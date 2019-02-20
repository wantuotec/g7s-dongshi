<div id="main-content"> <!-- Main Content Section with everything -->
            
            <noscript> <!-- Show a notification if the user has disabled javascript -->
                <div class="notification error png_bg">
                    <div>
                        Javascript is disabled or is not supported by your browser. Please <a href="http://browsehappy.com/" title="Upgrade to a better browser">upgrade</a> your browser or <a href="http://www.google.com/support/bin/answer.py?answer=23852" title="Enable Javascript in your browser">enable</a> Javascript to navigate the interface properly.
                    </div>
                </div>
            </noscript>
            
            <!-- Page Head -->
            <h2>页面标题</h2>
            <p id="page-intro">副标题信息</p>
            <p id="breadcrumb">面包屑</p>
            <ul class="shortcut-buttons-set">
                
                <li><a class="shortcut-button" href="#"><span>
                    <img src="<?php echo HOME_DOMAIN ?>images/admin/icons/pencil_48.png" alt="icon"><br>
                    快捷方式1
                </span></a></li>
                
                <li><a class="shortcut-button" href="#"><span>
                    <img src="<?php echo HOME_DOMAIN ?>images/admin/icons/paper_content_pencil_48.png" alt="icon"><br>
                    快捷方式2
                </span></a></li>
                
                <li><a class="shortcut-button" href="#"><span>
                    <img src="<?php echo HOME_DOMAIN ?>images/admin/icons/image_add_48.png" alt="icon"><br>
                    快捷方式3
                </span></a></li>
                
                <li><a class="shortcut-button" href="#"><span>
                    <img src="<?php echo HOME_DOMAIN ?>images/admin/icons/clock_48.png" alt="icon"><br>
                    快捷方式4
                </span></a></li>
                
                <li><a class="shortcut-button" href="#messages" rel="modal"><span>
                    <img src="<?php echo HOME_DOMAIN ?>images/admin/icons/comment_48.png" alt="icon"><br>
                    快捷方式5
                </span></a></li>
                
            </ul><!-- End .shortcut-buttons-set -->
            
            <div class="clear"></div> <!-- End .clear -->
            
            <div class="content-box">        
            
                <div class="content-box-header">
                
                    <h3>搜索</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                        <form class="search-content">
                            <table>
                            <colgroup>
                                    <col width="10%"/>
                                    <col width="40%"/>
                                    <col width="10%"/>
                                    <col width="40%"/>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <td>选项：</td>
                                    
                                    <td>
                                        <select name="dropdown">
                                            <option value="option1"> 选项 1 </option>
                                            <option value="option2"> 选项 2 </option>
                                            <option value="option3"> 选项 3 </option>
                                                        <option value="option4"> 选项 4 </option>
                                        </select>
                                    </td>
    
                                    <td>选项：</td>   
                                    <td>
                                        <select name="dropdown">
                                            <option value="option1"> 选项 1 </option>
                                            <option value="option2"> 选项 2 </option>
                                            <option value="option3"> 选项 3 </option>
                                            <option value="option4"> 选项 4 </option>
                                        </select>
                                    </td>
                                </tr>  
                                <tr>
                                    <td>选项：</td>

                                    <td>
                                        <input name="checkbox1" type="checkbox"> 选项 1 <input name="checkbox2" type="checkbox"> 选项 2
                                    </td>
     
                                    <td>选项：</td>
                                    <td>
                                        <input name="radio1" type="radio"> 选项 1
                                        <input name="radio1" type="radio"> 选项 2 
                                        <input name="radio1" type="radio"> 选项 3 
                                        <input name="radio1" type="radio"> 选项 4 
                                    </td>
                                </tr>
                                <tr> 
                                       <td>短输入框：</td>
                                     <td colspan="3"><input class="text-input small-input" id="small-input" name="small-input" type="text"> <span class="input-notification inputSuccess png_bg">成功</span> <!-- Classes for input-notification: success, error, information, attention -->
                                     <br><small>简短描述</small></td>      
                                <tr>   
                                    <td>中输入框：</td>
                                     <td colspan="3">
                                        <input class="text-input medium-input" id="medium-input" name="medium-input" type="text"> <span class="input-notification inputError png_bg">错误</span>
                                     </td>
                                </tr>  
                                <tr>    
                                    <td>长输入框：</td>
                                    <td colspan="3"><input class="text-input large-input" id="large-input" name="large-input" type="text"></td>
                                </tr>
                                <tr>    
                                    <td>日期选择器：</td>
                                    <td><input class="text-input Wdate" name="start_time" type="text" autocomplete="off"></td>
                                    <td>时间选择器：</td>
                                    <td><input class="text-input Wdate" name="start_time" type="text" autocomplete="off" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})"></td>
                                </tr> 
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <input class="submit" value="搜索" type="submit"/>
                                            <input class="button" type="button" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN ?>auth/reset_password?nobar=1', '400px', '300px')" value="新建模态窗口【iframe】"/>
                                            <input class="button" type="button" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN ?>auth/reset_password?nobar=1', '400px', '300px', false)" value="新建非模态窗口【iframe】"/>
                                            <input class="button" type="button" onclick="$.BKD.open('div', '#popup', '400px')" value="新建模态窗口【层】"/>
                                            <input class="button" type="button" onclick="$.BKD.open('div', '#popup', '400px', 'auto', false)" value="新建非模态窗口【层】"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <input class="button" type="button" onclick="$.BKD.tips(this)" value=" tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我将在3秒后自动关闭', 3, 'top')" value=" 上 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我将在3秒后自动关闭', 3, 'right')" value=" 右 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我将在3秒后自动关闭', 3, 'bottom')" value=" 下 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我将在3秒后自动关闭', 3, 'left')" value=" 左 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '<div style=\'color:black;font-weight:bold;\'>嘻嘻，我是自定义的外观</div>', 3, 'top')" value=" 自定义 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，需要点击按钮才能关闭', true, 'top')" value=" 按钮关闭 tips "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我的长度为 150px', 3, 'top', 150)" value=" 设置 tips 长度 "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我设置了字体颜色', 3, 'top', null, 'blue')" value=" 设置 tips 字体色 "/>
                                            &nbsp;<input class="button" type="button" onclick="$.BKD.tips(this, '嘻嘻，我设置了背景颜色', 3, 'top', null, null, 'blue')" value=" 设置 tips 背景色 "/>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </form>  
                </div><!-- End .content-box-content -->        
                      
            </div><!-- End .content-box -->

            <div class="content-box" id="popup" style="display:none">
                测试测试<br />
                测试测试<br />
                测试测试<br />
                测试测试<br />
                测试测试<br />
                测试测试<br />
            </div>

            <div class="content-box">
                <div class="content-box-header">
                    <h3>Chrome、Firefox桌面通知</h3>
                </div><!-- End .content-box-header -->

                <div class="content-box-content">
                    <input class="button" type="button" onclick="$.BKD.notification('我是标题01', '我是内容01')" value="一般通知"/>
                    <input class="button" type="button" onclick="$.BKD.notification('我是标题02', '我是内容02', null, 'tag01', null)" value="指定tag"/>
                    <input class="button" type="button" onclick="$.BKD.notification('我是标题03', '我是内容03', null, null, 5)" value="5秒关闭"/>
                    <input class="button" type="button" onclick="$.BKD.notification('我是标题04', '我是内容04', 'http://www.baidu.com/img/bd_logo1.png', null, null)" value="指定图标"/>
                </div>
            </div>

            <div class="content-box">
                <div class="content-box-header">
                    <h3>扑克牌样式</h3>
                </div><!-- End .content-box-header -->

                <div class="content-box-content">
                    <div class="clearfix">
                        <div class="content-box-card">
                            <div class="title c-black">长时间未接单</div>
                            <div class="content c-black">50</div>
                        </div>
                        <div class="content-box-card">
                            <div class="title c-black">取件即将超时</div>
                            <div class="content c-red">65</div>
                        </div>
                        <div class="content-box-card">
                            <div class="title c-black">取件即将超时</div>
                            <div class="content c-blue">65</div>
                        </div>
                        <div class="content-box-card">
                            <div class="title c-red">送件即将超时</div>
                            <div class="content c-green">90</div>
                        </div>
                        <div class="content-box-card">
                            <div class="title c-red">已发订单</div>
                            <div class="content c-yellow">500</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-box"><!-- Start Content Box -->            
                
                <div class="content-box-content">        

                        <table>
                            
                            <thead>
                                <tr>
                                   <th><input class="check_all" type="checkbox"></th>
                                   <th>栏目 1</th>
                                   <th>栏目 2</th>
                                   <th>栏目 3</th>
                                   <th>栏目 4</th>
                                   <th>栏目 5</th>
                                </tr>
                                
                            </thead>
                         
                            <tbody>
                                <tr class="alt-row">
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr class="alt-row">
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr class="alt-row">
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr class="alt-row">
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><input type="checkbox" name="check_all[]" ></td>
                                    <td>文字</td>
                                    <td><a href="#" title="title">超链接</a></td>
                                    <td>文字</td>
                                    <td>文字</td>
                                    <td>
                                        <!-- base/icons -->
                                         <a href="#" title="Edit"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/pencil.png" alt="Edit"></a>
                                         <a href="#" title="Delete"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/cross.png" alt="Delete"></a>
                                         <a href="#" title="Edit Meta"><img src="<?php echo HOME_DOMAIN; ?>images/admin/icons/hammer_screwdriver.png" alt="Edit Meta"></a>
                                    </td>
                                </tr>
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <div class="align-left">
                                            <select name="dropdown">
                                                <option value="option1">请选择...</option>
                                                <option value="option2">选项1</option>
                                                <option value="option3">选项2</option>
                                            </select>
                                            <a class="submit" href="#">按钮</a>
                                        </div>
                                        
                                        <div class="pagination">
                                            <a href="#" title="First Page">? 第一页</a><a href="#" title="Previous Page">? 上一页</a>
                                            <a href="#" class="number" title="1">1</a>
                                            <a href="#" class="number" title="2">2</a>
                                            <a href="#" class="number current" title="3">3</a>
                                            <a href="#" class="number" title="4">4</a>
                                            <a href="#" title="Next Page">下一页 ?</a><a href="#" title="Last Page">最后一页 ?</a>
                                        </div> <!-- End .pagination -->
                                        <div class="clear"></div>
                                    </td>
                                </tr>
                            </tfoot>
                            
                        </table>
                    
                </div> <!-- End .content-box-content -->
                
            </div> <!-- End .content-box -->
            
            <div class="content-box"><!-- Start Content Box -->            
                <div class="content-box-header">
                
                    <h3>富文本编辑器</h3>
                
                </div>
                <div class="content-box-content">    
                    <textarea id="editor" name="content" style="width:100%;height:300px;"></textarea>
                </div>
            
            </div>

            <div class="content-box column-left">
                
                <div class="content-box-header">
                    
                    <h3>左右分栏</h3>
                    
                </div> <!-- End .content-box-header -->
                
                <div class="content-box-content">
                    
                    <div class="tab-content default-tab">
                    
                        <h4>标题</h4>
                        <p>
                        文字.
                        </p>
                        
                    </div> <!-- End #tab3 -->        
                    
                </div> <!-- End .content-box-content -->
                
            </div> <!-- End .content-box -->
            
            <div class="content-box column-right closed-box">
                
                <div class="content-box-header"> <!-- Add the class "closed" to the Content box header to have it closed by default -->
                    
                    <h3>左右分栏 (关闭)</h3>
                    
                </div> <!-- End .content-box-header -->
                
            </div> <!-- End .content-box -->
            <div class="clear"></div>
            
            
            <!-- Start Notifications -->
            
            <div class="notification attention png_bg">
                <a href="#" class="close"></a>
                <div>
                    提醒信息：
                </div>
            </div>
            
            <div class="notification information png_bg">
                <a href="#" class="close"></a>
                <div>
                    正常信息：
                </div>
            </div>
            
            <div class="notification success png_bg">
                <a href="#" class="close"></a>
                <div>
                    成功信息：
                </div>
            </div>
            
            <div class="notification error png_bg">
                <a href="#" class="close"></a>
                <div>
                    错误信息：
                </div>
            </div>

            <div class="content-box">
                <?php echo $chart; ?>
            </div>

</div><!-- End Main Content -->
<?php echo script_tag(array('kindeditor/kindeditor-min.js','kindeditor/lang/zh_CN.js')) ?>
<script type="text/javascript">
<!--
$(document).ready(function(){
    $.BKD.cloesNotice();
    
    KindEditor.ready(function(K) {
        var editor = K.create('#editor');
    });
})

-->
</script>