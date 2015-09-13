<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: DatetimeField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:32
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 时间日期字段操作
// +----------------------------------------------------------------------
class DatetimeField implements FieldInterface {
	
	public $name = '时间和日期';
	
	/**
	 * 字段的前台页面输出
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $field 字段名称
	 * @param array $setting 字段配置信息
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */	
	public function output($field, $config, &$data) {
		
	}
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		if (empty($value)) { //根据配置
			if ($config['defaulttype'] >0 ) {
				switch ($config['defaulttype']) {
					case '1':
						$value = time();
						break;
					case '2':
						$value = $config['defaultvalue'];
						break;
					default:
						break;
				}
			}
		}
		if (!is_numeric($value)) $value = strtotime($value);
		return $value;
	}
	
	/**
	 * 后台该元素的表单样式
	 * 
	 * @param array $data 字段信息
	 */
	public function form($data = '') {
		//日期格式
		if ($data ['setting'] ['format'] == 'Y-m-d H:i:s') {
			$format = 'yyyy-MM-dd HH:mm:ss';
		} else if ($data ['setting'] ['format'] == 'Y-m-d H:i') {
			$format = 'yyyy-MM-dd HH:mm';
		} else if ($data ['setting'] ['format'] == 'Y-m-d') {
			$format = 'yyyy-MM-dd';
		} else if ($data ['setting'] ['format'] == 'm-d') {
			$format = 'MM-dd';
		} else if ($data ['setting'] ['format'] == 'Y年m月d日 H时i分s秒') {
			$format = 'yyyy年MM月dd日 HH时mm分ss秒';
		} else if ($data ['setting'] ['format'] == 'Y年m月d日 H时i分') {
			$format = 'yyyy年MM月dd日 HH时mm分';
		} else if ($data ['setting'] ['format'] == 'Y年m月d日') {
			$format = 'yyyy年MM月dd日';
		} else if ($data ['setting'] ['format'] == 'm月d日') {
			$format = 'MM月dd日';
		}
		if (empty($format)) $format = 'yyyy-MM-dd'; //默认
		$data['setting']['defaulttype'] = (int)$data['setting']['defaulttype'];
		if (empty($data['value'])) {
			switch ($data['setting']['defaulttype']) {
				case 1:
					$data['value'] = date($data['setting']['format']);
					break;
				case 2:
					$data['value'] = $data['setting']['defaultvalue'];
			}
		} else {
			$value = date($data ['setting'] ['format'],$data ['value']); //当前值
		}
		$name = 'info[' . $data ['field'] . ']';
		return Html::datetime ( $name, $value, $format );
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$config ['type'] = intval ( $config ['defaulttype'] );
		${'defaulttype_checked_' . $config ['defaulttype']} = 'checked';
		if ($config ['defaulttype'] == 0) {
			$defauletype_checked_0 = 'checked';
			$defauletype_checked_1 = '';
			$defauletype_checked_2 = '';
		}
		if ($config ['defaulttype'] == 1) {
			$defauletype_checked_0 = '';
			$defauletype_checked_1 = 'checked';
			$defauletype_checked_2 = '';
		}
		if ($config ['defaulttype'] == 2) {
			$defauletype_checked_2 = 'checked';
		}
		if ($config['format'] == 'Y-m-d H:i:s') {${'format_1'} = 'selected';}
		else if($config['format'] == 'Y-m-d H:i') {${'format_2'} = 'selected';} 
		else if($config['format'] == 'Y-m-d') {${'format_3'} = 'selected';} 
		else if($config['format'] == 'm-d') {${'format_4'} = 'selected';} 
		else if($config['format'] == 'Y年m月d日 H时i分s秒') {${'format_5'} = 'selected';} 
		else if($config['format'] == 'Y年m月d日 H时i分') {${'format_6'} = 'selected';} 
		else if($config['format'] == 'Y年m月d日') {${'format_7'} = 'selected';} 
		else if($config['format'] == 'm月d日') {${'format_8'} = 'selected';} 
		else {${'format_3'} = 'selected';} 
		$html = '
		  <table cellpadding="2" cellspacing="1" bgcolor="#ffffff">
			<tr> 
		      <td><strong>时间格式：</strong></td>
		      <td>			  
			  <select name="info[setting][format]">
			    <option value="Y-m-d H:i:s" '.${'format_1'}.'>' . date ( 'Y-m-d H:i:s' ) . '</option>
			    <option value="Y-m-d H:i" '.${'format_2'}.'>' . date ( 'Y-m-d H:i' ) . '</option>
			    <option value="Y-m-d" '.${'format_3'}.'>' . date ( 'Y-m-d' ) . '</option>
			    <option value="m-d" '.${'format_4'}.'>' . date ( 'm-d' ) . '</option>
			    <option value="Y年m月d日 H时i分s秒" '.${'format_5'}.'>' . date ( 'Y年m月d日 H时i分s秒' ) . '</option>
			    <option value="Y年m月d日 H时i分" '.${'format_6'}.'>' . date ( 'Y年m月d日 H时i分' ) . '</option>
			    <option value="Y年m月d日" '.${'format_7'}.'>' . date ( 'Y年m月d日' ) . '</option>
			    <option value="m月d日" '.${'format_8'}.'>' . date ( 'm月d日' ) . '</option>
			  </select>
			  </td>
		    </tr>
			<tr> 
		      <td><strong>默认值：</strong></td>
		      <td>
			  <input type="radio" name="info[setting][defaulttype]" value="0" ' . $defauletype_checked_0 . '/>无<br />
			  <input type="radio" name="info[setting][defaulttype]" value="1" ' . $defauletype_checked_1 . '/>当前时间<br />
			  <input type="radio" name="info[setting][defaulttype]" value="2" ' . $defauletype_checked_2 . '/>指定时间：<input type="text" name="[info][setting][defaultvalue]" value="" size="22"></td>
		    </tr>
		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表字段
	 * 
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` INT( 10 ) NULL
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}

}

?>