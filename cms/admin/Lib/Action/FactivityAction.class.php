<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FtagAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 活动报名
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 活动报名
 *
 */
class FactivityAction extends FbaseAction {


	/**
	 * 栏目表模型对象
	 *
	 * @var object
	 */
	protected $_category = null;

	/**
	 * 所在栏目数据
	 *
	 * @var array
	 */
	protected $category_data = array();


	/**
	 * @name活动报名管理初始化，主要用户检查操作权限
	 *
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		if (strtolower(MODULE_NAME) == 'factivity') {
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
	 * @name检查权限
	 *
	 * @param $userData
	 * @param $permissions
	 */
	private function checkPermissions($userData,$permissions) {
		if ($userData['username'] == 'developer') {
			return true;
		}
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
	 * @name活动内容管理
	 *
	 */
	public function manage() {
		$in = &$this->in;
		$data = array ();
		$_activity = D ( 'Activity' );
		//表前缀
		//查询条件
		$where = array ();
		$where["catid"] = $in['catid'];
		if ($in['q'] && $in['q'] != '请输入关键字') {
			$in['q'] = urldecode($in['q']);
			$where["{$in['field']}"] = array('like',"%{$in['q']}%");
		}
		//排序条件
		$order = " `sort` ASC, `aid` DESC ";
		//统计模型数量
		$data ['count'] = $_activity->where ( $where )->count ();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] = $_activity->field("`aid`,`thumb`,`attr`,`username`,`catid`,`title`,`start_time`,`end_time`,`sort`,`url`,`status`,`create_time`")->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		//2013-1-15 陈敏 ,新增加thumb,attr,username,status等字段的添加，用于前面信息的调取
		$this->assign ( 'data', $data );
		$this->assign('in', $in);
		$this->display();
	}

	/**
	 * @name录入要显示的内容
	 *
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_add();
		}
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$_activity = D ('Activity');
			//令牌验证
			if (!$_activity->autoCheckToken($in)) $this->message('<font class="red">请不要非法提交或者重复刷新页面！</font>');
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			$in['info']['catid'] = $in['catid'];
			$return = $_activity->add($in['info']);
			if (false !== $return) {
				$this->message('<font class="green">内容保存成功！</font>',U('factivity/manage?catid='.$in['catid']),3,false);
				exit;
			} else {
				$this->message('<font class="red">' . $_activity->getError() . '内容保存失败！</font>');
			}
		}
		$this->display();
	}



	/**
	 * @name更新内容
	 *
	 */
	public function edit() {
		$in = &$this->in;
		if ($in['ajax']) $this->_ajax_edit();
		if ($in['do']) $this->_do_edit();
		if (!$in['aid']) $this->message('<font class="red">参数错误，无法继续该操作！</font>');
		$_activity = D ('Activity');
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			//令牌验证
//			if (!$_activity->autoCheckToken($in))
//				$this->message('请不要非法提交或者重复刷新页面！'); //由于tags参数不能传数组，请将数组参数格式化
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			if (false !== $_activity->save($in['info'])) {
				$this->message('<font class="green">内容更新成功！</font>',U('factivity/manage?catid='.$in['catid']));
			} else {
				$this->message('<font class="red">' . $_activity->getError() . '内容更新失败！</font>');
			}
		}

        $data = $_activity->find($in['aid']);

		$attr_checkboxes = array("top"=> "首页置顶","hot"=>"热点","scroll"=> "图片轮播");
        $data["attr"] = empty($data["attr"]) ? array() : explode(",",$data["attr"] );
		$this->assign('attr_checkboxes',$attr_checkboxes);
		$this->assign('customer_id', $data["attr"]);
		
		$this->assign('data',$data );
		$this->display();
	}


	private function _ajax_edit() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'sort':  //更新排序
				$in['aid'] && $in['aid'] = substr($in['aid'],5);
				$in['sort'] = intval($in['sort']);
				if ($in['sort'] == '0' || !empty($in['sort'])) {
					$_activity = M ('Activity');
					$data = $_activity->find($in['aid']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_activity->save($data)) {
							echo $data['sort'];
							exit ();
						}
					}
				}
				echo '';
				break;
			case 'savetitle':  //更新标题
				$in['aid'] && $in['aid'] = substr($in['aid'],6);
				if (!empty($in['title'])) {
					$_activity = M ('Activity');
					$data = $_activity->find($in['aid']);
					if (is_array($data)) {
						$data['title'] = $in['title'];
						if (false !== $_activity->save($data)) {
							echo $data['title'];
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
	 * @name删除活动信息
	 */
	public function delete() {
		$in = &$this->in;
		if (!$in['aid']) $this->message('<font class="red">没有选择操作项！</font>');
		$_activity = D('Activity');
		if (is_numeric($in['aid'])) {
			$_activity->delete($in['aid']);
			$this->message('<font class="green">删除成功！</font>');
		}
	}


	/**
	 * @name活动报名信息
	 */
	public function manage_apply() {
		$in = &$this->in;
		$data = array ();
		$baoming=array();
		$_activity_apply = D ( 'ActivityApply' );
		//表前缀
		//查询条件
		$where = array ();
		$where["aid"] = $in['aid'];
		//2013-1-15 陈敏 if语句用于判读当前页面下的状态,无状态时则显示所有非回收站的报名信息
		if(isset($in["status"])) 
		{
			$where["status"] = $in['status'];//in为前台设置
			$this->assign('status',$in['status']);
		}
		else
		{
			$where['status']=array('gt','-1');
		}
		if ($in['q'] && $in['q'] != '请输入关键字') {
			$in['q'] = urldecode($in['q']);
			$where["{$in['field']}"] = array('like',"%{$in['q']}%");
		}
		//排序条件
		$order = " `mid` DESC ";
		//统计模型数量
		$data ['count'] = $_activity_apply->where ("`status`<>'-1' AND `aid`=".$in['aid'])->count ();

		//2013-1-15 陈敏 回收站里面的记录条数，判断显示全部删除字段与否
		$data ['jilu'] = $_activity_apply->where ("`status`='-1' AND `aid`=".$in['aid'])->count ();

		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] = $_activity_apply->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		$this->assign ( 'data', $data );
		$this->assign('in', $in);
		$this->display('manage_apply');
	}

	/**
	 * @name添加报名信息
	 */
	public function add_apply() {
		$in = &$this->in;
		$_activity_apply = D ('ActivityApply');
		if ($this->ispost()) {  //录入内容
			//令牌验证
			if (!$_activity_apply->autoCheckToken($in))
				$this->message('请不要非法提交或者重复刷新页面！'); //由于tags参数不能传数组，请将数组参数格式化
			if (!empty($in['info']['reply'])) {
				$in['info']['reply_user_id'] = $_SESSION['userdata']['user_id'];
			}
			if (!empty($in['info']['create_time'])) {
				$in['info']['create_time'] = strtotime($in['info']['create_time']);
			}
			if (!empty($in['info']['reply_time'])) {
				$in['info']['reply_time'] = strtotime($in['info']['reply_time']);
			}
			if (false !== $_activity_apply->add($in['info'])) {
				$this->message('<font class="green">信息添加成功！</font>',U('factivity/manage_apply?catid='.$in['catid'].'&aid='.$in['info']['aid']));
			} else {
				$this->message('<font class="red">' . $_activity_apply->getError() . '信息更新失败！</font>');
			}
		}
		$this->assign('in', $in);
		$this->display('add_apply');
	}

	/**
	 * @name报名信息修改
	 */
	public function edit_apply() {
		$in = &$this->in;
		if ($in['do']) $this->_do_edit();//2013-1-15 陈敏 主要是实现报名信息状态的更改
		$_activity_apply = D ('ActivityApply');
		if ($this->ispost()) {  //录入内容
			//令牌验证
			if (!$_activity_apply->autoCheckToken($in))
			$this->message('请不要非法提交或者重复刷新页面！'); //由于tags参数不能传数组，请将数组参数格式化
			if (!empty($in['info']['reply'])) {
				$in['info']['reply_user_id'] = $_SESSION['userdata']['user_id'];
			}
			if (!empty($in['info']['create_time'])) {
				$in['info']['create_time'] = strtotime($in['info']['create_time']);
			}
			if (!empty($in['info']['reply_time'])) {
				$in['info']['reply_time'] = strtotime($in['info']['reply_time']);
			}
			if (false !== $_activity_apply->save($in['info'])) {
				$this->message('<font class="green">内容更新成功！</font>',U('factivity/manage_apply?catid='.$in['catid'].'&aid='.$in['info']['aid']));
			} else {
				$this->message('<font class="red">' . $_activity_apply->getError() . '内容更新失败！</font>');
			}
		}
		$this->assign('data',$_activity_apply->find($in['mid']));
		$this->assign('in', $in);
		$this->display('edit_apply');
	}

/**
	 * @name操作
	 */
	protected function _do_edit() {
		$in = &$this->in;
		$_c = D ('Activity');
		if (!$in['mid'] && empty($in['info'])) $this->message('<font class="red">未选择操作项！</font>');
		switch ($in['do']) {
			case 'recycle':  //移动到回收站、即 status => '-1'
				if ($in['mid']) {
					$idArr = array($in['mid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['mid'];
				}
				if ($_c->status($idArr,'-1')) {
					$this->message('<font class="green">操作成功！</font>','',3);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'restore': //从回收站还原，还原后为待审状态,sort为1
				if ($in['mid']) {
					$idArr = array($in['mid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['mid'];
				}
				if ($_c->status($idArr,'0')) {
					$this->message('<font class="green">操作成功！</font>','',3);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'status': //标记为已审，待审
				if (!in_array($in['dostatus'],array('0','1'))) $this->message('<font class="red">未选择操作项！</font>');
				if ($in['mid']) {
					$idArr = array($in['mid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['mid'];
				}
				if ($_c->status($idArr,$in['dostatus'])) {
					$this->message('<font class="green">操作成功！</font>','',1);
				} else {
					$this->message('<font class="red">操作失败！</font>');
				}
				break;
			case 'moveto': //移动到其它栏目
				if (!$in['moveto']) $this->message('<font class="red">未选择操作项！</font>');
				if ($in['mid']) {
					$idArr = array($in['mid']);
				} elseif ($in['info'] && !empty($in['info'])) {
					$idArr = &$in['info']['mid'];
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

	public function delete_all()
	{
		$in=$this->in;
		$_c = D ('ActivityApply');
		if($_c->where("`status`='-1' AND `aid`=".$in['aid'])->delete())
		{
		$this->message('<font class="green">操作成功!</font>');
		}
		else
		{
			$this->message('<font class="red">操作失败!</font>');
			}
		}

	/**
	 * @name报名信息删除
	 */
	public function delete_apply() {
		$in = &$this->in;
			 if ($in['mid']) {
			if (is_array($in['mid'])) {
				$where['mid'] = array('in',$in['mid']);
			} else {
				$id = intval($in['mid']);
				$where['mid'] = $id;
			}
			$where['aid'] = intval($in['info']['aid'] ? $in['info']['aid'] : $in['aid']);
			$_activity_apply = D ('ActivityApply');
			if (false !== $_activity_apply->where($where)->delete()) {
				$this->message('<font class="green">删除成功！</font>');
			} 
			else {
				$this->message('<font class="red">删除失败！</font>');
			}
		}
	}
}

	