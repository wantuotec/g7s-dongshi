    <div id="main-content"> <!-- Main Content Section with everything -->
    <!-- Page Head -->
    <div class="content-box">
        <div class="content-box-header">
            <h3>搜 索</h3>
        </div><!-- End .content-box-header -->

        <div class="content-box-content">
            <form method="get" class="search-content">
                <table>
                    <tbody>
                        <tr>
                            <td>文章标题：</td>
                            <td>
                                <input type="text" name="like_article_title" class="text-input">
                            </td>
                            <td>文章类型：</td>
                            <td>
                                <select name="article_category_id">
                                    <option value="">请选择...</option>
                                    <?php if (is_array($category_list) && !empty($category_list)):
                                        foreach ($category_list as $category):
                                    ?>
                                        <option value="<?php echo $category['article_category_id'];?>"><?php echo $category['category_name'];?></option>
                                    <?php endforeach;endif;?>
                                </select>
                            </td>
                            <td>是否有效：</td>
                            <td>
                                <select name="is_enabled">
                                    <option value="">请选择...</option>
                                    <option value="1">有效</option>
                                    <option value="2">无效</option>
                                </select>
                            </td>
                            <td>添加时间：</td>
                            <td>
                                <input type="text" name="ge_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　－　
                                <input type="text" name="le_create_time" class="text-input small_input Wdate"
                                       maxlength="12">　
                            </td>
                        </tr>
                        <tr>
                            <td>阅读排序：</td>
                            <td>
                                <select name="order_by_read_num">
                                    <option value="">请选择...</option>
                                    <option value="1">升 序 ↑</option>
                                    <option value="2">降 序 ↓</option>
                                </select>
                            </td>
                            <td>点赞排序：</td>
                            <td>
                                <select name="order_by_like_num">
                                    <option value="">请选择...</option>
                                    <option value="1">升 序 ↑</option>
                                    <option value="2">降 序 ↓</option>
                                </select>
                            </td>
                            <td>首页推荐：</td>
                            <td>
                                <select name="is_recommend">
                                    <option value="">请选择...</option>
                                    <option value="1">是</option>
                                    <option value="2">否</option>
                                </select>
                            </td>
                        </tr>
                        <tr style="border-top:1px solid #EAEAEA">
                            <td colspan="8" align="center">
                                <input type="button" class="submit" onclick="$.BKD.redirect('<?php echo HOME_DOMAIN;?>adm_article/add_article')" value="新增文章"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="reset" class="submit" value="清除搜索"/>&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" class="submit" name="select" value="搜   索"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div><!-- End .content-box-content -->
    </div><!-- End .content-box -->
    <form action="">
    <div class="content-box"><!-- Start Content Box -->
        <div class="content-box-content">
            <table class="list">
                <thead>
                    <tr>
                       <th>排序</th>
                       <th>封面图片</th>
                       <th>文章ID</th>
                       <th>文章标题</th>
                       <th>文章分类</th>
                       <th>文章来源</th>
                       <th>阅读数</th>
                       <th>点赞数</th>
                       <th>评论数</th>
                       <th>添加时间</th>
                       <th>首页推荐</th>
                       <th>是否有效</th>
                       <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($list) && is_array($list)) { foreach($list as $val) { ?>
                        <tr>
                            <input type="hidden" name="article_id[]" value="<?php echo $val['article_id']?>">
                            <td>
                                <input type="text" name="sort[]" class="text-input" value="<?php echo $val['sort']?>" style="width: 30px; height: 20px;">
                            </td>
                            <td class="img-box"><img src="<?php echo $val['cover_photo']; ?>" style="width:50px;height:50px;border:2px solid #fff;cursor:pointer;"></td>
                            <td><?php echo $val['article_id']; ?></td>
                            <td><?php echo $val['article_title']; ?></td>
                            <td><?php echo $val['category_name']; ?></td>
                            <td><?php echo $val['origin_type_title']; ?></td>
                            <td><?php echo $val['read_num']; ?></td>
                            <td><?php echo $val['like_num']; ?></td>
                            <td><a class="bold" href="<?php echo HOME_DOMAIN;?>adm_article_comment?article_id=<?php echo $val['article_id'];?>"><?php echo $val['comment_total']; ?></a>
                            </td>
                            <td><?php echo $val['create_time']; ?></td>
                            <td><?php if ($val['is_recommend'] == 1) { echo "<span class='c-green'>是</span>"; } else { echo "否"; }?></td>
                            <td><?php if ($val['is_enabled'] == 1) { echo "<span class='c-green'>有效</span>"; } else { echo "<span class='c-red'>无效</span>"; }?></td>
                            <td>
                                <a href="javascript:void(0)" data="<?php echo $val['article_id'].'|'.$val['is_enabled'];?>" onclick="set_enabled(this)"> <?php if ($val['is_enabled'] == 1) { echo '设为无效'; } else { echo '设为有效'; }?> </a> | 
                                <a href="javascript:void(0)" data="<?php echo $val['article_id'].'|'.$val['is_recommend'];?>" onclick="set_recommend(this)"> <?php if ($val['is_recommend'] == 2) { echo '设为推荐'; } else { echo '关闭推荐'; }?> </a> | 
                                <a href="<?php echo HOME_DOMAIN;?>adm_article/article_detail?article_id=<?php echo $val['article_id'];?>">查看</a> | 
                                <a href="<?php echo HOME_DOMAIN;?>adm_article/edit_article?article_id=<?php echo $val['article_id'];?>">编辑</a>
                            </td>
                        </tr>
                    <?php }} ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="12"><input class="submit" type="button" name="edit_sort" value="保存排序" onclick="save_sort()" /></td>
                        <td><?php echo $pagination; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div> <!-- End .content-box-content -->
    </div> <!-- End .content-box -->
    </form>
</div><!-- End Main Content -->

<script type="text/javascript">
// 设置有效性
function set_enabled(obj){
    // 获取原值
    var data  = $(obj).attr('data');
    var datas = data.split("|");
    if ('undefined' == typeof datas[0] || 'undefined' == typeof datas[1] || '' == datas[0] || '' == datas[1]) {
        $.BKD.msg('数据错误！');
        return false;
    }

    // 有效性值转换
    var notice = '';
    if (1 == datas[1]) {
        is_enabled = 2;
        notice     = '无效';
    } else {
        is_enabled = 1;
        notice     = '有效';
    }
    if (confirm('确定要将该篇文章设置为'+notice+'吗？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_article/set_enabled', "article_id=" + datas[0] + "&is_enabled=" + is_enabled, function (response) {
            if(true === response.success) {
                $.BKD.refresh();
            } else {
                layer.alert(response.message, {icon: 2});
            }
        },'JSON');
    }
}

// 设置首页推荐
function set_recommend(obj){
    // 获取原值
    var data  = $(obj).attr('data');
    var datas = data.split("|");
    if ('undefined' == typeof datas[0] || 'undefined' == typeof datas[1] || '' == datas[0] || '' == datas[1]) {
        $.BKD.msg('数据错误！');
        return false;
    }

    // 值转换
    var notice = '';
    if (1 == datas[1]) {
        is_recommend = 2;
        notice     = '关闭推荐';
    } else {
        is_recommend = 1;
        notice     = '推荐';
    }
    if (confirm('是否将该篇文章在首页设置为'+notice+'？')) {
        $.post('<?php echo HOME_DOMAIN; ?>adm_article/set_recommend', "article_id=" + datas[0] + "&is_recommend=" + is_recommend, function (response) {
            if(true === response.success) {
                layer.alert('已设置该文章在首页'+ notice +'成功', {icon: 1}, function(){
                    $.BKD.refresh();
                });
            } else {
                layer.alert(response.message, {icon: 2});
            }
        },'JSON');
    }
}

// 保存排序
function save_sort () {
    $('input[name=edit_sort]').attr('disabled', true).removeClass('submit').val('正在保存中...');
    $.post('<?php echo HOME_DOMAIN; ?>adm_article/edit_sort', $('form').serialize(), function (response) {
        if (true === response.success) {
            layer.alert('修改排序成功', {icon: 1}, function(){
                $.BKD.refresh();
            });
        } else {
            layer.alert(response.message, {icon: 2});
            $('input[name=edit_sort]').attr('disabled', false).addClass('submit').val('保存排序');
        }
    } ,'JSON');
}

//弹出图片相册
layer.photos({
  photos: '.img-box'
  ,anim: 1 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
});
</script>