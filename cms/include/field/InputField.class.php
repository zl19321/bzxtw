<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: InputField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:01
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 单行文本字段操作
// +----------------------------------------------------------------------
class InputField implements FieldInterface {
	
	public $name = '单行文本';
	
	
	/**
	 * 字段的前台页面输出
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $class 字段类型
	 * @param string $field 字段名称
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */	
	public function output($field, &$data) {
	
	}
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		return htmlspecialchars($value);
	}
	
	/**
	 * 时间和日期
	 */
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$extra = &$data ['attribute']; //元素额外属性
		$type = $data ['setting']['ispassword'] ? 'password' : 'text';
		return Html::input ( $name, $value, $extra, $type );
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$config ['size'] = intval ( $config ['size'] );
		$config ['size'] = $config ['size'] ? $config ['size'] : '';
		$config ['ispassword'] = $config ['ispassword'] ? $config ['ispassword'] : 0;
		if ($config ['ispassword']) {
			$ispassword_checked_0 = '';
			$ispassword_checked_1 = 'checked';
		} else {
			$ispassword_checked_0 = 'checked';
			$ispassword_checked_1 = '';
		}
		$html = '
		  <table cellpadding="2" cellspacing="1" onclick="javascript:$(\'#minlength\').val(0);$(\'#maxlength\').val(255);">
			<tr> 
		      <td>文本框长度</td>
		      <td><input type="text" name="info[setting][size]" value="' . $config ['size'] . '" size="10"></td>
		    </tr>
			<tr> 
		      <td>默认值</td>
		      <td><input type="text" name="info[setting][defaultvalue]" value="' . $config ['defaultvalue'] . '" size="40"></td>
		    </tr>
			<tr> 
		      <td>是否为密码框</td>
		      <td><input type="radio" name="info[setting][ispassword]" value="1" ' . $ispassword_checked_1 . '> 是 <input type="radio" name="info[setting][ispassword]" value="0"  ' . $ispassword_checked_0 . '> 否</td>
		    </tr>
		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 单行文本
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data ['setting'] = unserialize ( $data ['setting'] );
		if (! $data ['setting'] ['maxlength'])
			$data ['setting'] ['maxlength'] = 255;
		$maxlength = min ( $data ['setting'] ['maxlength'], 255 );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( {$maxlength} ) NOT NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}

?>