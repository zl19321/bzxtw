<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ThumbField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:49:08
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 但图片字段操作
// +----------------------------------------------------------------------
class ThumbField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '单图片';
	
	public function output($field, $config, &$data) {
		if (strpos('http://', $data[$field]) === false) {
			$data[$field] = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . $data[$field];
		}
		
		return $data;
	}
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		//检查文件类型
		@$arr = explode('|',$config['upload_allowext']);		
		$info = pathinfo($value);
		if (is_array($arr) && !in_array( strtolower($info['extension']),$arr)) {
			return '';
		} else {
			return $value;
		}
	}
	
	/**
	 * 输出缩略图上传表单
	 * 
	 * @param array $data 字段信息
	 */
	public function form($data = '') {		
		$html = '';
//		if (empty($GLOBALS['loadFiles']['thickbox'])) {
//			$loadFiles = '<script type="text/javascript" src="'._PUBLIC_.'js/thickbox/thickbox-compressed.js"></script>
//						  <link rel="stylesheet" type="text/css" href="'._PUBLIC_.'js/thickbox/css.css" />'."\n";
//			$html .= $loadFiles;
//			$GLOBALS['loadFiles']['thickbox'] = $loadFiles;
//		}
//		if (empty($GLOBALS['loadFiles']['dialog'])) {
//			$loadFiles = '<script type="text/javascript" src="'._PUBLIC_.'js/thickbox/thickbox-compressed.js"></script>
//						  <link rel="stylesheet" type="text/css" href="'._PUBLIC_.'js/thickbox/css.css" />'."\n";
//			$html .= $loadFiles;
//			$GLOBALS['loadFiles']['dialog'] = $loadFiles;
//		}
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性
		if (strpos('http://', $value) === false) {
			$value = str_replace(__ROOT__.'/'.C('UPLOAD_DIR'), '',$value);
		}
		$html .= '
		<label><input type="text" name="'.$name.'" value="'.$value.'" id="info_'.$data ['field'].'" '.$extra.' style="width:260px" /></label>
		<label><input type="button" class="dialog" alt="'.U('fupload/fieldupload?fieldid='.$data['fieldid'].'&opener_id=info_'.$data ['field'].'&height=250&width=500').'" title="从电脑上传图片" class="notips" id="upload_img_'.$data['fieldid'].'" value="上传图片" /></label>
		<label><input type="button" class="dialog" alt="'.U('ffiles/images?fieldid='.$data['fieldid'].'&opener_id=info_'.$data ['field'].'&height=450&width=700').'" title ="站内图片库"  class="notips" id="choose_img_'.$data['fieldid'].'" value="站内选择" /></label>
		';
		return $html;
	}
	
/**
	 * 配置输出
	 */
	public function setting($config = '') {
		//各项配置
		$config['thumb_height']		= $config['thumb_height'] ? $config['thumb_height'] : C ('UPLOAD_THUMB_HEIGHT');
		$config['thumb_width']		= $config['thumb_width'] ? $config['thumb_width'] : C ('UPLOAD_THUMB_WIDTH');
		$config['upload_maxsize']	= $config['upload_maxsize'] ? $config['upload_maxsize'] : C ('UPLOAD_MAXSIZE');			
		$config['upload_allowext']	= $config['upload_allowext'] ? $config['upload_allowext'] : 'gif|jpg|jpeg|png|bmp';			
		$config['isthumb']			= $config['isthumb'] ? $config['isthumb'] : (int)C ('UPLOAD_THUMB_ISTHUMB');			
		$config['iswatermark']		= $config['iswatermark'] ? $config['iswatermark'] : C ('UPLOAD_WATER_ISWATERMARK');			
		$config['water_path']	= $config['water_path'] ? $config['water_path'] : C ('UPLOAD_WATER_PATH') ;			
		
		if($config['isthumb']) {
			$thumb_display = 'style="display:block"';
			$checked_thumb_1 = 'checked';
			$checked_thumb_0 = '';
		} else {
			$thumb_display = 'style="display:none"';
			$checked_thumb_1 = '';
			$checked_thumb_0 = 'checked';
		}
		
		if($config['iswatermark']) {
			$water_display = 'style="display:block"';
			$checked_water_1 = 'checked';
			$checked_water_0 = '';
		} else {
			$water_display = 'style="display:none"';
			$checked_water_1 = '';
			$checked_water_0 = 'checked';
		}
		$html = '
		  <table cellpadding="2" cellspacing="1" onclick="javascript:$(\'#minlength\').val(0);$(\'#maxlength\').val(255);">
			<tr> 
		      <td>文本框长度</td>
		      <td><input type="text" name="info[setting][size]" value="'.$config['size'].'" size="10"></td>
		    </tr>
			<tr> 
		      <td>默认值</td>
		      <td><input type="text" name="info[setting][defaultvalue]" value="'.$config['defaultvalue'].'" size="40"></td>
		    </tr>
			<tr> 
		      <td>允许上传的图片大小</td>
		      <td><input type="text" name="info[setting][upload_maxsize]" value="'.$config['upload_maxsize'].'" size="5">KB 提示：1KB=1024Byte，1MB=1024KB *</td>
		    </tr>
			<tr> 
		      <td>允许上传的图片类型</td>
		      <td><input type="text" name="info[setting][upload_allowext]" value="'.$config['upload_allowext'].'" size="40"></td>
		    </tr>			
			<tr> 
		      <td>是否产生缩略图</td>
		      <td><input type="radio" name="info[setting][isthumb]" value="1" '.$checked_thumb_1.' onclick="$(\'#thumb_size\').show()"/> 是 <input type="radio" name="info[setting][isthumb]" value="0"  '.$checked_thumb_0.' onclick="$(\'#thumb_size\').hide()"/> 否</td>
		    </tr>
			<tr id="thumb_size" '.$thumb_display.'> 
		      <td>缩略图大小</td>
		      <td>宽 <input type="text" name="info[setting][thumb_width]" value="'.$config['thumb_width'].'" size="3">px 高 <input type="text" name="info[setting][thumb_height]" value="'.$config['thumb_height'].'" size="3">px</td>
		    </tr>
			<tr> 
		      <td>是否加图片水印</td>
		      <td><input type="radio" name="info[setting][iswatermark]" value="1" '.$checked_water_1.' onclick="$(\'#watermark_img\').show()"/> 是 <input type="radio" name="info[setting][iswatermark]" value="0" '.$checked_water_0.'  onclick="$(\'#watermark_img\').hide()"/> 否</td>
		    </tr>
			<tr id="watermark_img" '.$water_display.'> 
		      <td>水印图片路径</td>
		      <td><input type="text" name="info[setting][water_path]" value="'.$config['water_path'].'" size="40"></td>
		    </tr>          
		</table>
			';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 缩略图
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( 200 ) NULL 
				DEFAULT NULL
				COMMENT '{$data['name']}'";
		return $model->query($sql);
	}
}

?>