<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: NumberField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:25
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 数字字段操作
// +----------------------------------------------------------------------
class NumberField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '数字';
	
	
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		return intval($value);
	}
	
	/**
	 * 数字，后台表单表示html
	 */
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' size="'.$data['setting']['size'].'"';
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性
		return Html::input ( $name, $value, $extra );
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		
		$config['minnumber'] = $config['minnumber'] > 1 ? $config['minnumber'] : 1;
		$config['maxnumber'] = intval($config['maxnumber']);
		$config['decimaldigits'] = $config['decimaldigits'] > 0 ? intval($config['decimaldigits']) : '-1';
		if($config['decimaldigits'] > 0) {
			${'selected_'.$config['decimaldigits']} = 'selected';
		} else {
			${'selected_auto'} = 'selected';
		}		
		$html = '
		  <table cellpadding="2" cellspacing="1">
			<tr> 
		      <td>取值范围</td>
		      <td><input type="text" name="info[setting][minnumber]" value="'.$config['minnumber'].'" size="5"> - <input type="text" name="info[setting][maxnumber]" value="'.$config['maxnumber'].'" size="5"></td>
		    </tr>
			<tr> 
		      <td>小数位数：</td>
		      <td>
				<select name="info[setting][decimaldigits]">
				  <option value="-1" '.${'selected_auto'}.'>自动</option>
				  <option value="0" '.${'selected_0'}.'>0</option>
				  <option value="1" '.${'selected_1'}.'>1</option>
				  <option value="2" '.${'selected_2'}.'>2</option>
				  <option value="3" '.${'selected_3'}.'>3</option>
				  <option value="4" '.${'selected_4'}.'>4</option>
				  <option value="5" '.${'selected_5'}.'>5</option>
				</select>
		      </td>
		    </tr>
			<tr> 
		      <td>默认值</td>
		      <td><input type="text" name="info[setting][defaultvalue]" value="'.$config['defaultvalue'].'" size="40"></td>
		    </tr>
		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 数字
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
        
        //修改不能创建数字物理字段的bug  fangfa  2013-1-30
        $num = $data ['setting'] ['decimaldigits']; 
        $data ['setting'] ['decimaldigits'] = $num <= 0 ? 'INT' : 'FLOAT';  
        if($data['setting'] ['decimaldigits'] == 'INT'){
            $where =   $data ['setting'] ['maxnumber'];
        }else{
            $where =   $data ['setting'] ['maxnumber'].','.$num;
        }
        
		$data ['setting'] ['minnumber'] = intval ( $data ['setting'] ['minnumber'] );
		$data ['setting'] ['defaultvalue'] = $data ['setting'] ['decimaldigits'] == 0 ? intval ( $data ['setting'] ['defaultvalue'] ) : floatval ( $data ['setting'] ['defaultvalue'] );
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` " . $data ['setting'] ['decimaldigits'] . "(" . $where . ") NOT NULL ";

		return $model->query ( $sql );
	}
}

?>