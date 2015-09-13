<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TextareaField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:49:00
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 多行文本字段操作
// +----------------------------------------------------------------------
class TextareaField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '多行文本';
	
	
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		if (!$config['ishtml']) {
			$value = htmlspecialchars($value);
		}
		return $value;
	}
	
	/**
	 * 多行文本
	 */
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性			
		return Html::textarea ( $name, $value,$data['setting']['cols'],$data['setting']['rows'], $extra );
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$html = '
		  <table cellpadding="2" cellspacing="1">
			<tr> 
		      <td>文本域行数</td>
		      <td><input type="text" name="info[setting][rows]" value="'.$config['rows'].'" size="10"></td>
		    </tr>
			<tr> 
		      <td>文本域列数</td>
		      <td><input type="text" name="info[setting][cols]" value="'.$config['cols'].'" size="10"></td>
		    </tr>
			<tr> 
		      <td>默认值</td>
		      <td><textarea name="info[setting][defaultvalue]" rows="2" cols="20" id="defaultvalue" style="height:60px;width:250px;">'.$config['defaultvalue'].'</textarea></td>
		    </tr>
		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表字段
	 * 多行文本
	 * 
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data ['setting'] = unserialize ( $data ['setting'] );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` TEXT  NOT NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}

?>