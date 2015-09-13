<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Html.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-11
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 生成html元素
// +----------------------------------------------------------------------


class Html {

	/**
	 * 生成一个下拉列表框
	 *
	 * @param string $name
	 * @param array $arr
	 * @param mixed $selected
	 * @param string $extra
	 */
	public static function select($name, $arr, $selected = null, $extra = null) {
		$html = '';
		$html .= "<select name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" {$extra} >\n";
		foreach ( $arr as $v ) {
			$html .= '<option value="' . htmlspecialchars ( $v ['value'] ) . '"';
			if ($selected == $v ['value']) {
				$html .= ' selected';
			}
			$html .= '>' . htmlspecialchars ( $v ['title'] ) . "</option>\n";
		}
		$html .= "</select>\n";
		return $html;
	}

	/**
	 * 多选列表
	 * @param unknown_type $name
	 * @param unknown_type $arr
	 * @param unknown_type $selected
	 * @param unknown_type $extra
	 */
	public static function multiple($name, $arr, $selected = array(), $extra = null) {
		$html = '';
		$html .= "<select name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" {$extra} >\n";
		foreach ( $arr as $v ) {
			$html .= '<option value="' . htmlspecialchars ( $v ['value'] ) . '"';
			if (in_array ( $v ['value'], $selected )) {
				$html .= ' selected';
			}
			$html .= '>' . htmlspecialchars ( $v ['title'] ) . "&nbsp;&nbsp;</option>\n";
		}
		$html .= "</select>\n";
		return $html;
	}

	/**
	 * 生成一组单选框
	 *
	 * @param string $name
	 * @param array $arr
	 * @param mixed $checked
	 * @param string $separator
	 * @param string $extra
	 */
	public static function radio_group($name, $arr, $checked = null, $extra = null) {
		$ix = 0;
		$html = '';
		foreach ( $arr as $v ) {
			$value_h = htmlspecialchars ( $v ['value'] );
			$title = nl2br ( str_replace ( ' ', '&nbsp;', htmlspecialchars ( $v ['title'] ) ) );
			$html .= "<input name=\"{$name}\" type=\"radio\" id=\"".str_replace(array('[',']'),array('_',''),$name)."_{$ix}\" value=\"{$value_h}\" ";
			if ($value_h == $checked) {
				$html .= " checked ";
			}
			$html .= " {$extra} />";
			$html .= "<label for=\"".str_replace(array('[',']'),array('_',''),$name)."_{$ix}\">{$title}</label>";
			$ix ++;
			$html .= "\n";
		}
		return $html;
	}

	/**
	*
	*生成一张地图编辑框
	*
	* $ak 百度地图的ak
	* $tyle 百度地图的类型
	* $width 地图的高度
	* $height 地图的高度
	* $field 字段的名称
	* $value 自定义的字段的值
	* $level 地图的等级
	**/
	public function map($ak,$type,$width,$height,$field,$value,$level){
		$arr=array();
		$arr=explode(',',$value);
		$lng=trim($arr[0]);//经度
		$lat=trim($arr[1]);//纬度
		$html = '';
		$html .= "<div id='allmap' style='width:".trim($width)."px;height:".trim($height)."px'></div>";
		$html .= "<script type='text/javascript' src='http://api.map.baidu.com/api?v=2.0&ak=".trim($ak)."'></script>";
		$html .= "<script type='text/javascript'>
		var map = new BMap.Map('allmap');";
		//进入添加页面以城市名来确定地图的中心点
		if($lng){
			$html.="var po = new BMap.Point(".$lng.",".$lat.");
			var status=1;
			var marker = new BMap.Marker(new BMap.Point(".$lng.",".$lat."));
			map.centerAndZoom(po,".$level.");
			map.addOverlay(marker);
			marker.enableDragging();  
			marker.addEventListener('dragend',function(e){
			document.getElementById('point').value=e.point.lng+',"."'+e.point.lat;});
			map.addEventListener('rightclick',function(e){
			if(status==0){
			var marker = new BMap.Marker(new BMap.Point(e.point.lng,e.point.lat));
			document.getElementById('point').value=e.point.lng+','+e.point.lat;
			map.addOverlay(marker);
			status=1;
			marker.enableDragging();  
			marker.addEventListener('dragend',function(e){
			document.getElementById('point').value=e.point.lng+',"."'+e.point.lat;});
			}
			});
			";
		}
		else{
		$html.="
		var status=0;
		var myCity = new BMap.LocalCity();
		myCity.get(myLocation);
		map.addEventListener('rightclick',function(e){
		if(status ==0){
		var marker = new BMap.Marker(new BMap.Point(e.point.lng,e.point.lat));
		document.getElementById('point').value=e.point.lng+','+e.point.lat;
		map.addOverlay(marker);
		status=1;
		}
		marker.enableDragging();  
		marker.addEventListener('dragend',function(e){
		document.getElementById('point').value=e.point.lng+',"."'+e.point.lat;
		});
		});";}
		$html.="control();
		function control(){
		map.addControl(new BMap.NavigationControl());
		map.enableScrollWheelZoom();//滑轮滚动缩放地图
		map.disableDoubleClickZoom();//禁止双击放大地图
		}
		function myLocation(result){
		var cityName = result.name;
		map.centerAndZoom(cityName,".$level.");}
		function delete_marker(){
		map.clearOverlays();
		document.getElementById('point').value='';
		status=0;
		}
		//搜索本地地址
		function localSearch(){
		var options = {
		onSearchComplete:function(results){   
		if (local.getStatus() == BMAP_STATUS_SUCCESS){   
		//以搜索出来的第一个结果作为地图的中心点
		map.centerAndZoom(new BMap.Point(results.getPoi(0).point.lng,results.getPoi(0).point.lat),".$level.");}}};
		var local = new BMap.LocalSearch(map,options);
		local.search(document.getElementById('address').value);
		}
		</script>";
		$html .="坐标:<input name='info[".$field."]' id='point' type='text' value='".$value."' style='width:150px;'>&nbsp;&nbsp;";
		$html .="<input type='button' value='清除标注' onclick='delete_marker()' >";
		$html .="&nbsp;&nbsp;&nbsp;城市名：<input type='text' style='width:270px;' id='address' name='address' value=''>";
		$html .= "&nbsp;&nbsp;&nbsp;<input type='button' value='搜索' style='width:60px;' onclick='localSearch()'>";
		return $html;
	}

	/**
	 * 生成一组多选框
	 *
	 * @param string $name
	 * @param array $arr
	 * @param array $selected
	 * @param string $separator
	 * @param string $extra
	 */
	public static function checkbox_group($name, $arr, $selected = array(), $extra = null) {
		$ix = 0;
		if (! is_array ( $selected )) {
			$selected = array (
				$selected
			);
		}
		$html = '';
		foreach ( $arr as $v ) {
			$value_h = htmlspecialchars ( $v ['value'] );
			$title = nl2br ( str_replace ( ' ', '&nbsp;', htmlspecialchars ( $v ['title'] ) ) );
			$html .= "<input name=\"{$name}[]\" type=\"checkbox\" id=\"".str_replace(array('[',']'),array('_',''),$name)."_{$ix}\" value=\"{$value_h}\" ";
			if (in_array ( $v ['value'], $selected )) {
				$html .= "checked=\"checked\"";
			}
			$html .= " {$extra} />";
			$html .= "<label for=\"".str_replace(array('[',']'),array('_',''),$name)."_{$ix}\">{$title}</label>";
			$ix ++;
			$html .= "\n";
		}
		return $html;
	}

	/**
	 * 生成一个复选框
	 *
	 * @param string $name
	 * @param int $value
	 * @param boolean $checked
	 * @param string $label
	 * @param string $extra
	 */
	public static function checkbox($name, $value = 1, $checked = false, $label = '', $extra = null) {
		$html = '';
		$html .= "<input name=\"{$name}\" type=\"checkbox\" id=\"".str_replace(array('[',']'),array('_',''),$name)."_1\" value=\"{$value}\"";
		if ($checked) {
			$html .= " checked";
		}
		$html .= " {$extra} />\n";
		if ($label) {
			$html .= "<label for=\"".str_replace(array('[',']'),array('_',''),$name)."_1\">" . htmlspecialchars ( $label ) . "</label>\n";
		}
		return $html;
	}

	/**
	 * 生成一个文本输入框
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $width
	 * @param int $maxLength
	 * @param string $extra
	 */
	public static function input($name, $value = '', $extra = null, $type='text') {
		$html = '';
		$html .= "<input name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" type=\"{$type}\" value=\"" . htmlspecialchars ( $value ) . "\" ";
		$html .= " {$extra} />\n";
		return $html;
	}

	/**
	 * 生成一个密码输入框
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $width
	 * @param int $maxLength
	 * @param string $extra
	 */
	public static function password($name, $value = '', $width = null, $maxLength = null, $extra = null) {
		$html = '';
		$html .= "<input name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" type=\"password\" value=\"" . htmlspecialchars ( $value ) . "\" ";
		if ($width) {
			$html .= "size=\"{$width}\" ";
		}
		if ($maxLength) {
			$html .= "maxlength=\"{$maxLength}\" ";
		}
		$html .= " {$extra} />\n";
		return $html;
	}

	/**
	 * 生成一个多行文本输入框
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $width
	 * @param int $height
	 * @param string $extra
	 */
	public static function textarea($name, $value = '', $width = null, $height = null, $extra = null) {
		$html = '';
		$html .= "<textarea name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" ";
		if ($width) {
			$html .= " cols=\"{$width}\" ";
		}
		if ($height) {
			$html .= " rows=\"{$height}\" ";
		}
		$html .= " {$extra} >";
		$html .= htmlspecialchars ( $value );
		$html .= "</textarea>\n";
		return $html;
	}

	/**
	 * 生成一个隐藏域
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $extra
	 */
	public static function hidden($name, $value = '', $extra = null) {
		$html = '';
		$html .= "<input name=\"{$name}\" type=\"hidden\" value=\"";
		$html .= htmlspecialchars ( $value );
		$html .= "\" {$extra} />\n";
		return $html;
	}

	/**
	 * 生成一个文件上传域
	 *
	 * @param string $name
	 * @param int $width
	 * @param string $extra
	 */
	public static function file($name, $width = null, $extra = null) {
		$html = '';
		$html .= "<input name=\"{$name}\" id=\"".str_replace(array('[',']'),array('_',''),$name)."\" type=\"file\"";
		if ($width) {
			$html .= " size=\"{$width}\"";
		}
		$html .= " {$extra} />\n";
		return $html;
	}


	/**
	 * 生成 form 标记
	 *
	 * @param string $name
	 * @param string $action
	 * @param string $method
	 * @param string $onsubmit
	 * @param string $extra
	 */
	public static function form_start($name, $action, $method = 'post', $onsubmit = '', $extra = null) {
		$html = '';
		$html .= "<form name=\"{$name}\" action=\"{$action}\" method=\"{$method}\" id=\"{$name}\" ";
		if ($onsubmit) {
			$html .= "onsubmit=\"{$onsubmit}\"";
		}
		$html .= " {$extra} >\n";
		return $html;
	}

	/**
	 * 日期与时间
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param string $format 日期格式，具体参见http://www.my97.net/dp/demo/index.htm
	 */
	public static function datetime($name, $value = '', $format = 'yyyy-MM-dd', $extra = null) {
		$html = '';
		if (empty ( $GLOBALS['loadFiles']['WdatePicker'] )) {
			$loadFiles = '<script language="javascript" src="' . _PUBLIC_ . 'js/calendar/WdatePicker.js"></script>'."\n";
			$html .= $loadFiles;
			$GLOBALS['loadFiles']['WdatePicker'] = $loadFiles;
		}
		$html .= '';
		$html .= '<input value="' . $value . '" '.$extra.' class="Wdate" type="text" name="' . $name . '" onFocus="WdatePicker({isShowClear:false,dateFmt:\'' . $format . '\'})"/>';
		return $html;
	}

	/**
	 * 关闭 form 标记
	 */
	public static function form_end() {
		$html = '';
		$html .= "</form>\n";
		return $html;
	}

	/**
	 * 调用编辑器
	 *
	 * @param string $name  textarea的name值
	 * @param string $value  textarea的值
	 * @param string $type  所选择使用的编辑器名称
	 * @param array $option  编辑器属性数组（包括：width、height、toolbar）
	 * @param string $extra  textarea的扩展属性及其值
	 * @return 编辑器相关的HTML
	 */
	public static function editor($name = 'info[content]', $value = '', $type = 'tiny_mce', $option = array(), $extra = '') {
		$html = '';
		$width = $option['width'];
		$height = $option['height'];
		if ($type == 'tiny_mce') {
			if (empty ( $GLOBALS['loadFiles']['tiny_mce'] )) {
				$loadFiles = '<script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/editor/tiny_mce/tiny_mce.js"></script>
							  <script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/editor/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php"></script>'."\n";
				$html .= $loadFiles;
				$GLOBALS['loadFiles']['tiny_mce'] = $loadFiles;
			}
			$uid = 'content_'.$name;
			$uid = str_replace('[','_',$uid);
			$uid = str_replace(']','',$uid);
			//创建保存内容的textrea
			$html .= '<textarea id="'.$uid.'" name="'.$name.'" '.$extra.'>'.htmlspecialchars($value).'</textarea>';
			$html .= '<script language="javascript">';
			$init_width = $width ? "width : {$width}," : '';
			$init_height = $height ? "height : {$height}," : '';
			if ($option ['toolbar'] == 'basic') {//基础型
				$html .= '
				tinyMCE.init({
					// General options
					mode : "exact",
					elements : "'.$uid.'",
					language : "zh",
					theme : "advanced",
					skin : "o2k7",
					skin_variant : "silver",
					verify_html : false,
					relative_urls : false,
					remove_script_host : true,
					file_browser_callback : "tinyBrowser",
					'.$init_width.'
					'.$init_height.'
					// Theme options
					theme_advanced_buttons1 : "forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true
				});
				';
			}

			if ($option ['toolbar'] == 'advanced') {//标准型
				$html .= '
				tinyMCE.init({
					// General options
					mode : "exact",
					elements : "'.$uid.'",
					language : "zh",
					theme : "advanced",
					skin : "o2k7",
					skin_variant : "silver",
					verify_html : false,
					relative_urls : false,
					remove_script_host : true,
					file_browser_callback : "tinyBrowser",
					'.$init_width.'
					'.$init_height.'
					plugins : "safari,pagebreak,style,advhr,advimage,advlink,emotions,insertdatetime,media,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,inlinepopups",
					// Theme options
					// Theme options
					theme_advanced_buttons1 : "undo,redo,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,|,removeformat,emotions,image,media,cleanup,pagebreak,|,fullscreen,code",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true
				});
				';
			}

			if ($option ['toolbar'] == 'full') {//完整型
				$html .= '
				tinyMCE.init({
					// General options
					mode : "exact",
					elements : "'.$uid.'",
					language : "zh",
					theme : "advanced",
					skin : "o2k7",
					skin_variant : "silver",
					verify_html : false,
					relative_urls : false,
					remove_script_host : true,
					file_browser_callback : "tinyBrowser",
					plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups",
					'.$init_width.'
					'.$init_height.'
					// Theme options
					theme_advanced_buttons1 : "undo,redo,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,|,tablecontrols",
					theme_advanced_buttons3 : "cite,abbr,acronym,del,ins,|,hr,removeformat,charmap,emotions,image,media,advhr,|,template,pagebreak,cleanup,|,fullscreen,code",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true
				});
				';
			}
            
            $html .= '</script>';
		}
		if ($type == 'kindeditor') {
		    if (empty ( $GLOBALS['loadFiles']['kindeditor'] )) {
				$loadFiles = '<script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/editor/kindeditor/kindeditor.js"></script>'."\n";
				$html .= $loadFiles;
				$GLOBALS['loadFiles']['kindeditor'] = $loadFiles;
			}
			$uid = 'content_'.$name;
			$uid = str_replace('[','_',$uid);
			$uid = str_replace(']','',$uid);
			//创建保存内容的textrea
			$html .= '<textarea id="'.$uid.'" name="'.$name.'" '.$extra.' style="width:'.$width.'px;height:'.$height.'px;visibility:hidden;">'.htmlspecialchars($value).'</textarea>';
			$html .= '
			<script type="text/javascript">
            KE.show({
            	id : \''.$uid.'\',
            	allowFileManager : true,
            	urlType : \'domain\'
            });
            </script>
			';
		}
        
        if($type == 'ueditor'){
            if (empty ( $GLOBALS['loadFiles']['ueditor'] )) {
                $loadFiles = '<script type="text/javascript" charset="utf-8" src="'.WEB_PUBLIC_PATH. '/editor/ueditor/ueditor.config.js"></script>
				<script type="text/javascript" charset="utf-8" src="'.WEB_PUBLIC_PATH. '/editor/ueditor/ueditor.all.min.js"></script> 
				<link rel="stylesheet" href="'.WEB_PUBLIC_PATH. '/editor/ueditor/lang/zh-cn/zh-cn.js"/>';    
				//$loadFiles = '<script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/editor/kindeditor/kindeditor.js"></script>'."\n";
				$html .= $loadFiles;
				$GLOBALS['loadFiles']['ueditor'] = $loadFiles;
			}
            $uid = 'content_'.$name;
			$uid = str_replace('[','_',$uid);
			$uid = str_replace(']','',$uid);
            
            $html .= '<div style="width:'.$option['width'].'px;"><textarea id="'.$uid.'" name="'.$name.'" style="width:'.$option['width'].'px;" >'.htmlspecialchars($value).'&nbsp;</textarea></div>';
            $html .= '<script type="text/javascript">

var editor = UE.getEditor("'.$uid.'");
editor.ready(function(){
    editor.setHeight("200");
})
</script>';
        }
        
		
		return $html;
	}

	/**
	 * 生成一个jqueryify文件上传框
	 */
	public static function ifyUpload($name, $value = '', $isImage = false, $extra = null) {
		$html = '';
		if (empty($GLOBALS['loadFiles']['thickbox'])) {
			$loadFiles = '<script type="text/javascript" src="'._PUBLIC_.'js/thickbox/thickbox-compressed.js"></script>
						  <link rel="stylesheet" type="text/css" href="'._PUBLIC_.'js/thickbox/css.css" />'."\n";
			$html .= $loadFiles;
			$GLOBALS['loadFiles']['thickbox'] = $loadFiles;
		}
		$uid = md5(uniqid());
		$html .= '
		<label><input type="text" name="'.$name.'" value="'.$value.'" id="tb_input'.$uid.'" '.$extra.' /></label>
		<label><input id="test" type="button" class="thickbox" alt="'.U('fupload/ifyupload?opener_id=tb_input'.$uid.'&TB_iframe=true&height=200&width=450').'" title="上传图片" id="upload_img_'.$uid.'" value="上传文件" /></label>
		';
		if ($isImage) {
			$html .= '<label><input type="button" class="thickbox" alt="'.U('ffiles/images?opener_id=tb_input'.$uid.'&TB_iframe=true&height=300&width=500').'" title="图片库" id="choose_img_'.$uid.'" value="站内选择" /></label>';
		}
		return $html;
	}

	/**
	 * 设置模板
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param unknown_type $extra
	 */
	public static function template($name, $value = '', $extra = null) {
		$html = '';
//		if (empty($GLOBALS['loadFiles']['dialog'])) {
//			$loadFiles = '<script type="text/javascript" src="'._PUBLIC_.'js/jquery_dialog.js"></script>'."\n";
//			$html .= $loadFiles;
//			$GLOBALS['loadFiles']['dialog'] = $loadFiles;
//		}
		$uid = md5(uniqid());
		$html .= '
				<label><input type="text" name="'.$name.'" value="'.$value.'" id="tb_input'.$uid.'" '.$extra.' /></label>
				<label><input type="button" class="dialog" alt="'.U('ffiles/tpl?opener_id=tb_input'.$uid.'&TB_iframe=true&height=400&width=500').'" title="模板库" id="upload_img_'.$uid.'" value="选择模板" /></label>
				';
		return $html;
	}

}

?>