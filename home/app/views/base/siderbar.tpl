<div id="sidebar">
    <div id="fold-frame">
        <a class="fold" href="#" title="折叠导航栏">≡</a>
        <a class="open" style="display:none" href="#" title="打开导航栏">≡</a>
    </div>

    <div id="sidebar-wrapper" > <!-- Sidebar with logo and menu -->
        <h1 id="sidebar-title" class="png" style="font-weight: normal;">
            东航食品管控系统
            <!--<a target="_parent"  href="<?php echo HOME_DOMAIN ?>admin/"><img class="pngfix" src="<?php echo HOME_DOMAIN ?>images/admin/logo_bkd_title_blue.png"/></a>-->
        </h1>

        <!-- Sidebar Profile links -->
        <?php if (get_env_name()) { ?>
            <div style="padding-left:15px;line-height:20px;font-size:18px;color:#DBDBDB;"><?php echo get_env_name(); ?></div>
        <?php } ?>

        <div id="profile-links">您 好！  <?php echo $userName ?><br/>
            <!--<a href="<?php echo HOME_DOMAIN; ?>" target="_blank" >查看前台</a> | -->
            <!--<a target="_parent" href="<?php echo HOME_DOMAIN ?>auth/loginout" >登出</a>-->
        </div>

        <ul id="main-nav">  <!-- Accordion Menu -->
            <?php foreach($menu as $menu_item): ?>
            <?php if($menu_item['is_display']){?>
            <li> 
                <a href="#" class="nav-top-item"> <!-- Add the class "current" to current menu item -->
                    <span><?php echo $menu_item['progName'] ?></span>
                    <strong><?php echo count($menu_item['nav']) ?></strong>
                </a>

                <ul style="display: none;">
                <?php foreach($menu_item['nav'] as $nav_item): 
                    $menu_param = base64_encode(json_encode(
                                                    array('parent_name' => urlencode($menu_item['progName']), 
                                                          'child_name' => urlencode($nav_item['progName']),
                                                          'function_name' => $nav_item['funcName'],
                                                         )
                                                    )
                                                );
                ?>
                <?php if($nav_item['is_display']){?>
                    <li><a target="content" href="<?php echo HOME_DOMAIN . $nav_item['funcName'] ?><?php if (strpos($nav_item['funcName'], '?') !== false){echo '&';} else {echo '?';}?>menu_param=<?php echo $menu_param?>"><?php echo $nav_item['progName'] ?></a></li>
                <?php } ?>
                <?php endforeach; ?>
                </ul>
            </li>
            <?php } ?>
            <?php endforeach; ?>
        </ul> <!-- End #main-nav -->
    </div>
</div> <!-- End #sidebar -->

<script type="text/javascript">
<!--
    $(document).ready(function(){
       $.BKD.siderbar('main-nav');
       $.BKD.foldFrame('fold-frame','frameset','10px,*');
    })
-->
</script>
