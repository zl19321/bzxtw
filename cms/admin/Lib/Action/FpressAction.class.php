<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FvoteAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-1-2
// +----------------------------------------------------------------------
// | Author: 成俊<cgjp123@163.com>
// +----------------------------------------------------------------------
// | 文件描述:  投票
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 投票管理
 *
 */
class FpressAction extends FbaseAction
{
	protected $_category = '';
	protected $category_data = '';
	/**
	 * @name初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'Press';
		if (strtolower(MODULE_NAME) == 'fpress') {
			if ($in['catid'] || $in['info']['catid']) { //检查栏目操作权限
				$this->_category = D ('Category');
				$this->category_data = $this->_category->find( (int)$in['catid'] );
				$this->assign('cat',$this->category_data);
				//print_r($this->category_data);
				//$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
			} else {
				$this->message('<font class="red">没有选择要操作的栏目~！</font>');
			}
		}
	}


	/**
	 * @name投票列表
	 */
	public function manage()
	{
		$in = &$this->in;
		$_press = D( $in['_tablename'] );
		$order = "sort asc";
		$data['count'] = $_press ->  count();
		
		import("ORG.Util.Page"); 	
		$Page = new Page ( $data['count'], 20);
		$data['pages'] = $Page->show ();

		$data['info'] = $_press -> field("pid,catid,thumb,url,sort,title,attr,create_time,update_time,status")->order($order)->limit ( $Page->firstRow . ',' . $Page->listRows ) -> select ();

		$this->assign("data", $data);
		$this->display();
	}

	/**
	 * @name添加投票
	 */
	public function add() {
		
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_add();
		}
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$_press = D ('Press');
			$return = $_press->add($in['info']);
			if (false !== $return) {			  
				$this->message('<font class="green">内容保存成功！</font>',U('fpress/manage?catid='.$in['info']['catid']),3,false);
				exit;
			} else {
				$this->message('<font class="red">' . $_press->getError() . '内容保存失败！</font>');
			}
		}
		$_mField = D('ModelField');
		$field_data = $_mField->where("`modelid`='{$cat_data['modelid']}'  AND `systype`<>'2'  AND `status`='1' ")->order(' `sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,array("catid"=>$in['catid'],"classify_select"=>$in['catid']));
	
		$this->assign('form_data',$form);
		$this->assign('catid',$in['catid']);
		$this->display();
	}

	public function edit() {
		$in = &$this->in;
		if (empty($in['info']['pid']) && empty($in['pid'])) $this->message('<font class="red">参数错误，无法继续该操作！</font>');
		if($in['ajax'] && $in['ajax'] == "sort"){
			$this->_edit_sort();
		}
		$_press = D ('Press');
		$_mField = D('ModelField');
		$cat_data = $this->category_data;
		
		import('Field',INCLUDE_PATH);  // 进行处理
		$fieldArr = $_mField->field("`fieldid`")->where(" `status`='1' AND `modelid`='".$cat_data['modelid']."'")->findAll();
		$field = array();  // 栏目模型所有字段的信息
		foreach ($fieldArr as $k=>$v) {
			$value = F ('modelField_'.$v['fieldid']);
			if (is_array($value)) {  //组合成 array($formtype1=>'',$formtype2=>'',$formtype3=>'',)的形式
				$field[$value['field']] = $value;
			}
		}
		if($in['dosubmit']){
			$bool = $_press->update($in['info']);
			//$bool = $_press -> data($data)->add();
			if($bool){
				$this->message("内容更新成功！",U("fpress/manage?catid=".$in['info']['catid']));
			}
			else {
				$this->message($_press->error, $this->forward);
			}
		}
		$data = $_press->where("pid=".$in['pid']." and catid=".$in['catid'])->find();
		foreach ($field as $k=>$v)  {
			Field::output($v['formtype'],$k, $v['setting'],$data);
		}
		$_mField = D('ModelField');
		$field_data = $_mField->where("`modelid`='{$cat_data['modelid']}'  AND `systype`<>'2'  AND `status`='1'")->order(' `sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,$data,array('url'=>'readonly="readonly"'));
		$this->assign('form_data',$form);
		$this->assign('data',$data);
		$this->display();
	}
	
	public function status_model(){
		$in = &$this->in;
		if( empty($in['pid']) ) {
			$this->message("没有编号！");
		}
		$_press = D("Press");
		$_press -> where("pid=".$in['pid'])->data( array("status" => $in['status']  ))-> save( );
		
		redirect(U("fpress/manage?catid=".$in['catid']));
	}
	 
	
	public function delete()
	{
		$in = &$this->in;
		$_model = D('Press');
		if (!$in['pid'] || !$in['catid']) {
			$this->message("参数错误", $this->forward);
		}
		$bool = $_model->where('pid ='.$in['pid']." and catid=".$in['catid'])->delete();
		if($bool){
			$this->message("操作成功！",U("fpress/manage?catid=".$in['catid']));
		}
		else {
			$this->message("操作失败！",U("fpress/manage?catid=".$in['catid']));
		}
	}
	
	protected function _edit_sort(){
 		$in = &$this->in;
 		$in['pid']  = substr($in['pid'],5);
		$in['sort'] = intval($in['sort']);
		if ($in['sort'] == '0' || !empty($in['sort'])) {
			$_press = M ('Press');
			$data = $_press->find($in['pid']);
			if (is_array($data)) {
				$data['sort'] = $in['sort'];
				if (false !== $_press->save($data)) {
					echo $data['sort'];
					exit ();
				}
			}
		}
		echo '';
 	}
}
?>
