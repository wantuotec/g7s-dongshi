<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>评论内容</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <tbody>
                        <tr>
                            <td style="display:block;;min-height:50px;">
                                <?php echo $list['comment'];?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td align="center">
                                <input type="button" class="submit" value="关   闭" onclick="window.parent.$.BKD.close_current();">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>