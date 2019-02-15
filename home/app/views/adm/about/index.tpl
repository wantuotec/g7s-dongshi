<div id="main-content">
    <div class="content-box">
        <div class="content-box-header">
            <h3>网站信息</h3>
        </div>

        <div class="content-box-content">
            <form class="search-content" method="post">
                <table>
                    <colgroup>
                        <col width="50%"/>
                        <col width="50%"/>
                    </colgroup>
                    <tbody>
                        <tr>
                            <td>网站域名：
                                <?php echo $list['domain_name'];?></td>
                            <td>创建时间：
                                <?php echo $list['website_create'];?></td>
                        </tr>
                        <tr>
                            <td>当前版本：
                                <?php echo $list['version'];?></td>
                            <td>源服务器：
                                <?php echo $list['servicer'];?></td>
                        </tr>
                        <tr style="border-bottom:1px solid #ccc">
                            <td colspan="2">服务程序：
                                <?php echo $list['program'];?></td>
                        </tr>

                        <tr>
                            <td>站长昵称：
                                <?php echo $list['nickname'];?></td>
                            <td>真实姓名：
                                <?php echo $list['real_name'];?></td>
                        </tr>
                        <tr>
                            <td>站长性别：
                                <?php echo $list['sex_title'];?></td>
                            <td>当前年龄：
                                <?php echo $list['age'];?></td>
                        </tr>
                        <tr>
                            <td>户籍地址：
                                <?php echo $list['register_address'];?></td>
                            <td>现居住地：
                                <?php echo $list['live_address'];?></td>
                        </tr>
                        <tr>
                            <td>当前职业：
                                <?php echo $list['job'];?></td>
                            <td>联系方式：
                                <?php echo $list['contact'];?></td>
                        </tr>
                        <tr>
                            <td>喜欢书籍：
                                <?php echo $list['like_books'];?></td>
                            <td>喜欢音乐：
                                <?php echo $list['like_musics'];?></td>
                        </tr>
                        <tr>
                            <td colspan="2">站长描述：</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border:2px solid #ededed;"><?php echo $list['master_describe'];?></td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <input class="submit" value="编 辑" onclick="$.BKD.redirect('<?php echo HOME_DOMAIN;?>adm_about/edit_website_info');" type="button">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>


