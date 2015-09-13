
//http://wu-jian.cnblogs.com/
var m_window=$($(window.parent.document)[0].body);

var JqueryDialog = {

	//配置项
	//模态窗口背景色
	"cBackgroundColor"			:	"#ffffff",

	//边框尺寸(像素)
	"cBorderSize"				:	0,
	//边框颜色
	"cBorderColor"				:	"#999999",

	//Header背景色
	"cHeaderBackgroundColor"	:	"#316AC5",
	//右上角关闭显示文本
	"cCloseText"				:	"",
	//鼠标移上去时的提示文字
	"cCloseTitle"				:	"关闭",

	//提交按钮文本
	"cSubmitText"				:	"确 认",
	//取消按钮文本
	"cCancelText"				:	"取 消",

	//拖拽效果
	"cDragTime"					:	"100",


	Open:function(dialogTitle, iframeSrc, iframeWidth, iframeHeight){
		JqueryDialog.init(dialogTitle, iframeSrc, iframeWidth, iframeHeight, true, true, true);
	},

	Open1:function(dialogTitle, iframeSrc, iframeWidth, iframeHeight, isSubmitButton, isCancelButton, isDrag){
		JqueryDialog.init(dialogTitle, iframeSrc, iframeWidth, iframeHeight, isSubmitButton, isCancelButton, isDrag);
	},


    init:function(dialogTitle, iframeSrc, iframeWidth, iframeHeight, isSubmitButton, isCancelButton, isDrag){


        m_window.append("<div id='m_shadow'></div>");

        m_window.find('#m_shadow')
            .live('click',function(){
                JqueryDialog.Close();
            }).css({
                'position':'absolute',
                'top':0,
                'left':0,
                'opacity':.25,
                'background-color':'black',
                'width':m_window.width(),
                'height':m_window.height(),
                'z-index':100
            });

		//获取客户端页面宽高
		var _client_width = m_window.width();
		var _client_height =m_window.height();

        //create dialog
		if(typeof(m_window.find("#jd_dialog")[0]) != "undefined"){
			m_window.find("#jd_dialog").remove();
		}
        m_window.append("<div id='jd_dialog'></div>");

		//dialog location
		//left 边框*2 阴影5
		//top 边框*2 阴影5 header30 bottom50
		var _jd_dialog = m_window.find("#jd_dialog");

        var _left = (m_window.width()-iframeWidth)/2+m_window.scrollLeft();
		var _top=(m_window.height()-iframeHeight)/2+m_window.scrollTop();

        _jd_dialog.css({
            'top':_top,
            'left':_left
        });

		//create dialog shadow

        _jd_dialog.append("<div id='jd_dialog_s'>&nbsp;</div>");
		var _jd_dialog_s = m_window.find("#jd_dialog_s");

		//iframeWidth + double border
		_jd_dialog_s.css("width", iframeWidth + JqueryDialog.cBorderSize * 2 + "px");
		//iframeWidth + double border + header + bottom
		_jd_dialog_s.css("height", iframeHeight + JqueryDialog.cBorderSize * 2 + 55 + "px");

		//create dialog main
		_jd_dialog.append("<div id='jd_dialog_m'></div>");
		var _jd_dialog_m = m_window.find("#jd_dialog_m");
		_jd_dialog_m.css("border", JqueryDialog.cBorderColor + " " + JqueryDialog.cBorderSize + "px solid");
		_jd_dialog_m.css("width", iframeWidth + "px");
		_jd_dialog_m.css("background-color", JqueryDialog.cBackgroundColor);

		//header
		_jd_dialog_m.append("<div id='jd_dialog_m_h'></div>");
		var _jd_dialog_m_h = m_window.find("#jd_dialog_m_h");
		_jd_dialog_m_h.css("background-color", JqueryDialog.cHeaderBackgroundColor);

		//header left
		_jd_dialog_m_h.append("<span id='jd_dialog_m_h_l'>" + dialogTitle + "</span>");
		_jd_dialog_m_h.append("<span id='jd_dialog_m_h_r'><a href='javascript:;' title='" + JqueryDialog.cCloseTitle + "' onclick='JqueryDialog.Close();return false;'>" + JqueryDialog.cCloseText + "</a></span>");

		//body
		_jd_dialog_m.append("<div id='jd_dialog_m_b'></div>");
		var _jd_dialog_m_b = m_window.find("#jd_dialog_m_b");
		_jd_dialog_m_b.css("width", iframeWidth + "px");
		_jd_dialog_m_b.css("height", iframeHeight + "px");

		//iframe 遮罩层
		_jd_dialog_m_b.append("<div id='jd_dialog_m_b_1'>&nbsp;</div>");
		var _jd_dialog_m_b_1 = m_window.find("#jd_dialog_m_b_1");
		_jd_dialog_m_b_1.css("top", "30px");
		_jd_dialog_m_b_1.css("width", iframeWidth + "px");
		_jd_dialog_m_b_1.css("height", iframeHeight + "px");
		_jd_dialog_m_b_1.css("display", "none");

		//iframe 容器
		_jd_dialog_m_b.append("<div id='jd_dialog_m_b_2'></div>");
		//iframe
		m_window.find("#jd_dialog_m_b_2").append("<iframe id='jd_iframe' src='"+iframeSrc+"' scrolling='auto' frameborder='0' width='"+iframeWidth+"' height='"+iframeHeight+"' />");

		//bottom
		_jd_dialog_m.append("<div id='jd_dialog_m_t'></div>");
		var _jd_dialog_m_t = m_window.find("#jd_dialog_m_t");
		if(isSubmitButton){
			_jd_dialog_m_t.append("<label class='btn'><input id='jd_submit' value='"+JqueryDialog.cSubmitText+"' type='button' onclick='JqueryDialog.Ok();' class='submit'/></label>");
		}
		if(isCancelButton){
			_jd_dialog_m_t.append("<label class='btn'><input id='jd_cancel' value='"+JqueryDialog.cCancelText+"' type='button' onclick='JqueryDialog.Close();' class='submit'/></label>");
		}
		//register drag
		if(isDrag){
			DragAndDrop.Register(_jd_dialog[0], _jd_dialog_m_h[0]);
		}
	},

	/// <summary>关闭模态窗口</summary>
	Close:function(){
        m_window.find('#m_shadow').remove();
		m_window.find("#jd_dialog").remove();
		
		m_window.find('#mainFrame').contents().find('input:first').focus().blur();
	},

	/// <summary>提交</summary>
	/// <remark></remark>
	Ok:function(){
		var frm = $("#jd_iframe");
		if (frm[0].contentWindow.Ok()){
			JqueryDialog.Close() ;
		}
		else{
			frm[0].focus() ;
		}
	},


	SubmitCompleted:function(alertMsg, isCloseDialog, isRefreshPage){
		if($.trim(alertMsg).length > 0 ){
			alert(alertMsg);
		}
    	if(isCloseDialog){
			JqueryDialog.Close();
			if(isRefreshPage){
				window.location.href = window.location.href;
			}
		}
	}
};

var DragAndDrop = function(){

	//客户端当前屏幕尺寸(忽略滚动条)
	var _clientWidth;
	var _clientHeight;

	//拖拽控制区
	var _controlObj;
	//拖拽对象
	var _dragObj;
	//拖动状态
	var _flag = false;

	//拖拽对象的当前位置
	var _dragObjCurrentLocation;

	//鼠标最后位置
	var _mouseLastLocation;

	var getElementDocument = function(element){
		return element.ownerDocument || element.document;
	};

	//鼠标按下
	var dragMouseDownHandler = function(evt){

		if(_dragObj){

			evt = evt || window.parent.event;

			//获取客户端屏幕尺寸
			_clientWidth = m_window.width();
			_clientHeight =m_window.height();

			//iframe遮罩
			$("#jd_dialog_m_b_1").css("display", "");

			//标记
			_flag = true;

			//拖拽对象位置初始化
			_dragObjCurrentLocation = {
				x : $(_dragObj).offset().left,
				y : $(_dragObj).offset().top-($.browser.msie ? 0 : $(window).scrollTop())
			};

			//鼠标最后位置初始化
			_mouseLastLocation = {
				x : evt.screenX,
				y : evt.screenY
			};

			//注：mousemove与mouseup下件均针对document注册，以解决鼠标离开_controlObj时事件丢失问题
			//注册事件(鼠标移动)
		    m_window.bind("mousemove", dragMouseMoveHandler);
			//注册事件(鼠标松开)
			m_window.bind("mouseup", dragMouseUpHandler);

			//取消事件的默认动作
			if(evt.preventDefault)
				evt.preventDefault();
			else
				evt.returnValue = false;
		}
	};

	//鼠标移动
	var dragMouseMoveHandler = function(evt){
        if(_flag){

			evt = evt || window.parent.event;

			//当前鼠标的x,y座标
			var _mouseCurrentLocation = {
				x : evt.screenX,
				y : evt.screenY
			};

			//拖拽对象座标更新(变量)
			_dragObjCurrentLocation.x = _dragObjCurrentLocation.x + (_mouseCurrentLocation.x - _mouseLastLocation.x);
			_dragObjCurrentLocation.y = _dragObjCurrentLocation.y + (_mouseCurrentLocation.y - _mouseLastLocation.y);

			//将鼠标最后位置赋值为当前位置
			_mouseLastLocation = _mouseCurrentLocation;

			//拖拽对象座标更新(位置)
			$(_dragObj).css("left", _dragObjCurrentLocation.x + "px");
			$(_dragObj).css("top", _dragObjCurrentLocation.y + "px");

			//取消事件的默认动作
			if(evt.preventDefault)
				evt.preventDefault();
			else
				evt.returnValue = false;
		}

	};

	//鼠标松开
	var dragMouseUpHandler = function(evt){
		if(_flag){
			evt = evt || window.parent.event;

			//取消iframe遮罩
			$("#jd_dialog_m_b_1").css("display", "none");

			//注销鼠标事件(mousemove mouseup)
			cleanMouseHandlers();

			//标记
			_flag = false;
		}
	};

	//注销鼠标事件(mousemove mouseup)
	var cleanMouseHandlers = function(){
		if(_controlObj){
			$(_controlObj.document).unbind("mousemove");
			$(_controlObj.document).unbind("mouseup");
		}
	};

	return {
		//注册拖拽(参数为dom对象)
		Register : function(dragObj, controlObj){
			//赋值
			_dragObj = dragObj;
			_controlObj = controlObj;
			//注册事件(鼠标按下)
			$(_controlObj).bind("mousedown", dragMouseDownHandler);
		}
	}

}();


$(window.parent)[0].JqueryDialog=JqueryDialog;

$(document).ready(function(){

    $(window.parent).bind("keydown",function(e){
        if(e.which==27 || e.keyCode==27){
            JqueryDialog.Close();
        }
    });
});
