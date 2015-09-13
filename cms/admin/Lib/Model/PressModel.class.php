<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagModel.class.php
// +----------------------------------------------------------------------
// | Date: 2011-8-16
// +----------------------------------------------------------------------
// | Author: Mark
// +----------------------------------------------------------------------
// | 文件描述: 报刊杂志Model
// +----------------------------------------------------------------------

class PressModel extends Model{

	/**
	 * 数据表名
	 * @var string
	 */
	protected $tableName = 'press';

	/**
	 * 分类ID
	 * @var int
	 */
	public $_catid = '';

	/**
	 * 模型ID
	 * @var int
	 */
	public $_modelid = '';

	/**
	 * 模型的字段信息数组
	 * @var array
	 */
	public $_field_data = array();
	//错误信息
 	public $error = '';
 	
 	public function init($catid) {
		if (!$catid) return false;
		$this->_catid = $catid;
		$category_data = F ('category_'.$catid);
		$modelid = $category_data['modelid'];
		$this->_modelid = $modelid;
		if (!$modelid) {
			$this->error .= "ID为{$modelid}的模型缓存信息被破坏，请更新模型缓存！";
			return false;
		}
		// 调用各字段类处理提交的内容
		$fieldArr = D("ModelField")->field("`fieldid`")->where(" `status`='1' AND `modelid`='{$modelid}'")->findAll();
		$field = array();  // 栏目模型所有字段的信息
		foreach ($fieldArr as $k=>$v) {
			$value = F ('modelField_'.$v['fieldid']);
			if (is_array($value)) {  //组合成 array($formtype1=>'',$formtype2=>'',$formtype3=>'',)的形式
				$field[$value['field']] = $value;
			}
		}
		$this->_field_data = $field;
	}
	
	public function setting($data = array(),$catid = '', $parentid= '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fcontent';
		//调用相应的widget
		$html = W ( 'CategoryPressAdd', $data, true );
		return $html;
	}
	
	protected  function _before_insert(&$data){
		if ( empty($data['catid']) ) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		/*if (!empty($data['url'])) { //url链接地址处理
			$data['url'] = date('Ym/') . $data['url'] . C ('URL_HTML_SUFFIX');
		}*/
		//默认seo信息处理
		if (empty($data['seotitle'])) {
			$data['seotitle'] = trim($data['title']) . ' - {stitle}';
		}
		if (empty($data['seokeywords'])) {
			$data['seokeywords'] = $data['title'];
		}
		if (empty($data['seodescription'])) {
			$data['seodescription'] = $data['description'];
		}
		if ((int)$data['sort'] == 0) {
			$data['sort'] = 1;
		}
		//print_r($data);exit();
	}
	
	protected function _before_update(&$data){
		$this-> _before_insert ( $data );
	}
	protected function _after_find(&$data){
		if (!empty($data['attr'])) {
			$data['attr'] = explode(",", $data['attr']);
		}
	}
	protected function _after_select(&$data){
		foreach ($data as $k => $t){
			$data[$k]['attr'] = explode(",",$t['attr']);
		}
	}
	
	
	public function update($data) {
		if (!$data['pid']) {
			$this->error .= '数据接收错误，没有指定要修改的信息！';
			return false;
		}
		if (!$data['catid']) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		if ($this->_catid !== $data['catid']) {
			$this->init($data['catid']);
		}
		/*if (!$this->validate($data,$data['catid'])) {  //数据验证
			return false;
		}*/
		$field_data = $this->_field_data;
		//文档属性为必须字段，当表单没有传递数据的时候，我们需要手动设定为空值
		!isset($data['attr']) && $data['attr'] = '';
		import('Field',INCLUDE_PATH);  // 进行处理
		foreach ($data as $k=>$v) { // 对数据进行处理
			$data[$k] = Field::add($field_data[$k]['formtype'],$k,$v,$field_data[$k]['setting']);
		}
		//提交到基础表content
		if(empty($data['update_time'])){
			$data['update_time'] = time();  //更新时间
		}
		if (false !== parent::save($data)) {
			if (empty($data['url'])) {
				parent::execute("UPDATE ".C("DB_PREFIX")."press set `url`='".date('Ym')."/".$data['pid']. C ('URL_HTML_SUFFIX') ."' WHERE `pid`='".$data['pid']."' LIMIT 1");
			}
			return $data['pid'];
		}
		return false;
	}

	
	
	public function add($data) {
		if (!$data['catid']) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		if ($this->_catid !== $data['catid']) {
			$this->init($data['catid']);
		}
		
		$field_data = $this->_field_data;
		import('Field',INCLUDE_PATH);  //进行处理
		foreach ($data as $k=>$v) { //对数据进行处理			
			$data[$k] = Field::add($field_data[$k]['formtype'],$k,$v,$field_data[$k]['setting']);
		}
		//摘要处理
		if (empty($data['description'])) {
			$text = html2txt(strip_tags(trim($data['content'])));
			if (!empty($text)) {
				$data['description'] = msubstr($text,0,160);
			}
		}
		
		//提交到基础表content
		$cid = parent::add($data);
		if (false !== $cid) {
			if (empty($data['url'])) {
				parent::execute("UPDATE ".C("DB_PREFIX")."press set `url`='".date('Ym')."/".$cid. C ('URL_HTML_SUFFIX') ."' WHERE `pid`='".$cid."' LIMIT 1");
			}
			return $cid;
		}
		return false;
	}
	

}
?>