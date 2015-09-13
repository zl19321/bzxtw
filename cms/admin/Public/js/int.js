$(function() {

	//连接下划线
	$('a').bind('focus',function(){
		if(this.blur){this.blur();};
	});

	//快捷操作
	$("#qMenu ul li").hover(function () {
		$(this).find("ul").slideDown(250);
	},function () {
		$(this).find("ul").slideUp(180);
	});

	/*左边的菜单项
    -----------------------------------------------*/
     $('#subNav li a').bind('click',function(){
       $('ul.active .current').removeClass('current');
       $(this).addClass('current');
     });

     $('.expand').live('click',function(){
       var self=$(this).removeClass('expand').addClass('fold');
       if(self.next().length){
         self.next().fadeOut('fast');
       }
     });

     $('.fold')
       .live('click',function(){
         var self=$(this).removeClass('fold').addClass('expand');
         if(self.next().length){
           self.next().fadeIn('fast');
           $('li:first a',self.next()).trigger('click');
         }})
       .next().css('display','none').end()
       .eq(0).trigger('click');


    /* 菜单切换
    -----------------------------------------------*/
    for(var i=0,len=$('#subNav >ul').length;i<len;++i){
      (function(i){
        $('#nav ul >li').eq(i).bind('click',function(){
          var self=$(this);
		  $('#po .text span').text(self.text());
          $('.active').removeClass('active');
          $(this).addClass('active');
          $('.menu_item').eq(i).addClass('active');
          return false;
        });
      })(i);
    }

	// 调整高宽度
	myWindowResize();
	$('#nav a').click(function(){
		myWindowResize();
	});

	$("#mainFrame").load(function(){
        mainCo();
    });

	// 后退、前进、刷新
	$('#goBack').click(function(){
		window.parent.frames["mainFrame"].history.back();
		return false;
	});

	$('#goNext').click(function(){
		window.parent.frames["mainFrame"].history.go(1);
		return false;
	});

	$('#refresh').click(function(){
		window.frames["mainFrame"].location.reload();
		return false;
	});

});

$(window).resize(function(){
	myWindowResize();
});



function myWindowResize(){
	var divwidth;
	var divheight;
	divheight = $(window).height() - 80;
	divwidth = $(window).width() - 205;
	$("#subNav").height(divheight);
	$("#mainCo").height(divheight - 55);
	$("#mainCo").css("width",divwidth);
	$("#mainFrame").css("width",divwidth-20);
	//$("#mainCo").css({width:divwidth,*width:divwidth-30});
}

function mainCo(){
	$("#mainFrame").height($("#mainCo").height());
	if($("#mainFrame").contents().find('body').height()>$("#mainCo").height())
	{
    	$("#mainFrame").height($("#mainFrame").contents().find('body').height()-10);
	}else{
		$("#mainFrame").height($("#mainCo").height()-2);
	}
}