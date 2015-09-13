$(function() {
	//按钮变色
	$('label.btn').hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	});

	//input焦点
	$('.input,.textarea').focus(function(){
		$(this).addClass("focus")
	});
	$('.input,.textarea').blur(function(){
		$(this).removeClass("focus")
	});

	//tab选项卡
	$(".tabs ul li a").click(function () {
		$(".tabs ul li a.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabcontent").hide();
		var content_show = $(this).attr("rel");
		if(content_show){
			$("#"+content_show).show();
			window.parent.mainCo && window.parent.mainCo();
			return false;
		}

	});
	$('.tabs ul li a').bind('focus',function(){
		if(this.blur){this.blur();};
	});

	//列表隔行换色
	//$(".listForm table tbody tr:odd").addClass("color");
	//鼠标移动换色
	$(".listForm table tbody tr").hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	});

	//全选和选择
	$("input[rel='checkbox']").click( function (){
		$(".listForm input[@type=checkbox]:checked").parent().parent().addClass("selected");
		$(".listForm input[@type=checkbox]:not(:checked)").parent().parent().removeClass("selected");
	});

	var checkAll=function(){
		var allCheck=true;
		if(!$(this).attr('checked')){
			allCheck=false;
		}
		$("input[rel='checkbox']").attr("checked",allCheck);
		$("input[rel='checkbox']").parent().parent().addClass("selected");
		$(".listForm input[@type=checkbox]:not(:checked)").parent().parent().removeClass("selected");
   };
   //设置全选框的状态
   $("input[name*='checkAll']").click(checkAll);
   //页面载入时设置全先框的状态
   checkAll();

   //表单验证
	$("form.validate").validate({
	    ignoreTitle:true,
		errorElement: "span"
	 });

	//弹出层
	$(".dialog").live('click', function(){
		var url = $(this).attr("alt");
		var href = $(this).attr("href");
		var title = $(this).attr("title");
		if (typeof(href) != "undefined" && href.substring(0,10)!='javascript') { //href 链接 优先采用
			url = href;
		}
		if(typeof(url) != 'undefined') {
			var arg = parseUrl(url);
			width = parseInt(arg.width);
			height = parseInt(arg.height);
			JqueryDialog.Open1(title, url, width, height, false, false, true);
		}
	});

	//页面加载效果
	$(window).load(function(){
		setTimeout(function(){$("#loading_content").fadeOut("fast");},400);
	});
});

/*parser url*/
function parseUrl ( url ) {
   var query = url.replace(/^[^\?]+\??/,'');
   var Params = new Object ();
   if ( ! query ) return Params; // return empty object
   var Pairs = query.split(/[;&]/);
   for ( var i = 0; i < Pairs.length; i++ ) {
	  var KeyVal = Pairs[i].split('=');
	  if ( ! KeyVal || KeyVal.length != 2 ) continue;
	  var key = unescape( KeyVal[0] );
	  var val = unescape( KeyVal[1] );
	  val = val.replace(/\+/g, ' ');
	  Params[key] = val;
   }
   return Params;
}
function OpenWindow(url, winName, width, height) {
	xposition=0; yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 )) {
		xposition = (screen.width - width) / 2;
		yposition = (screen.height - height) / 2;
	}
	theproperty= "width=" + width + ","
	+ "height=" + height + ","
	+ "location=0,"
	+ "menubar=0,"
	+ "resizable=1,"
	+ "scrollbars=1,"
	+ "status=0,"
	+ "titlebar=0,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "screenx=" + xposition + "," //仅适用于Netscape
	+ "screeny=" + yposition + "," //仅适用于Netscape
	+ "left=" + xposition + "," //IE
	+ "top=" + yposition; //IE
	window.open(url, winName, theproperty);
}

function confirmurl(url,message) {
	if(confirm(message)) {
	    location.href = url;
	}
}