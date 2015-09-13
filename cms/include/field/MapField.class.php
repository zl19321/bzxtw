<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: BoxField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:03
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 选项字段操作
// +----------------------------------------------------------------------
class MapField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '百度地图';
		
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		$arr = $this->format($config['option']);
		$valueArr = array();
		if (is_array($arr)) {
			foreach ($arr as $v) {
				$valueArr[] = $v['value'];
			}
		}
		$result = array();  //修改日期  2011-7-20 马东
		if (is_array($value)) {
			foreach ($value as $t ){
				if (in_array($t, $valueArr)) {
					$result[] = $t;
				}
			}
			return implode(",",$result);
		}else {
			//if (!in_array($value, $valueArr)) {
			//	return '';
			//} else {
			//return $value;
			//}
			return $value;
		}
	}
	
		private function format($option) {
		$setting_option = explode ( "\n", $option );
		$return = array();
		if (is_array ( $setting_option )) {
			//获取值
			foreach ( $setting_option as $k => $v ) {
				$v = ltrim($v);				
				list ( $return [$k] ['title'], $return [$k] ['value'] ) = explode ( '|', trim($v) );
			}
		}
		return $return;
		}
	
	/**
	 * 选项
	 * 
	 * @param array $data 字段信息
	 */
	public function form($data = '') {
		$html = '';
		$ak=$data['setting']['ak']?$data['setting']['ak']:'';
		$type=$data['setting']['map_type'];
		$width=$data['setting']['width']?$data['setting']['width']:'750';
		$height=$data['setting']['height']?$data['setting']['height']:'550';
		$level=$data['setting']['map_level'];
		$field=$data['field']?$data['field']:'';
		$value=$data['value'];
		if ($ak && $field){
			$html .= Html::map($ak,$type,$width,$height,$field,$value,$level);
			return $html;
		}
		return false;
	}
	
	/**
	 * 配置输出
	 * config setting里面的配置选项
	 */
	public function setting($config = ''){
		$html = '
		  <table cellpadding="2" cellspacing="1">
		  <tr>
		  <td>百度地图ak</td>
		  <td><input type="input" name="info[setting][ak]" value="'.$config['ak'].'" style="width:220px"></td>
		  </tr>
		  <tr>
		  <td width="100px" id="show">地图类型选择</td>
		  <td>
		  <select name="info[setting][map_type]" id="check_type">
			<option value="1" selected="selected">单点</option>
		  </select>
		  </td>
		  </tr>
		  <td>参数</td>
		  <td>宽度:<input type="input" name="info[setting][width]" style="width:50px" value="'.$config['width'].'">&nbsp;px&nbsp;&nbsp;
		  高度:<input type="input" name="info[setting][height]" style="width:50px" value="'.$config['height'].'">&nbsp;px
		  </td>
		  <tr>
		  </tr>
		  <tr>
		  <td>地图显示等级</td>
		  <td>
		  <select name="info[setting][map_level]">';
		  if($config['map_level']==12)
		  $html.='<option value="12" selected="selected">市</option>';
		  else{$html.='<option value="12">市</option>';}
		  if($config['map_level']==8)
		  $html.='<option value="8" selected="selected">省</option>';
		  else{$html.='<option value="8">省</option>';}
		  if($config['map_level']==4)
		  $html.='<option value="4" selected="selected">国</option>';
		  else{$html.='<option value="4">国</option>';}
		  $html.='</select>
		  </td>
		  </tr>
		</table>
		';
		return $html;
	}
	/**
	 * 创建物理数据表字段，选项
	 * 
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (!$model || empty ( $tableName ))
		return false;
		$sql="ALTER TABLE `".C ( 'DB_PREFIX' ).$tableName."` ADD `{$data['field']}` VARCHAR( 255 ) NOT NULL";
		return $model->query ( $sql );
	}
}

?>