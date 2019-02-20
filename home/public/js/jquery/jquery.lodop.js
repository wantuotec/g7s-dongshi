/**
 * jquery.lodop.js
 * 
 * lodop jquery 打印插件
 * 
 * @author  : $Author$
 * @date    : $Date$
 * @version : $Id$
 * @rev     : $Revision$
 * @url     : $HeadURL$
 */
(function( $, undefined ) {
	$.Lodop             = null;
	var _id_object      = 'LODOP_OB';	//object id
	var _id_embed       = 'LODOP_EM';	//embed id
	var _version        = '6.1.4.5';	// 当前控件版本

	var _msg_install_32 = "打印控件未安装或版本过低!点击这里 <a href='http://cdn.test.com/uploads/install_lodop32_"+_version+".exe' target='_self'>下载安装包</a> ,安装后请刷新页面或重新进入。";
    var _msg_install_64 = "打印控件未安装或版本过低!点击这里 <a href='http://cdn.test.com/uploads/install_lodop64_"+_version+".exe' target='_self'>下载升级包</a> ,升级后请重新进入。";
    var _msg_firefox    = "注意：1：如曾安装过Lodop旧版附件npActiveXPLugin,请在【工具】->【附加组件】->【扩展】中先卸它。";
    
    var _user_browser = function()
    {
        var ua = navigator.userAgent.toUpperCase();
        
        if(ua.indexOf("MSIE")>0)	return 'IE';
        if(ua.indexOf("FIREFOX")>0)	return 'Firefox';
        if(ua.indexOf("SAFARI")>0)	return 'Safari';
        if(ua.indexOf("CHROME")>0)	return 'Chrome';
        if(ua.indexOf("OPERA")>0)	return 'Opera';
    };

    var _get = function()
	{
		var LODOP = 'IE' === _user_browser() ? document.getElementById(_id_object) : document.getElementById(_id_embed);

		if ( (LODOP == null) || (typeof(LODOP.VERSION) == "undefined")){
			if (navigator.userAgent.indexOf('Win64')>=0){
				_notice(_msg_install_64);
			}else{
				_notice(_msg_install_32);
			}
			if ('Firefox' == _user_browser()){
				_notice(_msg_firefox);
			}
			return false;
		}else if (LODOP.VERSION < _version) {
			if (navigator.userAgent.indexOf('Win64')>=0){
				_notice(_msg_install_64);
			}else{
				_notice(_msg_install_32);
			}
	    }
	    return LODOP;
	};

	var _notice = function(msg)
	{
		var msg = $('<div>')
					.attr('style','margin: 10px; clear:both; border-radius:6px 6px 6px 6px; background:url("/image/icons/cross_circle.png") no-repeat scroll 5px center #FFCECE; color: #665252;padding: 10px 10px 10px 25px')
					.html(msg);
		$('body').prepend(msg);
	};

	var _init = function()
	{
		if($('#'+_id_object).length > 0 && $('#'+_id_embed).length > 0) return true;

		var obj_html = [
	      '<object  id="'+_id_object+'" classid="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width=0 height=0>',
	        '<embed id="'+_id_embed+'" type="application/x-print-lodop" width=0 height=0></embed>',
	      '</object>'
		].join('');
		$('body').append(obj_html);

		$.Lodop = _get();
		// 初始化设置
		
	};

	// Lodop插件的扩展方法
	$.Lodop_ext = 
	{
		/**
	     * [生成一个可选打印机列表的下拉菜单]
	     * @param  [$target] jquery对象
	     * @return [bool]
	     */
		printer_list: function($target)
		{
			if($target.length<=0) return false;

			var html = '<select onclick="$.Lodop.SET_PRINTER_INDEX(this.value)">';
			for(i = 0; i < $.Lodop.GET_PRINTER_COUNT(); i++)
			{
				html += '<option value='+i+'>'+$.Lodop.GET_PRINTER_NAME(i+':DriverName')+'</option>';
			}
			html += '</select>';
			$target.append(html);
			return true;
		}
	}

	$(document).ready(function(){
		_init();
	});
})(jQuery);
