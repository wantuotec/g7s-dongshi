<div id="main-content"> <!-- Main Content Section with everything -->
    <form name="form" method="post">
        <div class="content-box">
            <div class="content-box-header">
                <h3>配置详情</h3>
            </div><!-- End .content-box-header -->
            <div class="content-box-content">
                <table>
                    <colgroup>
                        <col width="10%"/>
                        <col width="30%"/>
                        <col width="10%"/>
                        <col width="30%"/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <td>配置名称：</td>
                        <td>
                            <?php echo $configure['configure_name'];?>
                        </td>
                        <td>配置值：</td>
                        <td>
                            <?php echo $configure['configure_value'];?>
                        </td>
                    </tr>
                    <tr>
                        <td>类型：</td>
                        <td class="c-red">
                            <?php echo $configure['type_title'];?>
                        </td>
                    </tr>
                    <tr>
                        <td>描述：</td>
                        <td>
                            <?php echo $configure['description'];?>
                        </td>
                    </tr>
                    <tr>

                    </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="10" align="center">　
                                <?php if (1 == intval($search['nobar'])) {?>
                                <input type="button" class="submit" value="关闭" onclick="window.parent.$.BKD.close_current();" />
                                <?} else { ?>
                                <input type="button" name="return" class="submit" value="返回" onclick="javascript:history.back(-1);" />
                                <?php } ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- End .content-box-content -->
        </div><!-- End .content-box -->
    </form>
</div><!-- End Main Content -->