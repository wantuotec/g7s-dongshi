<style type="text/css">
<!--
	li{display:inline;}
    .line{margin:5px 0}
    h2{border-bottom: 1px dotted #ccc;padding-bottom:5px;}
-->
</style>
<div id="main-content"> <!-- Main Content Section with everything -->
    <div class="content-box">	
        <div class="content-box-content">
        <?php if (is_array($menu) && !empty($menu)) { foreach($menu as $menu_item){ ?>
      			<div class="line"> <!-- Add the class "current" to current menu item -->
      					<h2><?php echo $menu_item['progName']; ?></h2>
        				<ul>
                        <?php foreach($menu_item['nav'] as $nav_item){
                            $menu_param = base64_encode(json_encode(
                                                            array('parent_name'   => $menu_item['progName'], 
                                                                  'child_name'    => $nav_item['progName'],
                                                                  'function_name' => $nav_item['funcName'],
                                                                 )
                                                            )
                                                        );
                        ?>
                            <li><a target="content" href="<?php echo HOME_DOMAIN . $nav_item['funcName']; ?><?php if (strpos($nav_item['funcName'], '?') !== false){echo '&';} else {echo '?';}?>menu_param=<?php echo $menu_param;?>"><?php echo $nav_item['progName']; ?></a></li>
                        <?php } ?>
        				</ul>
      			</div>
          <?php } } ?>
          </div>     
      </div>      
</div><!-- End Main Content -->
