<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: UploadField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:49:40
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 文件字段操作
// +----------------------------------------------------------------------
class UploadField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '多文件上传';

	
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
				$field_value = explode("\n",$data[$field]);
				$data[$field] = array();
				foreach ($field_value as $k => $t){
					$file_info = explode('|', $t);
					$data[$field][$k]['value'] = $file_info[0] . '|' . $file_info[1];
					$data[$field][$k]['name'] = $file_info[0] ;
					$data['download_field_name'] = $field;
					if($config['downloadtype'] == 1) {  //通过php读取
						$data[$field][$k]['download'] = '/down?cid=' . $data['cid'] . '&hash=' . base64_encode(time());
					} else {  //连接文件地址
						$data[$field][$k]['download'] = __ROOT__ . '/' . C('UPLOAD_DIR') . $file_info[1];
					}
				}
				
			} else {  //外部链接
				$file_info = $data[$field];
				$data[$field] = array();
				$data[$field]['value'] = $file_info;
				$data['download_field_name'] = $field;
				if($config['downloadtype'] == 1) {  //通过php读取
					$data[$field]['download'] = '/down?cid=' . $data['cid'] . '&hash=' . base64_encode(time());
				} else {  //连接文件地址
					$data[$field]['download'] = $file_info;
				}
			}
			//设置权限
			if(!isset($_SESSION['userdata']['roles'])) $_SESSION['userdata']['roles'][0] = 'guest';
			if(isset($_SESSION['userdata']['username']) && $_SESSION['userdata']['username'] == 'developer') {
				$data[$field]['permission_status'] = 1;
			} else {
				$data[$field]['permission_status'] = 0;
			}
			foreach($_SESSION['userdata']['roles'] AS $role) {
				if (in_array($role, $config['permissions'])) {
					$data[$field]['permission_status'] = 1;
					break;
				}
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
		@$arr = explode('|', $config['upload_allowext']);
		foreach ($value AS $k=>$v) {
			$info = pathinfo($v);
			if (is_array($arr) && !in_array( strtolower($info['extension']), $arr)) {
				unset($value[$k]);
			}
		}		
		$value = implode("\n", $value);		
		return trim($value);
	}
	
	
	
	/**
	 * 文件上传
	 */
	public function form($data = '') {
		$html = "";		
		$extra = &$data ['attribute']; //元素额外属性
		$html .= '
		<label><input type="button" class="dialog" alt="'.U('fupload/fieldupload?fieldid='.$data['fieldid'].'&shower_id=files_box&multi=true&height=250&width=500').'" title="从电脑上传文件" class="notips" id="upload_img_'.$data['fieldid'].'" value="上传文件" /></label>
		<div id="files_box" style="padding:10px">
		<input type="hidden" value="' . $file . '" name="info[' . $data['field'] . '][]">
		';
		if (!empty($data['value'])) {
			foreach ($data['value'] as $file){
				
				$_file = explode('|', $file['value']);
				$user_file_name = $_file[0];
				
				$html .= '
				<div title="双击删除此文件" style="cursor: pointer;" ondblclick="$(this).remove();">
				' . $user_file_name . '
				<input type="hidden" value="' . $file['value'] . '" name="info[' . $data['field'] . '][]">
				</div>';
			}
			/*
			foreach (explode("\n", $data['value']) AS $file) {
				$_file = explode('|', $file);
				$user_file_name = $_file[0];   //用户上传的文件名
				$sys_file_name = $_file[1];   //系统生成的文件名
				$html .= '
				<div title="双击删除此文件" style="cursor: pointer;" ondblclick="$(this).remove();">
				' . $user_file_name . '
				<input type="hidden" value="' . $file . '" name="info[' . $data['field'] . '][]">
				</div>
				';
			}
			*/
		}
		$html .= '</div>';
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {		
		$config ['upload_allowext'] = ! empty ( $config ['upload_allowext'] ) ? $config ['upload_allowext'] : 'zip|rar|doc|docx|xls|ppt|txt';		
		$config ['upload_maxsize'] = ! empty($config['upload_maxsize']) ? $config['upload_maxsize'] : 1024;
		$config['downloadtype'] = (int)$config['downloadtype'] ? 1 : 0;
		${'downloadtype_'.$config['downloadtype']} = 'checked';
		$html = '
		  <table cellpadding="2" cellspacing="1">			
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