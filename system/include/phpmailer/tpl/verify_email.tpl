<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>恭喜您，成功验证会员邮箱！</title>
<style>
body {font-family:"宋体"}
.fomat_head {
	width: 682px;
	_width: 682px;
	background-color: #383431;
	margin: 0px auto;
	_margin: 0px auto;
}
.fomat_word {
	width: 600px;
	_width: 600px;
	line-height: 24px;
	padding: 40px;
	font-size: 12px;
	height: auto;
	margin: 0px auto;
}

.h1 {font-size:13px;font-weight:bold;line-height:20px;}
.h2 {font-size:12px;line-height:20px;}
.h3 {font-size:15px;font-weight:bold;line-height:23px;}
.c-red {color:red;}
</style>
</head>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table border="1" cellspacing="0" bordercolor="#bebab7" cellpadding="0" width="682" align="center">
  <tr>
    <td><img src="http://cdn.test.com/uploads/email/20120811/111011-member-mail_01.gif" alt="" width="682" height="65" border="1" /></td>
  </tr>
  <tr>
    <td class="fomat_word">
		<span class="h1">亲爱的{{$username}}，您好!</span><br />
		
		<span class="h1">请点击以下链接验证您的邮箱：（基于安全考虑，本链接30分钟内有效）。</span><br />

		<a href="{{$reset_url}}" target="_blank">点此验证</a><br />
		或者直接复制以下链接粘贴至浏览器地址栏 <br />
		<strong><font color="#b40000">{{$reset_url}}</font></strong><br><br>
		<hr style="border-bottom: medium none; border-left: medium none; height: 0px; border-top: #939598 1px dashed; border-right: medium none">
        <br>
		
      	您可以在 <a target="_blank" href="http://user.test.com/order/view">会员中心-我的订单</a> 查看您的订单情况以及完成后续的操作。<br />
      	为确保我们的信息不被当做垃圾邮件处理，请把&nbsp;<a href="mailto:service@citychain.com.cn">service@citychain.com.cn</a>&nbsp;添加为您的联系人邮箱。 <br />
      	如有任何建议或疑问，请发送邮件至&nbsp;<a href="mailto:service@citychain.com.cn">service@citychain.com.cn</a>&nbsp;或者直接拨打 <strong>400-776-2727</strong> 咨询，<br />
    	我们将竭诚为您服务，祝您购物愉快。 
	</td>
  </tr>
</table>

</body>
</html>