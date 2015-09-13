$(function() {
		   
	//顶部搜索框样式
	$(".topR button").hover(function(){
		$(this).addClass("over");
	},function(){
		$(this).removeClass("over");
	});
	$(".topR input").focus(function(){
		$(this).addClass("over")
	});	
	$(".topR input").blur(function(){
		$(this).removeClass("over")
	});		
	
	//Nav渐变效果
	initCaseList();
	
	//tab选项卡
	$("a.tab").hover(function () {
		$("a.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabcontent").hide();
		var content_show = $(this).attr("rel");
		$("#"+content_show).show();
		return false;
	});
	
	//列表隔行换色
	$(".downLoad .list li:odd").addClass("grayBg"); 
	$(".job .list li:odd").addClass("grayBg"); 
	
	//为视频加上图标
	$(".video .videoLeft .partB div ul li.pic").append("<span></span>"); 
	$(".videoList .list li").append("<span></span>"); 
	
	//留言按纽
	$(".guestBook .partB button").hover(function(){
		$(this).addClass("over");
	},function(){
		$(this).removeClass("over");
	});
	
	//表单焦点事件
	$('.guestBook .partB input,.guestBook .partB textarea').focus(function(){
		$(this).addClass("over")
	});		
	$('.guestBook .partB input,.guestBook .partB textarea').blur(function(){
		$(this).removeClass("over")
	});	
	
	$('.jobSubmit .input,.jobSubmit textarea').focus(function(){
		$(this).addClass("over")
	});		
	$('.jobSubmit .input,.jobSubmit textarea').blur(function(){
		$(this).removeClass("over")
	});	
	
	//图片欣赏图片切换	
	$('.photo .photoLeft .partA .bImg').cycle({
        fx:     'fade',
        speed:   500, 
        timeout: 3000,
        pager:  '.photo .photoLeft .partA .sImg',
        pagerAnchorBuilder: function(idx, slide) {
            return '.photo .photoLeft .partA .sImg li:eq(' + (idx) + ')';
        }
    });	
	
	//视频欣赏图片切换	
	$('.video .videoLeft .partA .bImg').cycle({
        fx:     'fade',
        speed:   500, 
        timeout: 3000,
        pager:  '.video .videoLeft .partA .sImg',
        pagerAnchorBuilder: function(idx, slide) {
            return '.video .videoLeft .partA .sImg li:eq(' + (idx) + ')';
        }
    });	
	
	//图片欣赏内页图片切换
    var s=$('.photoView .sImg').find('.list li:first'),
        slide=$('.photoView .sImg').find('.list'),
        childLen=slide.find('li').length,
        w=parseInt(s.css('margin-left'))+parseInt(s.css('margin-right'))+s.width();

    slide.width(w*(childLen+1));

    var imageList=(function(){
        var _list=[],_idx=0,_len=0,_mid=2;

        $('.photoView .sImg a').each(function(){
            var self=$(this);
            self.bind('click',function(){
                $('.photoView .bImg img')
                    .fadeTo('fast',0.01,function(){
                      $(this).attr('src',self.attr('href'));
                    }).fadeTo('fast',1);

                $('.photoView .sImg li').removeClass("selected");
		        self.parent().addClass("selected");

                _idx=$('.photoView .sImg a').index(this);

                if(_idx==0 || _idx==1){
                    slide.animate({'left':0},'slow');
                    _mid=2;
                }else if( _idx==_len-1 || _idx==_len-2){
                    slide.animate({'left':520-(childLen)*108+20+'px'},'slow');
                    _mid=childLen-3;
                }else if(_mid!=_idx){
                    slide.animate({
                        'left':'-='+(_idx-_mid)*108+'px'
                    },'slow');

                    _mid=_idx;
                }

                return false;
             });

             _list.push(self);
        });

        _len=_list.length;

        return {
            curr : _list[_idx],
            next : function(){
               return _idx < _len-1 ? _list[++_idx] : _list[_idx=0];
            },
            prev : function(){
               return _idx > 0 ? _list[--_idx] : _list[_idx=_len-1];
            }
        };
    }());

    $('.arrowRight,.photoView .bImg img').bind('click',function(){
       (imageList.next()).trigger('click');
       return false;
    });

    $('.arrowLeft').bind('click',function(){
       (imageList.prev()).trigger('click');
       return false;
    });
	
	//form validateion
    $(".guestBook .partB").submit(function(){
        var passed=false;

        $(this).find('input,textarea').not('.submit').each(function(){
            var self=$(this),val=self.val();

            if($.trim(val)==""){
              self.focus();
              return (passed=false);
            }
            if(self.is('#f_tel') && !/(\d+|\d$){6}/.test(val)){
                self.focus().next().text("请输入正确的电话号码");
                return (passed=false);
            }else{
                self.next().text("*");
            }
            if(self.is('#f_email') && !/^\w+@\w+\.([.\w]+)$/.test(val)){
                self.focus().next().text("email格式有误 (例如 abc@fangfa.net)");
                return (passed=false);
            }else{
                self.next().text("*");
            }

            passed=true;
        });

        return passed ?true :false;
    });

    (function(){
        var config={
            delay:2000,
            speed:300,
            offset:155
        };

        var cloneUl=$("#index .indexLeft .partD ul").clone()
          .insertAfter($("#index .indexLeft .partD ul"))
          .animate({'left':'-=10px'},20);

        var width=cloneUl.width(),timer;
        $("#index .indexLeft .partD .wplist")
            .width(width*2+20)
            .hover(function(){
               if(timer) clearInterval(timer);
             },function(){
               timer=setInterval(sliding,config.delay);
             }).trigger('mouseout');

        function sliding(){
           $("#index .indexLeft .partD ul").each(function(){
               var self=$(this);

               if(self.position().left+width<100){
                   self.css('left',cloneUl.position().left+width);
               }

               self.animate({'left':'-='+config.offset+'px'},config.speed);
           });
         };
    })();
});

function toggleLight(){
    if (!this.cover) {
        this.cover = $(this.lightTag, this).css({opacity:0});
		this.speed1=this.speed1||400;
		this.speed2=this.speed2||200;
		if($.browser.msie && $.browser.version=="6.0")
		{
			this.speed1=this.speed2=1;
		}
    }
    if (this.onLight) {
        this.cover.stop(true).fadeTo(this.speed1, 0);
        this.onLight = false;
    }
    else {
        this.cover.stop(true).fadeTo(this.speed2, 1);
        this.onLight = true;
    }
}

function initCaseList()
{	
	var item=$("#nav ul li");
	item.each(function(){
		this.lightTag="div";
		var that=$(this);
		var div=$(document.createElement("div"));
		div.html(that.html());
		that.append(div).hover(toggleLight,toggleLight);
	});
}