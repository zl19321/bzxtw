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
class FileField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '单文件上传';
	
	/**
	 * 字段的前台页面输出
	 * 都造成下载信息数组
	 * $data[{$fieldname}] = array(
	 * 		'filename' => '',
	 * 		'url' => '',
	 * );
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $field 字段名称
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */	
	public function output($field, $config, &$data) {
		if (!empty($data[$field])) {
			if (strpos($data[$field], '|')) {  //直接上传的
				$file_info = explode('|', $data[$field]);
				$data[$field] = array();
				$data[$field]['value'] = $file_info[0] . '|' . $file_info[1];
				$data[$field]['name'] = $file_info[0];
				$data['download_field_name'] = $field;
				if($config['downloadtype'] == 1) {  //通过php读取
					$data[$field]['download'] = '/down?cid=' . $data['cid'] . '&hash=' . base64_encode(time());
				} else {  //连接文件地址
					$data[$field]['download'] = __ROOT__ . '/' . C('UPLOAD_DIR') . $file_info[1];
				}
			}else {
				$data[$field] = __ROOT__ . '/' . C('UPLOAD_DIR') . $data[$field];
			}
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
		if (is_array($arr) && !in_array(strtolower($info['extension']),$arr)) {
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
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$value = str_replace(__ROOT__.'/'.C('UPLOAD_DIR'), '',$value);
		$extra = $data ['attribute']; //元素额外属性
		$html .= '
		<label><input type="text" name="'.$name.'" value="'.$value.'" id="tb_input'.$data['fieldid'].'" '.$extra.' style="width:260px" /></label>
		<label><input type="button" class="dialog" alt="'.U('fupload/fieldupload?fieldid='.$data['fieldid'].'&opener_id=tb_input'.$data['fieldid'].'&height=250&width=500').'" title="从电脑上传文件" class="notips" id="upload_file_'.$data['fieldid'].'" value="上传文件" /></label>';
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {		
		$config ['upload_allowext'] = ! empty ( $config ['upload_allowext'] ) ? $config ['upload_allowext'] : 'zip|rar|doc|docx|xls|ppt|txt';
		$config ['size'] = ! empty($config['size']) ? $config['size'] : 50;
		$config ['upload_maxsize'] = ! empty($config['upload_maxsize']) ? $config['upload_maxsize'] : 1024;
		$config['downloadtype'] = (int)$config['downloadtype'] ? 1 : 0;
		${'downloadtype_'.$config['downloadtype']} = 'checked';
		$html = '
		  <table cellpadding="2" cellspacing="1">
			<tr> 
		      <td>文件列表框宽度</td>
		      <td><input type="text" name="info[setting][size]" value="'.$config ['size'].'" size="5" /></td>
		    </tr>
			<tr> 
		      <td>允许上传的文件大小</td>
		      <td><input type="text" name="info[setting][upload_maxsize]" value="'.$config ['upload_maxsize'].'" size="5" /> KB 提示：1 KB = 1024 Byte，1 MB = 1024 KB *</td>
		    </tr>
			<tr> 
		      <td>允许上传的文件类型</td>
		      <td><input type="text" name="info[setting][upload_allowext]" value="'.$config ['upload_allowext'].'" size="50" /></td>
		    </tr>			
			<tr> 
		      <td>文件下载方式</td>
		      <td><input type="radio" name="info[setting][downloadtype]" value="0" '.${'downloadtype_0'}.' /> 链接文件地址 <input type="radio" name="info[setting][downloadtype]" value="1" '.${'downloadtype_1'}.' /> 通过PHP读取</td>
		    </tr>
		  </table>
		  ';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 上传文件
	 * @param object $model 数据表模型对象
	 * @param string $tableName 要操作的数据表
	 * @param mixed 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( 250 ) NULL 
				DEFAULT NULL
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}

?>