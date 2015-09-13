<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: StyleField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:32
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 颜色和字型字段操作
// +----------------------------------------------------------------------
class StyleField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '颜色和字型';
	
	
		
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
	 * 栏目
	 */
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = &$data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$extra = &$data ['attribute']; //元素额外属性
		if (! empty ( $value )) {
			list ( $t, $code ) = explode ( '#', $value );
			$code = substr ( $code, 0, strlen ( $code ) - 2 );
			${'style_' . $code} = 'selected';
		}
		$html = '<select name="' . $name . '[color]" id="' . $name . '" ' . $extra . '>
					<option value="" ' . ${'style_'} . '>颜色</option>
					<option value="color:#000000;" style="background-color:#000000;" ' . ${'style_000000'} . '>#000000</option>
					<option value="color:#ADADAD;" style="background-color:#ADADAD;" ' . ${'style_ADADAD'} . '>#ADADAD</option>
					<option value="color:#000093;" style="background-color:#000093;" ' . ${'style_000093'} . '>#000093</option>
					<option value="color:#6A6AFF;" style="background-color:#6A6AFF;" ' . ${'style_6A6AFF'} . '>#6A6AFF</option>
					<option value="color:#CECEFF;" style="background-color:#CECEFF;" ' . ${'style_CECEFF'} . '>#CECEFF</option>
					<option value="color:#467500;" style="background-color:#467500;" ' . ${'style_467500'} . '>#467500</option>
					<option value="color:#EA0000;" style="background-color:#EA0000;" ' . ${'style_EA0000'} . '>#EA0000</option>
					<option value="color:#00FFFF;" style="background-color:#00FFFF;" ' . ${'style_00FFFF'} . '>#00FFFF</option>
					<option value="color:#6F00D2;" style="background-color:#6F00D2;" ' . ${'style_6F00D2'} . '>#6F00D2</option>
					<option value="color:#53FF53;" style="background-color:#53FF53;" ' . ${'style_53FF53'} . '>#53FF53</option>
					<option value="color:#5151A2;" style="background-color:#5151A2;" ' . ${'style_5151A2'} . '>#5151A2</option>
					<option value="color:#AE57A4;" style="background-color:#AE57A4;" ' . ${'style_AE57A4'} . '>#AE57A4</option>
					<option value="color:#707038;" style="background-color:#707038;" ' . ${'style_707038'} . '>#707038</option>
					<option value="color:#006000;" style="background-color:#006000;" ' . ${'style_006000'} . '>#006000</option>
					<option value="color:#FFFFFF;" style="background-color:#FFFFFF;" ' . ${'style_FFFFFF'} . '>#FFFFFF</option>
					</select>
		 		  <label>
		 		  <input type="checkbox" name="'.$name.'[b]" id="style_b" value="font-weight:bold;" /> 加粗
		 		  </label>';
		
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$config ['style'] ['color'] = empty ( $config ['style'] ['color'] ) ? '' : $config ['style'] ['color'];
		$color_code = substr ( substr ( $config ['style'] ['color'], 1 ), 0, strlen ( $config ['style'] ['color'] ) - 2 );
		${'style_' . $color_code} = 'selected';
		$html = '
		  <table cellpadding="2" cellspacing="1">
			<tr> 
		      <td>默认值</td>
		      <td>
		        <select name="info[setting][style][color]" id="style_color" >
					<option value="" ' . ${'style_'} . '>颜色</option>
					<option value="color:#000000;" style="background-color:#000000;" ' . ${'style_000000'} . '>#000000</option>
					<option value="color:#ADADAD;" style="background-color:#ADADAD;" ' . ${'style_ADADAD'} . '>#ADADAD</option>
					<option value="color:#000093;" style="background-color:#000093;" ' . ${'style_000093'} . '>#000093</option>
					<option value="color:#6A6AFF;" style="background-color:#6A6AFF;" ' . ${'style_6A6AFF'} . '>#6A6AFF</option>
					<option value="color:#CECEFF;" style="background-color:#CECEFF;" ' . ${'style_CECEFF'} . '>#CECEFF</option>
					<option value="color:#467500;" style="background-color:#467500;" ' . ${'style_467500'} . '>#467500</option>
					<option value="color:#EA0000;" style="background-color:#EA0000;" ' . ${'style_EA0000'} . '>#EA0000</option>
					<option value="color:#00FFFF;" style="background-color:#00FFFF;" ' . ${'style_00FFFF'} . '>#00FFFF</option>
					<option value="color:#6F00D2;" style="background-color:#6F00D2;" ' . ${'style_6F00D2'} . '>#6F00D2</option>
					<option value="color:#53FF53;" style="background-color:#53FF53;" ' . ${'style_53FF53'} . '>#53FF53</option>
					<option value="color:#5151A2;" style="background-color:#5151A2;" ' . ${'style_5151A2'} . '>#5151A2</option>
					<option value="color:#AE57A4;" style="background-color:#AE57A4;" ' . ${'style_AE57A4'} . '>#AE57A4</option>
					<option value="color:#707038;" style="background-color:#707038;" ' . ${'style_707038'} . '>#707038</option>
					<option value="color:#006000;" style="background-color:#006000;" ' . ${'style_006000'} . '>#006000</option>
					<option value="color:#FFFFFF;" style="background-color:#FFFFFF;" ' . ${'style_FFFFFF'} . '>#FFFFFF</option>
					</select>
		 		  <label>
		 		  <input type="checkbox" name="info[setting][style][b]" id="style_b" value="font-weight:bold;" /> 加粗
		 		  </label>
		 		</td>
		      </tr>
			</table>
			';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 字型和颜色
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data ['setting'] = unserialize ( $data ['setting'] );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` VARCHAR( 30 ) NOT NULL 
				DEFAULT NULL
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}

}

?>