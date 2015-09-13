<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TemplateField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:48:49
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 文档属性字段操作
// +----------------------------------------------------------------------
class AttrField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '文档属性';
	
	//文档属性
	private $attr = array(
		'top' => '首页置顶',
		'hot' => '热点',
		'scroll' => '图片轮播'
	);
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		$attr = array_flip ($this->attr);
		if (is_array($value) && !empty($value)) {
			$value = array_intersect($value,$attr);  //过滤无用属性，非$this->attr 中的属性值将被过滤掉
		}
		if (!empty($value) && is_array($value)) {
			$value = implode(',', $value);
		}
		return (string)$value;
	}
	
	
	
	/**
	 * 文档属性
	 */
	public function form($data = '') {
		$name = 'info[' . $data ['field'] . ']';
		$value = &$data ['value'] ? $data ['value'] : $data ['setting'] ['defaultvalue']; //当前值
		$extra = &$data ['attribute']; //元素额外属性
		//可用属性
		$html = '';
		if (!empty($data['setting']['show']) && is_array($data['setting']['show'])) {
			foreach ($data['setting']['show'] as $v) {
				if (isset($this->attr[$v])) {
					$checked = '';
					if (!empty($value)) {
						if (in_array($v, $value)) {
							$checked = 'checked';
						}						
					}
					$html .= '<input type="checkbox" name="info[attr][]" value="'.$v.'" '.$checked.' /> ' . $this->attr[$v];
				}
			}			
		}
		return $html;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		if (is_array($config['show']) && !empty($config['show'])) {
			foreach ($config['show'] as $v) {
				${'show_'.$v} = 'checked';
			} 
		} elseif (empty($config) && !is_array($config)) {  //默认全选
			foreach ($this->attr as $v) {
				${'show_'.$v} = 'checked';
			}
		}
		$html = '可用属性：';
		foreach ($this->attr as $k=>$v) {
			$html .= '<input type="checkbox" name="info[setting][show][]" value="'.$k.'" '.${'show_top'}.'/> '.$v;
		}		
		return $html;
	}
	
	
	/**
	 * 创建物理数据表
	 * 模板
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
	    return ;
	}
}

?>