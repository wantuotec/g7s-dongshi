var pwdmin   = 6;  //最低密码位数

$(function(){
	//==========切换登录和注册界面===============
	$('#switch_qlogin').click(function(){
		$('#switch_login').removeClass("switch_btn_focus").addClass('switch_btn');
		$('#switch_qlogin').removeClass("switch_btn").addClass('switch_btn_focus');
		$('#switch_bottom').animate({left:'0px',width:'70px'});
		$('#qlogin').css('display','none');
		$('#web_qr_login').css('display','block');
		
		});
	$('#switch_login').click(function(){
		
		$('#switch_login').removeClass("switch_btn").addClass('switch_btn_focus');
		$('#switch_qlogin').removeClass("switch_btn_focus").addClass('switch_btn');
		$('#switch_bottom').animate({left:'154px',width:'70px'});
		
		$('#qlogin').css('display','block');
		$('#web_qr_login').css('display','none');
		});
	//默认进入页面为登录页面
	if(getParam("a")=='0')
	{
		$('#switch_login').trigger('click');
	}

	// ==========监听注册按钮事件=================
	$('#reg').click(function() {
		if ($('#user').val() == "") {
			$('#user').focus()
			$('#userCue').html("<font color='#FF6A6A'><b>用户名不能为空！</b></font>");
			return false;
		}

		if ($('#user').val().length < 4 || $('#user').val().length > 16) {
			$('#user').focus()
			$('#userCue').html("<font color='#FF6A6A'><b>用户名位4-16字符！</b></font>");
			return false;
		}

		//去验证当前用户名是否已被注册
		$.ajax({
			type: 'post',
			url: "/member/ajaxyz.php",
			data: "uid=" + $("#user").val() + '&temp=' + new Date(),
			dataType: 'html',
			success: function(result) {

				if (result.length > 2) {
					$('#user').focus().css({
						border: "1px solid #2795dc",
						boxShadow: "0 0 2px #2795dc"
					});$("#userCue").html(result);
					return false;
				} else {
					$('#user').css({
						border: "1px solid #D7D7D7",
						boxShadow: "none"
					});
				}
			}
		});


		if ($('#passwd').val().length < pwdmin) {
			$('#passwd').focus();
			$('#userCue').html("<font color='#FF6A6A'><b>密码不能小于" + pwdmin + "位！</b></font>");
			return false;
		}
		if ($('#passwd2').val() != $('#passwd').val()) {
			$('#passwd2').focus();
			$('#userCue').html("<font color='#FF6A6A'><b>两次密码不一致！</b></font>");
			return false;
		}

		$('#regUser').submit();
	});

	// ==================监听登录按钮事件==================
	$("#to_login").bind("click", function(){
		alert('+++++++++++++');
		var A = window.open("oauth/index.php","TencentLogin", "width=800,height=550,menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");

		// $.HOME.open('iframe','<?php echo HOME_DOMAIN;?>member/auth?nobar=1','800px', '550px', 'true', '登录|注册')
	});
});
	
function logintab(){
	scrollTo(0);
	$('#switch_qlogin').removeClass("switch_btn_focus").addClass('switch_btn');
	$('#switch_login').removeClass("switch_btn").addClass('switch_btn_focus');
	$('#switch_bottom').animate({left:'154px',width:'96px'});
	$('#qlogin').css('display','none');
	$('#web_qr_login').css('display','block');
	
}

//根据参数名获得该参数 pname等于想要的参数名 
function getParam(pname) {
    var params = location.search.substr(1); // 获取参数 平且去掉？ 
    var ArrParam = params.split('&'); 
    if (ArrParam.length == 1) { 
        //只有一个参数的情况 
        return params.split('=')[1]; 
    } 
    else { 
         //多个参数参数的情况 
        for (var i = 0; i < ArrParam.length; i++) { 
            if (ArrParam[i].split('=')[0] == pname) { 
                return ArrParam[i].split('=')[1]; 
            } 
        } 
    } 
}