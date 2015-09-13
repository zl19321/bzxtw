<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TitleField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:49:20
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 标题类型字段操作
// +----------------------------------------------------------------------
class TitleField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '标题';
	
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		return htmlspecialchars($value);
	}
	
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$extra = &$data ['attribute']; //元素额外属性
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' size="'.$data['setting']['size'].'"';
		$extra .= ' id="title_'.$data['fieldid'].'"';
//		$extra .= ' class="'.($data['css'] ? $data['css'] : 'title').'  '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$html = Html::input ( $name, $value, $extra );
		$html .= '
		<input type="button" value="检测标题" onclick="$.get(\''.U('fcontent/add').'\', { ajax : \'check_title\', c_title:$(\'#info_title\').val(),catid:$(\'#catid\').val()}, function(data){if(data == \'此标题已经被使用过！\'){$(\'#info_title\').val(\'\');$(\'#t_msg\').html(data);}else{$(\'#t_msg\').html(data);}  })">&nbsp;<span id=\'t_msg\'></span>
		';
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$html = '
		文本框长度 <input type="text" name="info[setting][size]" value="' . $config ['size'] . '" size="10">		  
		';		
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 标题
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data ['setting'] = unserialize ( $data ['setting'] );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( 100 ) NOT NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}

}

?>