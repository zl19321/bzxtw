<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FloginAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-8-16
// +----------------------------------------------------------------------
// | Author: Mark 
// +----------------------------------------------------------------------
// | 文件描述: 电子报刊
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 登录登出模块
 *
 */
class FmagazineAction extends FbaseAction {

	protected $_category = '';
	protected $category_data = '';
	/**
	 * @name内容管理初始化，主要用户检查操作权限
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		$cat = F("category_".$in['catid']);
		$this->assign("cat",$cat);
		if (strtolower(MODULE_NAME) == 'fmagazine') {
			if ($in['catid']) { //检查栏目操作权限
				$this->_category = D ('Category');
				$this->category_data = $this->_category->find((int)$in['catid']);
				//$this->assign('cat',$this->category_data);
				//$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
			} else {
				$this->message('<font class="red">没有选择要操作的栏目~！</font>');
			}
		}
	}
	
	public function manage(){
		$in = &$this->in;
		$_magazine = D ( 'Magazine' );
		//表前缀
		$db_pre = C('DB_PREFIX');
		$where = array ();
		$where["{$db_pre}magazine.catid"] = $in['catid'];
		$data = $_magazine->where($where)->order("sort asc")->select();
		
		import ( 'Tree', INCLUDE_PATH );
		$parent_id = 0;
		$tree = get_instance_of ( 'Tree' );
		$tree->init ( $data );
		$str = "<tr>
				  <!--<td><input type='checkbox' rel='checkbox' name='info[id][]' value='\$id' /></td>-->
				  <td class='editable_sort pointer' id='sort_\$id'>\$sort</td>
				  <td>\$id</td>
				  <td>\$spacer\$title\$is_images</td>
				  <td>\$create_time&nbsp;</td>
				  <td>\$update_time&nbsp;</td>
				  <td><!--\$access | -->\$edit | \$editiamges | \$delete</td>
				</tr>";
		$categorys_option = $tree->get_tree ( 0, $str);
		$this->assign ( 'html',$categorys_option );	//已有分类
		$this->display();
	}
	
 	public function add(){
		$in = &$this->in;
		$_magazine = D('Magazine');
		
		if ($in['dosubmit']) {
			if (false === $_magazine->add($in['info'])) {
			 	$this->message($_magazine->error, $this->forward);
			}else {
			 	$this->message("添加杂志成功", U("fmagazine/manage?catid=".$in['catid']));
			}
		}
		import ( 'Tree', INCLUDE_PATH );
		$parent_id = 0;
		$tree = get_instance_of ( 'Tree' );
		$categorys = $_magazine -> field(" `id`,`title`,`parentid`") -> where("`catid`=".$in['catid'])->findAll();
		$tree->init ( $categorys );
		$str = "<option value='\$id' \$selected>\$spacer\$title</option>\n";
		$categorys_option = $tree->get_tree ( 0, $str, $parent_id);
		$this->assign ( 'html',$categorys_option );	//已有分类
		$this->display();
 	}
 	
 	public function edit(){
 		$in = &$this->in;
 		if ($in['ajax']) { $this->_edit_sort(); }
		$_magazine = D ( 'Magazine' );
 		//删除一个杂志及子级杂志。
 		if (isset($in['do']) && $in['do'] == "delete" ) {
 			$parentid = $_magazine->where("id=".$in['id'])->getField("parentid");
 			if (false == $_magazine -> del($parentid, $in['catid'], $in['id'])) {
			 	$this->message($_magazine->error, $this->forward);
			}else {
			 	$this->message("删除杂志成功！", U("fmagazine/manage?catid=".$in['catid']));
			}
 		}
 		if ($in['dosubmit']) {
			if (false === $_magazine->where("id=".$in['info']['id'])->save($in['info'])) {
			 	$this->message($_magazine->error, $this->forward);
			}else {
			 	$this->message("修改杂志成功", U("fmagazine/manage?catid=".$in['catid']));
			}
 		}
 		import ( 'Tree', INCLUDE_PATH );
		$parent_id = 0;
		$tree = get_instance_of ( 'Tree' );
		$categorys = $_magazine -> field(" `id`,`title`,`parentid`") -> where("`catid`=".$in['catid'])->findAll();
		$tree->init ( $categorys );
		$str = "<option value='\$id' \$selected>\$spacer\$title</option>\n";
		$categorys_option = $tree->get_tree ( 0, $str, $parent_id);
		$this->assign ( 'html',$categorys_option );	//已有分类
		
 		$data = $_magazine->where("id=".$in['id']." and catid=".$in['catid'])->find();
 		$this->assign("data",$data);
 		$this->display();
 	}
 	
 	protected function _edit_sort(){
 		$in = &$this->in;
 		$in['id']  = substr($in['mid'],5);
		$in['sort'] = intval($in['sort']);
		if ($in['sort'] == '0' || !empty($in['sort'])) {
			$_magazine = M ('Magazine');
			$data = $_magazine->find($in['id']);
			if (is_array($data)) {
				$data['sort'] = $in['sort'];
				if (false !== $_magazine->save($data)) {
					echo $data['sort'];
					exit ();
				}
			}
		}
		echo '';
 	}
 	
 	public function editimages(){
 		$in = &$this->in;
 		$_magazine = D("Magazine");
 		if ($in['do']) {
 			$this->image();
 			exit();
 		}
 		if($in['dosubmit']){
 			if( false === $this->deal_image() ){
			 	$this->message("修改导图失败", $this->forward);
			}else {
			 	$this->message("修改导图成功！", U("fmagazine/manage?catid=".$in['catid']));
 			}
 		}
 		$data = $_magazine->where("id=".$in['id'])->find();
 		if( empty($data['images']) ){
 			$this->message("没有上传杂志封面，请上传杂志图片！",U("fmagazine/edit?id=".$data['id']."&catid=".$data['catid']));
 		}
 		$data['images'] = __ROOT__."/". C("UPLOAD_DIR").$data['images'];
 		$this->assign("data",$data);
 		$this->display();
 	}
 	/**
 	 * 编辑杂志图片
 	 * 处理提交过来的数据，保存
 	 * */
 	private function deal_image(){
 		$in = &$this->in;
 		//得到关联的文章 cid
 		preg_match_all('/href=\"([^\"]+?)\"/',$in['html_container'],$match);
 		$cid = array();
 		if(!empty($match[1])){
 			foreach ($match[1] as $t){
	 			$path = pathinfo($t);
	 			$cid[] = $path['filename'];
	 		}
	 		$cid = implode(",", $cid);
 		}
 		
 		$_magazine = D("Magazine");
 		
 		$bool = $_magazine->where("id=".$in['info']['id'])->save(array('img_map'=>$in['html_container'],'content_id'=> $cid));
 	
 		if ($bool) {
 			$this->message("编辑成功！",U("fmagazine/manage?catid=".$in['info']['catid']));
 		}else {
 			$this->message("编辑失败！",U("fmagazine/manage?catid=".$in['info']['catid']));
 		}
 		
 	}
 	/**
 	 * 得到该栏目下的所有文章
 	 * 输出页面，供图片地址选择链接
 	 * */
 	public function image(){
 		$in = &$this->in;
		$_content = D("Content c");
		$db_pre = C('DB_PREFIX');
		$c = $_content->join("{$db_pre}category ct on c.catid=ct.catid")->field("c.cid, c.url, c.title, ct.catdir")->where("c.catid=".$in['catid'])->findAll();
		$r = array();
		foreach ($c as $t){
			$r[]= "{'id':".$t['cid'].",'url':'". __ROOT__."/".$t['catdir']."/".$t['url']."','title':'".$t['title']."'}";
		}
		if (!empty($t)) {
			echo "[".implode(",",$r)."]";
			exit();
		}else {
			echo "";
		}
	}
	
	/**************************文章管理***********************/
	
	public function page_manage() {
		$in = &$this->in;
		$data = array ();
		$_content = D ( 'Content' );
		//表前缀
		$db_pre = C('DB_PREFIX');
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
	public function page_add() {
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
				$this->message('<font class="green">内容保存成功！</font>',U('fmagazine/page_manage?catid='.$in['catid']),3,false);
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
	public function page_edit() {
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
			if (false !== $_content->update($in['info'],$in['info']['cid'])) {				
				$this->message('<font class="green">内容更新成功！</font>',U('fmagazine/page_manage?catid='.$in['catid']));
				//应用扩展，实现ping(ping服务),clear_html(更新静态页面)
				tag('after_content_add', serialize($_content->get($in['cid'], 'all')));
			} else {
				$this->message('<font class="red">' . $_content->getError() . '内容更新失败！</font>');
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
	public function page_delete() {
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

?>