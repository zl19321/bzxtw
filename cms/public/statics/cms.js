//cms必须的js文件
//JQuery对JSON的扩展
jQuery.extend({
  /**
   * @see  将json字符串转换为对象
   * @param   json字符串
   * @return 返回object,array,string等对象
   */
    evalJSON: function(strJson) {
        return eval("(" + strJson + ")");
    }
});

jQuery.extend({
  /**
   * @see  将javascript数据类型转换为json字符串
   * @param 待转换对象,支持object,array,string,function,number,boolean,regexp
   * @return 返回json字符串
   */
    toJSON: function(object) {
        var type = typeof object;
        if ('object' == type) {
            if (Array == object.constructor) type = 'array';
            else if (RegExp == object.constructor) type = 'regexp';
            else type = 'object';
        }
        switch (type) {
        case 'undefined':
        case 'unknown':
            return;
            break;
        case 'function':
        case 'boolean':
        case 'regexp':
            return object.toString();
            break;
        case 'number':
            return isFinite(object) ? object.toString() : 'null';
            break;
        case 'string':
            return '"' + object.replace(/(\\|\")/g, "\\$1").replace(/\n|\r|\t/g,
            function() {
                var a = arguments[0];
                return (a == '\n') ? '\\n': (a == '\r') ? '\\r': (a == '\t') ? '\\t': ""
            }) + '"';
            break;
        case 'object':
            if (object === null) return 'null';
            var results = [];
            for (var property in object) {
                var value = jQuery.toJSON(object[property]);
                if (value !== undefined) results.push(jQuery.toJSON(property) + ':' + value);
            }
            return '{' + results.join(',') + '}';
            break;
        case 'array':
            var results = [];
            for (var i = 0; i < object.length; i++) {
                var value = jQuery.toJSON(object[i]);
                if (value !== undefined) results.push(value);
            }
            return '[' + results.join(',') + ']';
            break;
        }
    }
});
//统计
function count(cid,type){
	$.get(__JSROOT__+"/count/",{cid:cid,type:type},function(data){$("#"+type+cid).html(data);});
}
function getCookie(name){
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while(i < clen)	{
		var j = i + alen;
		if(document.cookie.substring(i, j) == arg) return getCookieval(j);
		i = document.cookie.indexOf(" ", i) + 1;
		if(i == 0) break;
	}
	return null;
}
function setCookie(name, value, days){
	var argc = setCookie.arguments.length;
	var argv = setCookie.arguments;
	var secure = (argc > 5) ? argv[5] : false;
	var expire = new Date();
	if(days==null || days==0) days=1;
	expire.setTime(expire.getTime() + 3600000*24*days);
	document.cookie = name + "=" + escape(value) + ("; path=" + '/') + ";expires="+expire.toGMTString();
}
function delCookie(name){
    var exp = new Date();
	exp.setTime (exp.getTime() - 1);
	var cval = getCookie(name);
	document.cookie = name+"="+cval+";expires="+exp.toGMTString();
}
function getCookieval(offset){
	var endstr = document.cookie.indexOf (";", offset);
	if(endstr == -1)
	endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

function getProduct() {
    var str = getCookie('shopcart');
    var obj = [];
    if (str != null && str != '') obj = $.evalJSON(str);
    return obj;
}
function addProduct(id,name,price,url){
    var count = parseInt(prompt('请输入购买数量，只能输入正整数：','1'));
    if(count<=0)  return false;
    var obj = getProduct();
    for (var i=0;i<obj.length;i++)
        if (id==obj[i][0]){obj[i][4]=parseInt(obj[i][4])+count;break;}
    if (i==obj.length)
        obj.push([id,name,price,url,count]);
    var d = new Date();
    d.setYear(d.getYear()+1);
    setCookie('shopcart', $.toJSON(obj), d);
    return true;
}
function changeProduct(id,name,price) {
	var result=[];
	var obj = getProduct();
	for (var i=0;i<obj.length;i++)
        if (id!=obj[i][0]){result.push(obj[i]);}
	var d = new Date();
	d.setYear(d.getYear()+1);
}
function deleteProduct(id){
    var result=[];
    var obj = getProduct();
    for (var i=0;i<obj.length;i++)
        if (id!=obj[i][0]){result.push(obj[i]);}
    ShowCart(result);
    var d = new Date();
    d.setYear(d.getYear()+1);
    setCookie('shopcart', $.toJSON(result), d);
}
//o=存储物品信息的js object  o的每个元素为一个js数组 [id,name,price,url,count]
//value_id=存储物品信息的表单域ID
function ShowCart(o) {
    var str = '<table border="1"><tr><td>名称</td><td>单价</td><td>数量</td><td>&nbsp;</td></tr>';
    var sum = 0.0;
    var hidden = '';
    for(var i=0;i<o.length;i++) {
        if (i>0)  hidden+='|';  //每个商品之间用|区分信息
        hidden+=o[i][0]+','+o[i][1]+','+o[i][2]+','+o[i][3]+','+o[i][4];
        str += '<tr><td>'+o[i][1]+'</td><td>'+o[i][2]+'</td><td>'+o[i][4]+'</td><td><a href="javascript:deleteProduct(\''+o[i][0]+'\');void(0);">删除</a></td></tr>';
        sum += o[i][2]*o[i][4];
    }
    str += '<tr><td colspan="4" align="right">总价：￥'+ForDight(sum,2)+'元</td></tr></table>';
    str += '<input type="hidden" name="info[ordername]" value="'+hidden+'" />';
    return str;
}
function ForDight(Dight,How) {
	Dight = Math.round (Dight*Math.pow(10,How))/Math.pow(10,How);
	return Dight;
}

//添加页面到会员个人收藏夹中
function favorite(title,url,remark) {
	return false;
}

$().ready(function (){
	//添加都个人收藏夹
	$(".favorite").click(function () {
		return favorite();
	});
	//添加当前页面的物品到购物车
	$(".cart").click(function () {
		var addcart_info = $(this).attr("info");
		var info = addcart_info.split(",");
		if(!info[0] || !info[1] || !info[2]) {
			alert('添加失败！');
		} else {
			addProduct(info[0],info[1],info[2],location.href);
			alert('添加成功！');
		}
		return false;
	});


});