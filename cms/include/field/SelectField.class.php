<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: SelectField.class.php
// +----------------------------------------------------------------------
// | Date: 2011-01-17 09:21:40
// +----------------------------------------------------------------------
// | Author: 成俊 <cgjp123@163.com>
// +----------------------------------------------------------------------
// | 文件描述: 无限极联动分类字段
// +----------------------------------------------------------------------

class SelectField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var unknown_type
	 */
	public $name = '分类';
	
	/**
	 * 字段的前台页面输出
	 * 都造成下载信息数组
	 * $data[{$fieldname}] = array(
	 * 		'filename' => '',
	 * 		'url' => '',
	 * );
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $field 字段名称
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
			
		return $value;
	}
	
	
	
	/**
	 * 返回顶级分类
	 */
	public function form($data = '') {
		//有权限的，当前栏目的所有同类型的栏目的信息
		$_category = D ('Category');
		//dump($data);
		$categorys = $_category->getTopclassify($data['classify_id']);//得到该目录绑定的分类树的顶级
		$name = 'info['.$data['field'].']';
		$value = $data['value'] ? $data['value'] : 0;
		$extra = &$data ['attribute']; //元素额外属性
		$arr = array();
		$childhtml = array();
		if (is_array($categorys) && !empty($categorys)) {
			$html = "<select id='par_0' name=\"{$name}\" {$extra} >";
			foreach ($categorys as $k=>$v) {
					$arr[] = array(
						'title' => $v['name'],
						'value' => $v['catid'],
					);
					$childhtml[] = self::getchildtree($v,$value);
			}
			$html .= '<option value="0">请选择分类</option>';
			foreach($arr as $k=>$v){
				$html .= '<option value="' . htmlspecialchars ( $v ['value'] ) . '"';
				if ($value == $v ['value']) {
					$html .= ' selected';
				}
				$html .= '>' . htmlspecialchars ( $v ['title'] ) . "&nbsp;&nbsp;</option>\n";
				$html .= $childhtml[$k];
			}	
					//$html .= "</select><select style='display:none;' id=\"{$data['field']}_2\" name=\"{$name}[]\" onclick=\"getnextvalue(this.value,".$data['value'].")\"></select>";
					$html .= "</select>";
		}else{
			$html = "<input type='button' class='dialog' value='点击绑定分类' id='select_box' alt='/admin.php?m=fcontent&a=getclassify&catid=".$data['classify_id']."&TB_iframe=true&height=300&width=500' />";
		}
		return $html;
	}
	
	/*
	*递归调用得所有到子栏目
	*/
	protected function getchildtree($data=array(),$default='',$JG='├'){
		if(!empty($data)){
			$_category = D ('Category');
			$html = '';
			$childdata_array = $_category->field("`name`, `catid`, `parentid`")->where("`parentid`=".$data['catid'])->findAll();
			if(is_array($childdata_array) && !empty($childdata_array)){
				foreach($childdata_array as $k=>$v){
					if($k==(count($childdata_array)-1)){
						//$JG = iconv('UTF-8','UTF-8',substr_replace($JG,'└',0,1));
						$JG = preg_replace('/^├$/','└',$JG);
					}
					$html.="<option value='".$v['catid']."'";
					if($default == $v['catid']){
						$html .= 'selected';
					}
					$html .= " >".$JG.$v['name'];
					$html.="</option>";
					
					$html.=self::getchildtree($v,$default,$JG.'─');
				}
				
			}
			return $html;
		}
		return false;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {		
		$html = '无参数，添加完毕后请在 栏目管理->分类信息 中添加分类详细信息！';
		return $html;
	}
	
	/**
	 * 创建物理数据表
	 * 上传文件
	 * @param object $model 数据表模型对象
	 * @param string $tableName 要操作的数据表
	 * @param mixed 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` SMALLINT( 5 ) NULL 
				DEFAULT '0'
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}
?>