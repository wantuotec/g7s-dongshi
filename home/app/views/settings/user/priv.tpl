<div id="main-content"> <!-- Main Content Section with everything -->
			
			<!-- Page Head -->
		
            <div class="content-box">		
            
                <div class="content-box-header">
                
                    <h3>权限管理</h3>
                
                </div><!-- End .content-box-header -->
                
                <div class="content-box-content">
                            <table class="line">
                            <thead>
                                <tr>
                                    <th>模块</th>
                                    <th>功能</th>
                                    <th colspan="6">权限</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(count($list)): ?>
                                <?php foreach($list as $item): ?>
                                    <?php if(is_array($item['nav'])): ?>
                                        <?php foreach($item['nav'] as $key=>$nav): ?>
                                        <tr>
                                            <?php if($key==0): ?>    
                                                <td rowspan="<?php echo count($item['nav']) ?>"><?php echo $item['progName'] ?></td>
                                            <?php endif; ?>    
                                                <td>                                                        
                                                    <?php echo $nav['progName'] ?>                         
                                                </td>
                                            <?php foreach($priv_option as $key=>$value): ?>
                                                <td>
                                                    <input disabled="disabled" class="priv_opt" value="<?php echo $key ?>" type="checkbox"<?php if($nav[$key] === 'allow'): ?>checked="checked"<?php endif; ?>/> <?php echo $value ?> 
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                         </tbody>
                            </table>  

                </div><!-- End .content-box-content -->        
                      
            </div><!-- End .content-box -->
			
</div><!-- End Main Content -->