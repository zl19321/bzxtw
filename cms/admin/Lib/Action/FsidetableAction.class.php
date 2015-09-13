<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: sidetableAction.class.php
// +----------------------------------------------------------------------
// | Date: 2012-12-20
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 副表管理模块，静态模块
// +----------------------------------------------------------------------

//文档状态： 0=待审,9=终审,-1=回收站

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 内容管理模块
 *
 */
class FsidetableAction extends FbaseAction {

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
     * 
     * 栏目副表表名及副表后缀
     * @var string
     * 
     */
    protected $table_db_name = 'sidetable_'; 
    
    protected $table_db_name_suffix = '';


	/**
	 * @name内容管理初始化，主要用户检查操作权限
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		if (strtolower(MODULE_NAME) == 'fsidetable') {
			if ($in['catid']) { //检查栏目操作权限
				$this->_category = D ('Category');
				$this->category_data = $this->_category->find((int)$in['catid']);
				$this->assign('cat',$this->category_data);
				$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
                $this->table_db_name .= $this->category_data['model']['exttable'];
                $this->table_db_name_suffix = $this->category_data['model']['exttable'];
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
		$_sidetable = M ( $this->table_db_name );		
		//表前缀
		$db_pre = C('DB_PREFIX');
		//查询条件
		$where = array ();
		$where["{$db_pre}{$this->table_db_name}.catid"] = $in['catid'];
		//统计模型数量
		$data ['count'] = $_sidetable->where ( $where )->count ();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
        
        //fangfa 2012-12-29 副表列表页字段自定义显示
        $_field = M('ModelField');        
		//当前页数据
		$data ['info'] = $_sidetable->where ( $where )->order ("sort asc,id asc")->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();  
        //查询字段自定义
		$this->assign('category_data',$this->category_data);
		$this->assign('tablename',$this->category_data['model']['tablename']);
        $this->assign ('data', $data );
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
			$_sidetable = M ($this->table_db_name);
			//令牌验证
			if (!$_sidetable->autoCheckToken($in)) $this->message('<font class="red">请不要非法提交或者重复刷新页面！</font>');
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
            //过滤时间参数
            foreach($in['info'] as $k=>$v){
                if(strpos($k,'_time')){
                    $in['info'][$k] = strtotime($v);
                }
            } 
			$return = $_sidetable->add($in['info']);
			if (false !== $return) {				  
				$this->message('<font class="green">内容保存成功！</font>',$in['forward']);
				//应用扩展，实现ping(ping服务),clear_html(更新静态页面),由于tags参数不能传数组，请将数组参数格式化
				//tag('after_content_add', serialize($_content->get($return,'all')));
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
		$_sidetable = M ($this->table_db_name);
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
            //过滤时间参数
            foreach($in['info'] as $k=>$v){
                if(strpos($k,'_time')){
                    $in['info'][$k] = strtotime($v);
                }
            } 
			if (false !== $_sidetable->data($in['info'])->where('id'.' = '.$in['info']['id'])->save()) {		
            	$this->message('<font class="green">内容更新成功！</font>',$in['forward']);
			} else {
			echo $_sidetable->getLastSql();
			exit;
				$this->message('<font class="red">内容更新失败！</font>',$in['forward']);
			}
		}
		//获取信息详细内容
		$data = $_sidetable->where('id = '.$in[$this->table_db_name_suffix.'_id'])->find();
		$_mField = D('ModelField');
		$field_data = $_mField->where("`modelid`='{$cat_data['modelid']}'  AND `systype`<>'2'  AND `status`='1' ")->order(' `sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,$data,array('url'=>'readonly="readonly"'));
		$this->assign('form_data',$form);
		$this->assign('data',$data);
        $this->assign('name_field',$this->category_data['model']['exttable'].'_name');
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
					$table = trim($in['sitetable']);
					$_content = M ($table);
					$data = $_content->find($in['id']);
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
					$table = trim($in['sitetable']);
					$_content = M ($table);
					$data = $_content->find($in['id']);
					if (is_array($data)) {
						$data['name'] = $in['title'];
						if (false !== $_content->save($data)) {
							echo $data['name'];
							exit ();
						}
					}
				}
				echo '';
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
		if (!$in[$this->category_data['model']['exttable'].'_id']) $this->message('<font class="red">没有选择操作项！</font>');
		$_sidetable = M($this->table_db_name);
		if (is_numeric($in[$this->category_data['model']['exttable'].'_id'])) {
			$mess = ($_sidetable->where( 'id' . ' = ' . $in[$this->category_data['model']['exttable'].'_id'])->delete()) ? "删除成功" : "删除失败";
			$this->message('<font class="green">'.$mess.'！</font>');
		} elseif ($in['all'] == '1') { //批量删除 
            $array_id = implode(',',$in[$this->category_data['model']['exttable'].'_id']);
			$mess = ($_sidetable->where( 'id' . ' in ( ' . $array_id . ' )')->delete()) ? "删除成功" : "删除失败";
			$this->message('<font class="green">'.$mess.'！</font>');
		}
		//删除内容后的操作：包括更新相关列表页实体文件，删除详细页实体文件，更新首页实体文件
		tag('after_content_delete');
	}


}