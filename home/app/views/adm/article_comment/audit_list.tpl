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
                            <td>文章ID：</td>
                            <td>
                                <input type="text" class="text-input" name="article_id">
                            </td>
                            <td>评论时间：</td>
                            <td>
                                <input type="text" maxlength="12" class="text-input small_input Wdate" name="ge_create_time">&nbsp;－&nbsp;
                                <input type="text" maxlength="12" class="text-input small_input Wdate" name="le_create_time">
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
                       <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <td><input type="checkbox" name="check_all[]" value="<?php echo $val['article_comment_id'];?>"> <?php echo $val['article_comment_id']; ?>
                            </td>
                            <td><?php echo $val['customer_name'];?></td>
                            <td><?php echo $val['type_title']; ?></td>
                            <td><?php echo $val['return_type']; ?></td>
                            <td><a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN; ?>adm_article/article_detail?article_id=<?php echo $val['article_id']; ?>&nobar=1', '800px', '600px')">查看文章</a>
                            </td>
                            <td>
                                <a href="#" onclick="$.BKD.open('iframe', '<?php echo HOME_DOMAIN; ?>adm_article_comment/comment_detail?article_comment_id=<?php echo $val['article_comment_id']; ?>&nobar=1', '800px', '400px')">查看内容</a>
                            </td>
                            <td><span class="c-red"><?php echo $val['audit_status_title']; ?></span></td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td>
                                <a href="#" onclick="audit(<?php echo $val['article_comment_id'];?>, 1)">通过</a> | 
                                <a href="#" onclick="audit(<?php echo $val['article_comment_id'];?>, 2)">不通过</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td><input type="checkbox" class="check_all"> 全选</td>
                        <td><input type='button' name='batch' value='批量审核通过' class='submit audit_order' onclick="service_batch_ok()"/></td>
                        <td colspan="7"><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
</div><!-- End Main Content -->

<script type="text/javascript">
// 单条审核操作
function audit (article_comment_id, audit_type) {
    if ('undefined' == typeof(article_comment_id)) {
        layer.alert('文章评论ID不存在', {icon: 2});
        return false;
    }
    if ('undefined' == typeof(audit_type)) {
        layer.alert('审核状态值不存在', {icon: 2});
        return false;
    }

    var audit_status = 3;  //默认是不通过的
    var audit_title  = '';
    if (1 == audit_type) {
        audit_status = 2;
        audit_title  = '通过';
    } else if (2 == audit_type) {
        audit_title  = '不通过';
    }

    layer.confirm('是否审核此文章评论'+audit_title+'？', {
      btn: ['确 认','算 了'] //按钮
    }, function(){
        $.post('<?php echo HOME_DOMAIN; ?>adm_article_comment/audit', 'article_comment_id='+article_comment_id+'&audit_status='+audit_status, function (response) {
            if (true === response.success) {
                layer.alert('审核'+audit_title+'操作成功', {icon: 1}, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2});
            }
        }, 'JSON');
    });
}

// 批量审核通过
function service_batch_ok() {
    var article_comment_ids = $.BKD.get_check_all();

    if ('' == article_comment_ids) {
        layer.alert('请选择要审核通过的文章评论', {icon: 7});
        return false;
    }

    layer.confirm('确定要把这些文章评论审核通过吗？', {
      btn: ['确 认','算 了'] //按钮
    }, function(){
        $('.audit_order').attr('disabled', true).removeClass('submit').val('审核中...');
        $.post('<?php echo HOME_DOMAIN; ?>adm_article_comment/batch_audit_ok', 'article_comment_ids=' + article_comment_ids, function (response) {
            if (true === response.success) {
                layer.alert('批量审核通过操作成功', {icon: 1}, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2});
                $('.audit_order').attr('disabled', false).addClass('submit').val('批量审核通过');
            }
        } ,'JSON');
    });
}
</script>