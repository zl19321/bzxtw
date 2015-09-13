<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ShowSystemSetWidget.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 系统设置输出widget
// +----------------------------------------------------------------------
defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
class SysSetWidget extends Widget {
	
	/**
	 * 输出的html代码，包含head和body两个部分，选项卡导航部分和内容部分
	 * 
	 * @var array
	 */
	protected $html = array();
	
	/**
	 * 参数为数组形式，widget数组信息
	 * @param array $data
	 */
	public function render($data) {
		if (is_array($data['widget'])) {
			import('Html',INCLUDE_PATH);
			foreach($data['widget'] as $k=>$v) {
				if (method_exists($this,$v['block'])) {
					$extra = '';
					if (empty($data['selected'])) {//当前选中项，如果没有指定，则为第一个
						if ($k==0)  {
							$extra['head'] .= ' class="selected" ';
						} else {
							$extra['head'] .= ' class="" ';
							$extra['body'] .= ' style="display:none" ';
						}
					} else {
						if ($v['wid'] == $data['selected']) {
							$extra['head'] .= ' class="selected" ';
						} else {
							$extra['head'] .= ' class="" ';
							$extra['body'] .= ' style="display:none" ';
						}
					}
					$this->$v['block']($v,$data['value'],$extra);
				}
			}
		}
		return $this->html;
	}
	
	/**
	 * 基本信息
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function system($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>网站信息</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		$this->html['body'] .= '
			<tr>
			  <th>参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
		    <tr>
		      <th width="150">网站网址<span>含"http://"，无最后"/"</span></th>
		      <td><input name="info[siteurl]" type="text" id="siteurl" value="'.$value_data['siteurl'].'" size="40" maxlength="50"  class="input"/> <span class="help"> </span></td>
		      <td>siteurl</td>
		    </tr>
		    <tr>
		      <th>Meta Title<span>针对搜索引擎设置的网页标题</span></th>
		      <td><input name="info[seotitle]" type="text" id="seotitle" value="'.$value_data['seotitle'].'" size="40" maxlength="50"  class="input"/> <span class="help"> </span></td>
		      <td>seotitle</td>
		    </tr>
		    <tr>
		      <th>Meta Keywords<span>针对搜索引擎设置的关键词</span></th>
		      <td><textarea name="info[seokeywords]" cols="50" rows="2" id="seokeywords" class="textarea">'.$value_data['seokeywords'].'</textarea> <span class="help"> </span></td>
		      <td>seokeywords</td>
		    </tr>
		    <tr>
		      <th>Meta Description<span>针对搜索引擎设置的网页描述</span></th>
		      <td><textarea name="info[seodescription]" cols="50" rows="2" id="seodescription" class="textarea">'.$value_data['seodescription'].'</textarea> <span class="help"> </span></td>
		      <td>seodescription</td>
		    </tr>
		    <tr>
		      <th>版权信息<span>将显示在网站底部</span></th>
		      <td><textarea name="info[copyright]" cols="50" rows="2"  class="textarea">'.$value_data['copyright'].'</textarea> <span class="help"> </span></td>
		      <td>copyright</td>
			</tr>
		    <tr>
		      <th>网站ICP备案序号<span>请在信息产业部管理网站申请<br><a href="http://www.miibeian.gov.cn" target="_blank">http://www.miibeian.gov.cn</a></span></th>
		      <td><input name="info[icpno]" type="text" id="icpno" value="'.$value_data['icpno'].'" size="40"  class="input"/> <span class="help"> </span></td>
		      <td>icpno</td>
		    </tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	/**
	 * 网站设置
	 * 
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function website($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>公司信息</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		$this->html['body'] .= '
			<tr>
			  <th>参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
			<tr>
		      <th width="150">公司名称</th>
		      <td><input name="info[companyname]" type="text" id="companyname" value="'.$value_data['companyname'].'" size="40" maxlength="50" class="input" /> <span class="help"> </span></td>
		      <td>companyname</td>
		    </tr>
            <tr>
		      <th>公司邮箱</th>
		      <td><input name="info[companyemail]" type="text" id="companyemail" value="'.$value_data['companyemail'].'" size="40"  class="input"/> <span class="help"> </span></td>
		      <td>companyemail</td>
		    </tr>
            
		    <tr>
		      <th>联系人</th>
		      <td><input name="info[companylinkman]" type="text" id="companylinkman" value="'.$value_data['companylinkman'].'" size="40"  class="input"/> <span class="help"> </span></td>
		      <td>companylinkman</td>
		    </tr>
		    <tr>
		      <th>联系电话</th>
		      <td><input name="info[companytel]" type="text" id="companytel" value="'.$value_data['companytel'].'" size="40"  class="input"/> <span class="help"> </span></td>
		      <td>companytel</td>
		    </tr>
            <tr>
		      <th>公司传真</th>
		      <td><input name="info[companyfax]" type="text" id="companyfax" value="'.$value_data['companyfax'].'" size="40"  class="input"/> <span class="help"> </span></td>
		      <td>companyfax</td>
		    </tr>
		    <tr>
		      <th>公司地址</th>
		      <td><textarea name="info[companyaddress]" cols="50" rows="2"  class="textarea">'.$value_data['companyaddress'].'</textarea> <span class="help"> </span></td>
		      <td>companyaddress</td>
			</tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	
	/**
	 * 邮件设置
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function mail($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>邮件设置</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		//表单元素的值
		if (empty($value_data['mail_type'])) $value_data['mail_type'] = 1;
		${'mail_type_'.$value_data['mail_type']} = 'checked';
		if (!$value_data['port']) $value_data['port'] = 25;
		$this->html['body'] .= '
			<tr>
			  <th>参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
			<tr>
			  <th width="150">发送方式</th>
			  <td>
			    <input type="radio" name="info[mail_type]" id="mail_type_1" value="1" '.${'mail_type_1'}.' onclick="$(\'#mail_server\').attr(\'disabled\', false);$(\'#mail_port\').attr(\'disabled\', false);$(\'#mail_user\').attr(\'disabled\', false);$(\'#mail_password\').attr(\'disabled\', false);"  '.${'mail_type_1'}.'/>
			    通过SMTP协议发送(支持ESMTP验证) <br />
			    <input type="radio" name="info[mail_type]" id="mail_type_2" value="2" '.${'mail_type_2'}.' onclick="$(\'#mail_server\').attr(\'disabled\', true);$(\'#mail_port\').attr(\'disabled\', true);$(\'#mail_user\').attr(\'disabled\', true);$(\'#mail_password\').attr(\'disabled\', true);" '.(substr(strtolower(PHP_OS), 0, 3) == 'win'  ? 'disabled' : '' ). ${'mail_type_2'}.'/>
			    通过mail函数发送(仅*unix类主机支持，请配置php.ini sendmail_path 参数) <br />
			    <input type="radio" name="info[mail_type]" id="mail_type_3" value="3" '.${'mail_type_3'}.' onclick="$(\'#mail_server\').attr(\'disabled\', false);$(\'#mail_port\').attr(\'disabled\', false);$(\'#mail_user\').attr(\'disabled\', true);$(\'#mail_password\').attr(\'disabled\', true);"  '.${'mail_type_3'}.'/>
			    通过SOCKET连接SMTP服务器发送(仅Windows主机支持, 不支持ESMTP验证)<br /></td>
			  <td>mail_type</td>
			</tr>
			<tr>
			  <th width="150">邮件服务器
			    <span>SMTP服务器，只有正确设置才能使用发邮件功能</span></th>
			  <td><input name="info[mail_server]" id="mail_server" type="text" size="40" value="'.$value_data['mail_server'].'"  class="input"/></td>
			  <td>mail_server</td>
			</tr>
			<tr>
			  <th width="150">邮件发送端口
			   <span>默认为25，一般无需更改</span></th>
			  <td><input name="info[mail_port]" id="mail_port" type="text" size="40" value="25"  class="input"/></td>
			  <td>mail_port</td>
			</tr>
			<tr>
			  <th width="150">邮箱帐号
			    <span>SMTP服务器的用户帐号(完整的电子邮件地址如user@domain.com)，只有正确设置才能使用发邮件功能</span></th>
			  <td><input name="info[mail_user]" id="mail_user" type="text" size="40" value="'.$value_data['mail_user'].'"  class="input"/></td>
			  <td>mail_user</td>
			</tr>
			<tr>
			  <th width="150">邮箱密码</th>
			  <td><input name="info[mail_password]" id="mail_password" type="password" size="40" value="'.$value_data['mail_password'].'"  class="input"/></td>
			  <td>mail_password</td>
			</tr>
			<tr>
			  <th width="150">邮件设置测试
			    <span>请填写接受测试的邮件地址 </span></th>
			  <td>
			  <input name="email_to" type="text" id="email_to" value="" size="30"  class="input"/>
			  <input name="button" type="button" id="test_mail" onClick="javascript:test_mail();" value="发送测试邮件" />
			  <script language="javascript">
			  $().ready(function (){
			  	$("#test_mail").click(function (){
			  		$("#email_to").after(\'<img src="'._PUBLIC_.'images/working.gif" id="working_gif\');
			  		$.get("'.U('fset/set&ajax=test_mail').'&mail_type="+$("input[name=\'info[mail_type]\']").val()+"&mail_server="+$("#mail_server").val()+"&mail_port="+$("#mail_port").val()+"&mail_user="+$("#mail_user").val()+"&mail_password="+$("#mail_password").val()+"&email_to="+$("#email_to").val(), function(data){
						alert(data);
						$("#working_gif").remove();
					});
				});
			  });
			  </script>
			  </td>
			  <td>&nbsp;</td>
			</tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	
	
	/**
	 * 附件设置
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function attachment($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>附件设置</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		if (!isset($value_data['upload_water_place'])) {
			$value_data['upload_water_place'] = 9;
		}
		$pos_checked = ${'pos_'.(int)$value_data['upload_water_place']} = 'checked';
		$html = '
				<table cellpadding="0" cellspacing="0" style=" width:300px; height:150px;">
				  <tbody  style="background:url('._PUBLIC_.'images/flower.jpg) no-repeat;">
			      <tr align="center">
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="1" '.${'pos_1'}.'/>
			          #1</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="2" '.${'pos_2'}.'/>
			          #2</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="3" '.${'pos_3'}.'/>
			          #3</td>
			      </tr>
			      <tr align="center">
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="4" '.${'pos_4'}.'/>
			          #4</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="5" '.${'pos_5'}.'/>
			          #5</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="6" '.${'pos_6'}.'/>
			          #6</td>
			      </tr>
			      <tr align="center">
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="7" '.${'pos_7'}.'/>
			          #7</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="8" '.${'pos_8'}.'/>
			          #8</td>
			        <td style="border:0;"><input type="radio" name="info[upload_water_place]" value="9" '.${'pos_9'}.'/>
			          #9</td>
			      </tr>
			      </tbody>
			    </table>
		';
		if(function_exists('imagepng')) $gd .= "PNG ";
        if(function_exists('imagejpeg')) $gd .= " JPG ";
        if(function_exists('imagegif')) $gd .= " GIF ";
        //默认项
        !isset($value_data['upload_thumb_isthumb']) && $value_data['upload_thumb_isthumb'] = 1;
        !isset($value_data['upload_water_iswatermark']) && $value_data['upload_water_iswatermark'] = 0;
        ${'upload_thumb_isthumb_'.$value_data['upload_thumb_isthumb']} = 'checked';
        ${'upload_water_iswatermark_'.$value_data['upload_water_iswatermark']} = 'checked';
        
        ${'is_brcode_'.$value_data['is_brcode']} = 'checked';
        ${'is_comment_'.$value_data['is_comment']} = 'checked';
        ${'is_cut_'.$value_data['is_cut']} = 'checked';
        
        $body_select = "";
		for($size=1; $size<=10; $size++) 
			$body_select .= '<option value="'.$size.'"'.(($value_data['brcode_size']==$size)?' selected':'').'>'.'尺寸：'.$size.'</option>';
        
		$this->html['body'] .= '
			<tr>
			  <th width="150">参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
			<tr>
			  <th width="150">允许上传的附件类型<span></span></th>
			  <td><input name="info[upload_attachment_allowext]" type="text" id="upload_attachment_allowext" value="'.$value_data['upload_attachment_allowext'].'" size="40"  class="input"/></td>
			  <td>upload_attachment_allowext</td>
			</tr>
			<tr>
			  <th width="150">允许上传的附件大小<span></span></th>
			  <td><input name="info[upload_maxsize]" type="text" id="upload_maxsize" value="'.$value_data['upload_maxsize'].'" size="15" maxlength="10"  class="input"/>KB</td>
			  <td>upload_maxsize</td>
			</tr>
			<tr>
			  <th width="150">允许的上传的图片类型<span></span></th>
			  <td><input name="info[upload_images_allowext]" type="text" id="upload_images_allowext" value="'.$value_data['upload_images_allowext'].'" size="40"  class="input"/></td>
			  <td>upload_images_allowext</td>
			</tr>
			<tr>
			  <th width="150">文件或者图片上传路径<span></span></th>
			  <td><input name="info[upload_dir]" type="text" id="upload_dir" value="'.$value_data['upload_dir'].'" size="40"  class="input"/></td>
			  <td>upload_dir</td>
			</tr>
			<tr>
			  <th width="150">文件或者图片上传目录<span></span></th>
			  <td><input name="info[upload_url]" type="text" id="upload_url" value="'.$value_data['upload_url'].'" size="40"  class="input"/></td>
			  <td>upload_url</td>
			</tr>
			<tr>
			  <th width="150">PHP图形处理（GD库）功能检测<span></span></th>
			  <td><font color="red">支持'.$gd.' </font></td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <th width="150">启用缩略图功能<span></span></th>
			  <td><input type="radio" name="info[upload_thumb_isthumb]" value="1"  '.${'upload_thumb_isthumb_1'}.' />
			    是&nbsp;&nbsp;&nbsp;&nbsp;
			    <input type="radio" name="info[upload_thumb_isthumb]" value="0"  '.${'upload_thumb_isthumb_0'}.' />
			    否 </td>
			  <td>upload_thumb_isthumb</td>
			</tr>
			<tr>
			  <th width="150">缩略图大小
			    <span>设置缩略图的大小，小于此尺寸的图片附件将不生成缩略图</span></th>
			  <td><input name="info[upload_thumb_width]" type="text" id="upload_thumb_width" value="'.$value_data['upload_thumb_width'].'" size="5" maxlength="5"  class="input"/>
			    X
			    <input name="info[upload_thumb_height]" type="text" id="upload_thumb_height" value="'.$value_data['upload_thumb_height'].'" size="5" maxlength="5" class="input" />
			    px</td>
			  <td>upload_thumb_width, upload_thumb_height</td>
			</tr>
            <tr>
			  <th width="150">启用图片裁剪<span></span></th>
			  <td><input type="radio" name="info[is_cut]" value="1"  '.${'is_cut_1'}.' />
			    是&nbsp;&nbsp;&nbsp;&nbsp;
			    <input type="radio" name="info[is_cut]" value="0"  '.${'is_cut_0'}.' />
			    否 </td>
			  <td>is_cut</td>
			</tr>
            <tr>
			  <th width="150">图片裁剪缩放比例<span>根据显示器尺寸自行设定</span></th>
			  <td><input name="info[cut_size]" type="text" id="cut_size" value="'.$value_data['cut_size'].'" size="30" maxlength="255" />px</td>
			  <td>cut_size</td>
			</tr>
            <tr>
			  <th width="150">启用评论<span></span></th>
			  <td><input type="radio" name="info[is_comment]" value="1"  '.${'is_comment_1'}.' />
			    是&nbsp;&nbsp;&nbsp;&nbsp;
			    <input type="radio" name="info[is_comment]" value="0"  '.${'is_comment_0'}.' />
			    否 </td>
			  <td>is_comment</td>
			</tr>
            <tr>
			  <th width="150">启用二维码功能<span></span></th>
			  <td><input type="radio" name="info[is_brcode]" value="1"  '.${'is_brcode_1'}.' />
			    是&nbsp;&nbsp;&nbsp;&nbsp;
			    <input type="radio" name="info[is_brcode]" value="0"  '.${'is_brcode_0'}.' />
			    否 </td>
			  <td>is_brcode</td>
			</tr>
			<tr>
			  <th width="150">二维码图片大小
			    <span> 选择(尺寸：1)生成的二维码图片为28px*28px，其它尺寸宽高则乘以其倍数</span></th>
			  <td><select name="info[brcode_size]">'.$body_select.'</select></td>
			  <td>brcode_width, brcode_height</td>
			</tr>            
			<tr>
			  <th width="150">启用图片水印功能<span></span></th>
			  <td><input type="radio" name="info[upload_water_iswatermark]" value="1" '.${'upload_water_iswatermark_1'}.' />
			    是&nbsp;&nbsp;&nbsp;&nbsp;
			    <input type="radio" name="info[upload_water_iswatermark]" value="0" '.${'upload_water_iswatermark_0'}.' />
			    否 </td>
			  <td>upload_water_iswatermark</td>
			</tr>
			<tr>
			  <th width="150">水印添加条件<span></span></th>
			  <td><input name="info[watermark_minwidth]" type="text" id="watermark_minwidth" value="'.$value_data['watermark_minwidth'].'" class="input" size="5" maxlength="5" />
			    X
			    <input name="info[watermark_minheight]" type="text" id="watermark_minheight" value="'.$value_data['watermark_minheight'].'" class="input" size="5" maxlength="5" />
			    px</td>
			  <td>watermark_minwidth, watermark_minheight</td>
			</tr>
			<tr>
			  <th width="150">水印图片路径
			    <span>您可替换水印文件以实现不同的水印效果</span> </th>
			  <td><input name="info[upload_water_path]" type="text" id="upload_water_path" value="'.$value_data['upload_water_path'].'" size="30" maxlength="255" class="input" /></td>
			  <td>upload_water_path</td>
			</tr>
			<tr>
			  <th width="150">水印透明度
			    <span>范围为 1~100 的整数，数值越小水印图片越透明</span></th>
			  <td><input name="info[upload_water_trans]" type="text" id="upload_water_trans" value="'.$value_data['upload_water_trans'].'" size="10" maxlength="10" class="input" /></td>
			  <td>upload_water_trans</td>
			</tr>
			<tr>
			  <th width="150">JPEG 水印质量
			    <span>范围为 0~100 的整数，数值越大结果图片效果越好，但尺寸也越大</span></th>
			  <td><input name="info[watermark_quality]" type="text" id="watermark_quality" value="'.$value_data['watermark_quality'].'" size="10" maxlength="10" class="input" /></td>
			  <td>watermark_quality</td>
			</tr>
			<tr>
			  <th width="150">水印添加位置
			    <span>附加的水印图片位于 ./images/watermark.gif</span> </th>
			  <td>'.$html.'</td>
			  <td>upload_water_place</td>
			</tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	/**
	 * FTP设置
	 * 
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function ftp($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>FTP设置</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		$this->html['body'] .= '
			<tr>
			  <th width="150">参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	/**
	 * 撰写配置
	 * 
	 * @param $widget_info 该widget在数据中的信息
	 * @param $value_data 该widget在表单中显示需要的数据
	 * @param $extra 该widget的选项卡导航部分和内容部分的额外html属性
	 */
	protected function write($widget_info,$value_data,$extra) {
		$this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>撰写配置</a></li>";
		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		$this->html['body'] .= '
			<tr>
			  <th width="150">参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
			<tr>
			  <th>编辑器类型:</th>
		      <td><input name="info[editor_type]" type="radio" value="kindeditor" ' . ($value_data['editor_type']=='kindeditor' ? "checked" : '') . ' />Kind Editor
                  <input name="info[editor_type]" type="radio" value="ueditor" ' . ($value_data['editor_type']=='ueditor' ? "checked" : '') . ' />Ueditor
		       <span class="help"> </span></td>
		      <td>editor_type</td>
			</tr>
			<tr>
		      <th>是否发布内容自动ping:</th>
		      <td><input name="info[auto_ping]" type="radio" value="1" ' . ($value_data['auto_ping']==1 ? "checked" : '') . ' />是&nbsp;&nbsp;
		      	  <input name="info[auto_ping]" type="radio" value="0" ' . ($value_data['auto_ping']==0 ? "checked" : '') . ' />否
		       <span class="help"> </span></td>
		      <td>auto_ping</td>
		    </tr>
		    <tr>
		      <th>ping地址:<span>每行一个，当开启自动ping功能以后才会生效</span></th>
		      <td><textarea name="info[ping_sites]" cols="50" rows="10"  class="textarea">'.$value_data['ping_sites'].'</textarea> <span class="help"> </span></td>
		      <td>ping_sites</td>
		    </tr>
		    <tr>
		      <th>替换词语:<span>（词语会被替换成***）用|分开，但不要在结尾加|</span></th>
		      <td><textarea name="info[filter_word]" cols="50" rows="10"  class="textarea">'.$value_data['filter_word'].'</textarea> <span class="help"> </span></td>
		      <td>filter_word</td>
		    </tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
	protected function  blog($widget_info,$value_data,$extra){
	     $this->html['head'] .= "<li><a href='javascript:;' rel='tabsContent{$widget_info['wid']}' {$extra['head']}>微博设置</a></li>";		 
	     		$this->html['body'] .= "
		<table cellpadding='0' cellspacing='0' class='tabcontent' id='tabsContent{$widget_info['wid']}' {$extra['body']}>
		<tbody>";
		$this->html['body'] .= '
			<tr>
			  <th width="150">参数说明</th>
			  <th style="text-align:left">参数值</th>
			  <th style="text-align:left">变量名</th>
			</tr>
			<tr>
			  <th colspan="3">腾讯微博参数设置:</th>
			</tr>
		    <tr>
		      <th>appid:</th>
		      <td><input name="info[tencappid]" type="text" value="'.$value_data['tencappid'].'" size="30" maxlength="255" class="input" /></td>
		      <td>tencappid</td>
		    </tr>
					    <tr>
		      <th>appkey:</th>
		      <td><input name="info[tencappkeyd]" type="text" value="'.$value_data['tencappkeyd'].'" size="30" maxlength="255" class="input" /></td>
		      <td>tencappkeyd</td>
		    </tr>
		';
		$this->html['body'] .= '</tbody><tfoot>
				<tr>
					<td></td>
					<td><label class="btn"><input type="submit" name="submit" value="确定保存" class="submit" /></label></td>
				</tr>
			</tfoot></table>';
	}
	
}