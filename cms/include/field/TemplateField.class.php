<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TemplateField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:49
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 模板选择字段操作
// +----------------------------------------------------------------------
class TemplateField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '模板';
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		
		return $value;
	}
	
	
	
	/**
	 * 时间和日期
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
//			$loadFiles = '<script type="text/javascript" src="'._PUBLIC_.'js/jquery_dialog.js"></script>'."\n";
//			$html .= $loadFiles;
//			$GLOBALS['loadFiles']['dialog'] = $loadFiles;
//		}
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性
		$html .= '
				<label><input type="text" name="'.$name.'" value="'.$value.'" id="tb_input'.$data['fieldid'].'" '.$extra.' /></label>
				<label><input type="button" class="dialog" alt="'.U('ffiles/tpl?fieldid='.$data['fieldid'].'&opener_id=tb_input'.$data['fieldid'].'&height=300&width=500').'" title="模板库" id="upload_img_'.$data['fieldid'].'" value="选择模板" /></label>				
				';
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		
		$html = '
		  <table cellpadding="2" cellspacing="1" onclick="javascript:$(\'#minlength\').val(0);$(\'#maxlength\').val(255);">
			<tr> 
		      <td>文本框长度</td>
		      <td><input type="text" name="info[setting][size]" value="'.$config['size'].'" size="10"></td>
		    </tr>			
		</table>
		';
		return $html;
	}
	
	
	/**
	 * 创建物理数据表
	 * 模板
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data['setting'] = unserialize($data['setting']);
		if(!$data['setting']['maxlength']) $data['setting']['maxlength'] = 255;
		$maxlength = min($data['setting']['maxlength'], 255);
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( {$maxlength} ) NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query($sql);
	}
}

?>