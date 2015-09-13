<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:42
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: TAG字段操作
// +----------------------------------------------------------------------
class TagField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = 'TAG';
	
	/**
	 * 将TAG格式化输出
	 * 编程ul > li 形式的数据 
	 * @param string $field 字段名称
	 * @param array $setting 字段配置信息
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */
	public function  output ($field, $config, &$data) {
		
	}
	
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		$return = array();
		if (!empty($value)) {
			$value = htmlspecialchars($value);
			$return = explode(' ',$value);
			if (is_array($return)) {
				foreach ($return as $k=>$v) {
					if (empty($v)) unset($return[$k]);
				}
			}
		}
		return $return;
	}
	
	
	/**
	 * 后台表单输出
	 */
	public function form($data = '') {
		//获取所有的推荐位
		$html = '';
		if (empty($GLOBALS['loadFiles']['autocomplete'])) {	//自动完成需要加载的外部文件
			$loadFiles = '<script language="javascript" src="' . _PUBLIC_ . 'js/autocomplete/jquery.autocomplete.min.js"></script>'."\n";
			$loadFiles .= '<link rel="stylesheet" type="text/css" href="' . _PUBLIC_ . 'js/autocomplete/jquery.autocomplete.css" />'."\n";
			$html .= $loadFiles;
			$GLOBALS['loadFiles']['autocomplete'] = $loadFiles;
		}
		$name = 'info[' . $data ['field'] . ']';
		if (is_array($data['value'])) {
			$value_arr = array();
			foreach ($data['value'] as $v) {
				$v['name'] && $value_arr[] = $v['name'];
			}
			!empty($value_arr) && $value = implode(',',$value_arr);
		} else {
			$value = $data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		}
		$extra = &$data ['attribute']; //元素额外属性//		
		$extra .= ' id="tag_'.$data['fieldid'].'"';//		
		$html .= Html::input ( $name, $value, $extra );
		//要发送到入口文件的参数
		$extraParams = array(
			'm' => 'ftag',
			'a' => 'manage',
			'ajax' => 'autocomplete'
		);
		$html .= '
		<script type="text/javascript">		
		$().ready(function() {				
			$("#tag_'.$data['fieldid'].'").autocomplete("'.__APP__.'", {
				max: 4,
				highlight: false,
				multiple: true,
				multipleSeparator: " ",
				scroll: true,
				extraParams: '.json_encode($extraParams).',
				scrollHeight: 300,
				minChars : 1,					
				formatResult: function(row) {
					return row[0];
				}
			});
		});		
		</script>
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
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		return true;
	}
	
	
}

?>