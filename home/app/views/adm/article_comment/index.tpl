<div id="main-content"> <!-- Main Content Section with everything -->
    <!-- Page Head -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>文章评论审核列表-用户</h3>
        </div><!-- End .content-box-header -->

        <div class="content-box-content">
            <form name="search_form" method="get" class="search-content">
                <table>
                    <tbody>
                        <tr>
                            <td>文章ID：
                                <input type="text" class="text-input" name="article_id">
                            </td>
                            <td>回复状态：
                                <select name="is_return">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($cfg['is_return']) && !empty($cfg['is_return'])):
                                        foreach ($cfg['is_return'] as $key => $val):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $val;?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>审核状态：
                                <select name="audit_status">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($cfg['audit_status']) && !empty($cfg['audit_status'])):
                                        foreach ($cfg['audit_status'] as $key => $val):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $val;?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>评论时间：
                                <input type="text" maxlength="12" class="text-input small_input Wdate" name="ge_create_time">&nbsp;－&nbsp;
                                <input type="text" maxlength="12" class="text-input small_input Wdate" name="le_create_time">
                            </td>
                        </tr>

                        <tr>
                            <td>评论类型：
                                <select name="type">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($cfg['type']) && !empty($cfg['type'])):
                                        foreach ($cfg['type'] as $key => $val):
                                    ?>
                                        <option value="<?php echo $key;?>"><?php echo $val;?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                        </tr>

                        <tr style="border-top:1px solid #EAEAEA">
                            <td colspan="4" align="center">
                                <input type="submit" class="submit" name="select" value="搜   索"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="reset" class="submit" value="清除搜索"/>
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
                       <th>评论ID</th>
                       <th>评论人</th>
                       <th>评论类型</th>
                       <th>回复类型</th>
                       <th>关联文章</th>
                       <th>评论内容</th>
                       <th>审核状态</th>
                       <th>评论时间</th>
                       <th>回复状态</th>
                       <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['article_comment_id']; ?></td>
                            <td><?php echo $val['customer_name'];?></td>
                            <td><?php echo $val['type_title']; ?></td>
                            <td><?php echo $val['return_type']; ?></td>
                            <td><a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN; ?>adm_article/article_detail?article_id=<?php echo $val['article_id']; ?>&nobar=1', '800px', '600px')">查看文章</a>
                            </td>
                            <td>
                                <a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN; ?>adm_article_comment/comment_detail?article_comment_id=<?php echo $val['article_comment_id']; ?>&nobar=1', '800px', '400px')">查看内容</a>
                            </td>
                            <td><?php echo $val['audit_status_title']; ?></td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td><?php if ($val['is_return'] == 1) { echo "<span class='c-green'>已回复</span>"; } else if ($val['is_return'] == 2) { echo "<span class='c-red'>未回复</span>"; }?>
                            </td>
                            <td>
                                <?php if ($val['type'] == 1) { ?>
                                <a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN; ?>adm_article_comment/return_comment?article_comment_id=<?php echo $val['article_comment_id']; ?>&nobar=1', '800px', '600px')">回 复</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="9"><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->