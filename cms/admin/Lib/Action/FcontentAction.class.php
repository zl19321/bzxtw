<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ContentAction.class.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 15:26:46 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 内容管理模块，静态模块
// +----------------------------------------------------------------------

//文档状态： 0=待审,9=终审,-1=回收站

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 内容管理模块
 *
 */
class FcontentAction extends FbaseAction {

	/**
	 * 栏目表模型对象
	 * @var object
	 */
	protected $_category = '';

	/**
	 * 栏目数据
	 * @var array
	 */
	protected $category_data = '';


	/**
	 * @name内容管理初始化，主要用户检查操作权限
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		if (strtolower(MODULE_NAME) == 'fcontent') {
			if ($in['catid']) { //检查栏目操作权限
				$this->_category = D ('Category');
				$this->category_data = $this->_category->find((int)$in['catid']);
				$this->assign('cat',$this->category_data);
				$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
			} else {
				$this->message('<font class="red">没有选择要操作的栏目~！</font>');
			}
		}
	}

	/**
	 * 检查权限
	 * @param $userData
	 * @param $permissions
	 */
	private function checkPermissions($userData,$permissions) {
		if ($userData['username'] == 'developer') {
			return true;
		}
//		dump($userData);dump($permissions);
		$has = false;
		if (is_array($userData['roles'])) {
			foreach ($userData['roles'] as $v) {  //如果有一个角色有权限   那就有权限
				if (in_array($v,$permissions[ACTION_NAME])) $has = true;
			}
		}
		if (!$has) {
			$this->message('<font class="red">无权访问！</font>');
		}
		return true;
	}

	/**
	 * @name内容管理、列表
	 */
	public function manage() {
		$in = &$this->in;
		$data = array ();
		$_content = D ( 'Content' );
		//表前缀
		$db_pre = C('DB_PREFIX');
        //是否启用评论
        $this->assign('is_comment',C('IS_COMMENT'));
        //是否启用剪裁功能
        $this->assign('is_cut',C('IS_CUT'));
        
		//查询条件
		$where = array ();
		$where["{$db_pre}content.catid"] = $in['catid'];
		if (isset($in['status'])) {
			$where["{$db_pre}content.status"] = $in['status'];
			$this->assign('status',$in['status']);
		} else {  //显示所有非回收站里的内容
			$where["{$db_pre}content.status"] = array('gt', '-1'); 
		}
		if ($in['q'] && $in['q'] != '请输入关键字') {
			$in['q'] = urldecode($in['q']);
			$where["{$db_pre}content.{$in['field']}"] = array('like',"%{$in['q']}%");
		}
		//排序条件
		$order = " {$db_pre}content.`sort` ASC,{$db_pre}content.`cid` DESC ";
		//join  count表
		$join = "{$db_pre}content_count ON ";
		$join .= "{$db_pre}content.cid={$db_pre}content_count.cid ";
		//field
		$field = "{$db_pre}content.*";
		$field .= ",{$db_pre}content_count.`hits`,{$db_pre}content_count.`comments`,{$db_pre}content_count.`comments_checked`";
		//统计模型数量
		$data ['count'] = $_content->join($join)->where ( $where )->count ();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] = $_content->field($field)->join($join)->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		if (is_array($data ['info'])) {
			foreach ($data ['info'] as $k=>$v) {
				$data['info'][$k]['url'] = $_content->getUrl($v);
			}
		}
		//同类型分类下拉列表json
		$categorys = $this->_category->getSameCategorys($this->category_data['catid']);
		$arr = array();
		if (is_array($categorys)) {
			foreach ($categorys as $k=>$v) {
				$arr[$v['catid']] = $v['name'];
				$categorys[$k]['id'] = $v['catid'];
			}
			$this->assign('cat_json',json_encode($arr));
			//树形栏目下拉框
			import('Tree',INCLUDE_PATH);
			$_tree = get_instance_of('Tree');
			$_tree->init ( $categorys );
			$str = "<option value='\$catid'>\$spacer\$name</option>";
			$this->assign('category_tree',$_tree->get_tree ( 0, $str ));
		}
		$this->assign ( 'data', $data );
		$this->assign('in', $in);
		$this->display();
	}

	/**
	 * @name录入要显示的内容
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_add();
		}
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$_content = D ('Content');
			//令牌验证
			if (!$_content->autoCheckToken($in)) $this->message('<font class="red">请不要非法提交或者重复刷新页面！</font>');
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			$return = $_content->add($in['info']);
			if (false !== $return) {				  
				$this->message('<font class="green">内容保存成功！</font>',U('fcontent/manage?catid='.$in['catid']),3,false);
				//应用扩展，实现ping(ping服务),clear_html(更新静态页面),由于tags参数不能传数组，请将数组参数格式化
				tag('after_content_add', serialize($_content->get($return,'all')));
				exit;
			} else {
				$this->message('<font class="red">' . $_content->getError() . '内容保存失败！</font>');
			}
		}
		$_mField = D('ModelField');
		$field_data = $_mField->where("`modelid`='{$cat_data['modelid']}'  AND `systype`<>'2'  AND `status`='1' ")->order(' `sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,array("catid"=>$in['catid'],"classify_select"=>$in['catid']));
		$this->assign('form_data',$form);
		$this->assign('catid',$in['catid']);
		$this->display();
	}

	/**
	 * @name添加内容时候的ajax请求
	 */
	protected function _ajax_add() {
		$in = &$this->in;
		$cat_data = $this->category_data;
		switch ($in['ajax']) {
			case 'check_title':
				$_content = D ('Content');
				if ($_content->titleExist($in['c_title'])) {
					die('此标题已经被使用过！');
				} else {
					die('此标题还没有被使用过！');
				}
				break;
			default:
				break;
		}
	}

	/**
	 * @name更新内容
	 */
	public function edit() {
		$in = &$this->in;
		if ($in['ajax']) $this->_ajax_edit();
		if ($in['do']) $this->_do_edit();
		if (!$in['cid']) $this->message('<font class="red">参数错误，无法继续该操作！</font>');
		$_content = D ('Content');
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$in['info']['cid'] = &$in['cid'];
			//令牌验证
//			if (!$_content->autoCheckToken($in)) $this->message('请不要非法提交或者重复刷新页面！');,由于tags参数不能传数组，请将数组参数格式化
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
            $in['info']['images']   =   is_array($in['info']['images'])?$in['info']['images']:'';
			if (false !== $_content->update($in['info'],$in['info']['cid'])) {				
				$this->message('<font class="green">内容更新成功！</font>',$in['forward']);
				//应用扩展，实现ping(ping服务),clear_html(更新静态页面)
				tag('after_content_add', serialize($_content->get($in['cid'], 'all')));
			} else {
				$this->message('<font class="red">' . $_content->getError() . '内容更新失败！</font>',$in['forward']);
			}
		}
		//获取信息详细内容
		$data = $_content->get($in['cid'],'all');
		$_mField = D('ModelField');
		$field_data = $_mField->where("`modelid`='{$cat_data['modelid']}'  AND `systype`<>'2'  AND `status`='1' ")->order(' `sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,$data,array('url'=>'readonly="readonly"'));
		$this->assign('form_data',$form);
		$this->assign('data',$data);
		$this->display();
	}

	/**
	 * @name 处理编辑的AJAX请求
	 */
	private function _ajax_edit() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'sort':  //更新排序
				$in['cid'] && $in['cid'] = substr($in['cid'],5);
				$in['sort'] = intval($in['sort']);
				if ($in['sort'] == '0' || !empty($in['sort'])) {
					$_content = M ('Content');
					$data = $_content->find($in['cid']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_content->save($data)) {
							echo $data['sort'];
							exit ();
						}
					}
				}
				echo '';
				break;
			case 'savetitle':  //更新标题
				$in['cid'] && $in['cid'] = substr($in['cid'],6);
				if (!empty($in['title'])) {
					$_content = M ('Content');
					$data = $_content->find($in['cid']);
					if (is_array($data)) {
						$data['title'] = $in['title'];
						if (false !== $_content->save($data)) {
							echo $data['title'];
							exit ();
						}
					}
				}
				echo '';
				break;
			case 'updatetime':  //更改 更新时间
				$in['cid'] && $in['cid'] = substr($in['cid'],5);
				if (!empty($in['updatetime'])) {
					$_content = M ('Content');
					$data = $_content->find($in['cid']);
					if (is_array($data)) {
						$data['update_time'] = strtotime($in['updatetime']);
						if (false !== $_content->save($data)) {
							echo date('Y-m-d',$data['update_time']);
							exit ();
						}
					}
				}
				echo '';
				break;
			case 'category':  //更换栏目
				$in['cid'] && $in['cid'] = substr($in['cid'],9);
				if (!empty($in['new_catid'])) {
					if ($in['catid'] == $in['new_catid']) {
						echo $this->category_data['name'];
						exit ();
					} else { //更新
						$_content = M ('Content');
						$data = $_content->field("`cid`,`catid`")->find($in['cid']);
						if (is_array($data)) {
							$data['catid'] = $in['new_catid'];
							if (false !== $_content->save($data)) {
								$category_data = F ('category_' . $in['new_catid']);
								echo $category_data['name'];
								exit ();
							}
						}
					}
				}
				echo '';
				break;
			case 'getclassify':
				if($in['cid']){
					$category = D('Category')->getTreeByParentId($in['cid']);
					if(!empty($category['child'])){
						$html = '';
						foreach($category['child'] as $k=>$v){
							$html .= '<option value="'.$v['catid'];
							if($k==1){
								$html .= 'selected';
							}
							$html .= '">'.$v['name'].'</option>';
						}
						die($html);
					}
					die('false');
				}
				die('false');
				break;
			default:
				break;
		}
		exit ();
	}
	
	/**
	 * @name获取分类信息
	 */
	public function getclassify(){
		$in = &$this->in;
		if($in['dosubmit']){
			if($in['topcatid'] && $in['catid']){
				if(false !== D('Category')->settopcatid($in['catid'],$in['topcatid'])){
						echo '<script>alert("提交成功!")</script>';
						//$this->message('提交成功！');
					}else{
						echo '<script>alert("提交失败!")</script>';
						//$this->message('提交失败！');
					}
			}
			return false;
		}
		if($in['catid']){
			$category = D('Category')->field("`modelid`")->where("`catid`=".$in['catid'])->find();
			if(!empty($category)){
				$classify = D('Category')->field("`catid` as 'id', `parentid`, `name`, `topcatid`")->where("`cattype`='cla' AND `modelid`=".$category['modelid'])->findAll();
				if(empty($classify)) die('没有查到分类数据');
				foreach($classify as $k=>$v){
					if(!empty($classify[$k]['topcatid'])){
						$tid_array = split(',',$classify[$k]['topcatid']);
						if(in_array($in['catid'],$tid_array)){
							$classify[$k]['checked'] = 'checked';
						}
					}
				}
				$this->assign('catid',$in['catid']);
		 		$this->assign ( 'data', $classify );
				$this->display('select_classify');
			}
			return false;
		}
		return false;
	}

	/**
	 * @name操作
	 */
	protected function _do_edit() {
		$in = &$this->in;
		$_c = D ('Content');
		if (!$in['cid'] && empty($in['info'])) $this->message('<font class="red">未选择操作项！</font>');
		switch ($in['do']) {
			case 'recycle':  //移动到回收站、即 status => '-1'
				if ($in['cid']) {
					$idArr = array($in['cid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['cid'];
				}
				if ($_c->status($idArr,'-1')) {
					$this->message('<font class="green">操作成功！</font>','',1);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'restore': //从回收站还原，还原后为待审状态,sort为1
				if ($in['cid']) {
					$idArr = array($in['cid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['cid'];
				}
				if ($_c->status($idArr,'0')) {
					$this->message('<font class="green">操作成功！</font>','',1);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'status': //标记为已审，待审
				if (!in_array($in['dostatus'],array('0','9'))) $this->message('<font class="red">未选择操作项！</font>');
				if ($in['cid']) {
					$idArr = array($in['cid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['cid'];
				}
				if ($_c->status($idArr,$in['dostatus'])) {
					$this->message('<font class="green">操作成功！</font>',U('fcontent/manage?catid='.$in['catid']),1);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'moveto': //移动到其它栏目
				if (!$in['moveto']) $this->message('<font class="red">未选择操作项！</font>');
				if ($in['cid']) {
					$idArr = array($in['cid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['cid'];
				}
				if ($_c->moveto($idArr,$in['moveto'])) {
					$this->message('<font class="green">操作成功！</font>','',1);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			default:
				break;
		}
		exit ();
	}

	/**
	 * @name彻底删除信息
	 */
	public function delete() {
		$in = &$this->in;
		if (!$in['cid']) $this->message('<font class="red">没有选择操作项！</font>');

		$_c = D('Content');
		if (is_numeric($in['cid'])) {
			$_c->delete(array($in['cid']));
			$this->message('<font class="green">删除成功！</font>');
		} elseif ($in['cid'] == 'all') { //清空回收站
			$_c->delete('all', $in['catid']);
			$this->message('<font class="green">删除成功！</font>');
		}
		//删除内容后的操作：包括更新相关列表页实体文件，删除详细页实体文件，更新首页实体文件
		tag('after_content_delete');
	}


}