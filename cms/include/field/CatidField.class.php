<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: CatidField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:22
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 栏目字段操作
// +----------------------------------------------------------------------
class CatidField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '栏目';


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
	 *
	 * @param array $data 字段信息
	 */
	public function form($data = '') {
		//有权限的，当前栏目的所有同类型的栏目的信息
		$_category = D ('Category');
		$categorys = $_category->getSameCategorys($data['value']);
		$arr = array();
		if (is_array($categorys)) {
			foreach ($categorys as $v) {
				$arr[] = array(
					'title' => $v['name'],
					'value' => $v['catid'],
				);
			}
		}
		//所有栏目
		$name = 'info['.$data['field'].']';
		$value = $data['value'] ? $data['value'] : $data['setting']['defaultvalue'];	//选定值
//		$extra = &$data ['formattribute']; //元素额外属性
//		$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
//		$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
		$extra = &$data ['attribute']; //元素额外属性
		return Html::select($name,$arr,$value,$extra);
	}

/**
	 * 配置输出
	 */
	public function setting($config = '') {
		$html = '
		  <table cellpadding="2" cellspacing="1">
			<tr>
		      <td>默认值</td>
		      <td><input type="text" name="info[setting][defaultvalue]" value="'.$config['defaultvalue'].'" size="5"></td>
		    </tr>
		</table>
		';
		return $html;
	}

	/**
	 * 创建物理数据表字段
	 * 栏目
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param $data 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
//		$data['setting'] = unserialize($data['setting']);
		if(!$data['setting']['maxlength']) $data['setting']['maxlength'] = 255;
		$maxlength = min($data['setting']['maxlength'], 255);
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "`
				ADD `{$data['field']}` VARCHAR( {$maxlength} ) NOT NULL
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";
		return $model->query($sql);
	}

}

?>