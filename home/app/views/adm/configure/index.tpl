<div id="main-content"> <!-- Main Content Section with everything -->
    <!-- Page Head -->
    <div class="content-box">       
        <div class="content-box-header">
            <h3>查询</h3>
        </div><!-- End .content-box-header -->

        <div class="content-box-content">
            <form name="search_form" method="get" class="search-content">
                <table>
                    <tbody>
                        <tr>
                            <td>类型：</td>
                            <td>
                                <select name="type">
                                    <option value="">全部</option>
                                    <?php foreach ($type as $key => $value) {?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>配置名称：</td>
                            <td>
                                <input type="text" name="configure_name" class="text-input" value="" />
                            </td>
                            <td>描述：</td>
                            <td>
                                <input type="text" name="description" class="text-input large-input" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="100%" align="center" colspan="8">
                                <input type="submit" class="submit" value="查 询"/>　　
                                <input type="button" onclick="$.BKD.redirect('<?php echo HOME_DOMAIN; ?>adm_configure/add');" value="添加配置" class="submit">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div><!-- End .content-box-content -->
    </div><!-- End .content-box -->
    <div class="content-box"><!-- Start Content Box -->         
        <div class="content-box-content">       
            <table class="list">
                <thead>
                    <tr>
                       <th>配置名称</th>
                       <th>配置数据</th>
                       <th>类型</th>
                       <th>描述</th>
                       <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($list as $val) { ?>
                        <tr>
                            <td>
                                <a onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN;?>adm_configure/detail?configure_id=<?php echo $val['configure_id'];?>&amp;nobar=1', '800px', '320px')" href="#"><?php echo $val['configure_name']; ?></a>
                            </td>
                            <td><?php echo $val['configure_value']; ?></td>
                            <td><?php echo $val['type_title']; ?></td>
                            <td><?php echo $val['description']; ?></td>
                            <td>
                                <a href="<?php echo HOME_DOMAIN; ?>adm_configure/detail?configure_id=<?php echo $val['configure_id']; ?>">查看</a>
                                &nbsp;&nbsp;<a href="<?php echo HOME_DOMAIN; ?>adm_configure/edit?configure_id=<?php echo $val['configure_id']; ?>">编辑</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5"><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->