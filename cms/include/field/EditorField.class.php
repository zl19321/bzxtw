<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: EditorField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:44
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 编辑器字段操作
// +----------------------------------------------------------------------
class EditorField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '编辑器';
	
	/**
	 * 字段的前台页面输出
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $field 字段名称
	 * @param array $setting 字段配置信息
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */	
	public function output($filed, $config, &$data) {
		if ($config['keywork-link']) {
			require_cache(INCLUDE_PATH . 'Keylink.function.php');			
			$data[$filed] = Keylink($data[$filed],1);
		}
	}
	
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
		$type = C ( 'EDITOR_TYPE' ) ? C ( 'EDITOR_TYPE' ) : 'tiny_mce';
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['default']; //当前值
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性
		$option = &$data ['setting'];
		if (empty($option)) $option['toolbar'] = 'basic';
		return Html::editor ( $name, $value, $type, $option, $extra );
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {		
		//各种配置参数
		if ($config ['toolbar'] == 'basic' || empty ( $config ['toolbar'] )) {
			$toolbar_checked_basic = 'checked';
			$toolbar_checked_advanced = '';
			$toolbar_checked_full = '';
		}
		if ($config ['toolbar'] == 'advanced') {
			$toolbar_checked_basic = '';
			$toolbar_checked_advanced = 'checked';
			$toolbar_checked_full = '';
		}
		if ($config ['toolbar'] == 'full') {
			$toolbar_checked_basic = '';
			$toolbar_checked_advanced = '';
			$toolbar_checked_full = 'checked';
		}
		if (!isset($config['keywork-link'])) $config['keywork-link'] = 1;
		${'keyword_link_'.$config['keywork-link']} = 'checked';
		$html = '
		 <table cellpadding="2" cellspacing="1">
			<tr> 
		      <td>编辑器样式：</td>
		      <td><input type="radio" name="info[setting][toolbar]" value="basic" ' . $toolbar_checked_basic . ' /> 简洁型 <input type="radio" name="info[setting][toolbar]" value="advanced" ' . $toolbar_checked_advanced . ' /> 标准型 <input type="radio" name="info[setting][toolbar]" value="full" ' . $toolbar_checked_full . ' /> 全功能</td>
		    </tr>
			<tr> 
		      <td>编辑器大小：</td>
		      <td>宽 <input type="text" name="info[setting][width]" value="'.$config['width'].'" size="4"> px 高 <input type="text" name="info[setting][height]" value="'.$config['height'].'" size="4"> px</td>
		    </tr>
		    <tr> 
		      <td>是否启用关键字链接：</td>
		      <td><input type="radio" name="info[setting][keywork-link]" value="1" '.${'keyword_link_1'}.'/> 启用 
		          <input type="radio" name="info[setting][keywork-link]" value="0" '.${'keyword_link_0'}.'/> 禁用 </td>
		    </tr>
			<tr> 
		      <td>默认值：</td>
		      <td><textarea name="info[setting][defaultvalue]" rows="2" cols="20" id="defaultvalue" style="height:100px;width:250px;">' . $config ['defaultvalue'] . '</textarea></td>
		    </tr>			
		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表字段
	 * 编辑器
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param $data 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data ['setting'] = unserialize ( $data ['setting'] );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` MEDIUMTEXT  NOT NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}

?>