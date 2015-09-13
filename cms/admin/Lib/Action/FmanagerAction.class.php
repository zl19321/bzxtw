<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FmanagerAction.class.php
// +----------------------------------------------------------------------
// | Date: 2011-1-30
// +----------------------------------------------------------------------
// | Author: 孙斌 <sunyichi@163.com>
// +----------------------------------------------------------------------
// | 文件描述:  管理员管理
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name RBAC访问控制设置
 *
 */
class FmanagerAction extends FbaseAction {
	
	/**
	 * User类对象
	 * @var object
	 */
	protected $_user = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		import('User',INCLUDE_PATH);//导入User类
		//所有管理员角色
		$this->_user = get_instance_of('User');
		$roles = D('Role')->field("`role_id`,`nickname`")->where("`status`='1' and `isadmin`='1'")->order("`role_id` ASC ")->findAll();
		$this->assign('roles',$roles);
	}
	
	/**
	 * @name 管理员管理
	 *
	 */
	public function manage() {
		$in = &$this->in;
		$where = array();
		if (true !== C('USER_PASSPORT_ON')) {
			$_user = M ('User');
			//表前缀
			$db_pre = C('DB_PREFIX');
			$data = array ();
			//查询条件
			$where = array ();
			$where["{$db_pre}user.isadmin"] = 1;
			if ($in['role_id']) {
				$where["{$db_pre}role_user.role_id"] = $in['role_id'];
			}
			if ($in['q'] && $in['q'] != '请输入关键字') {
				if($in['type']=='rolename')$where["{$db_pre}role.name"] = $in['q'];
				else if($in['type']=='rolenickname')$where["{$db_pre}role.nickname"] = $in['q'];
				else $where["{$db_pre}user." . $in['type']] = $in['q'];
			}
			//排序条件
			if ($in['order']) {
				$order = "{$db_pre}user.`user_id` DESC";
			} else {
				$order = "{$db_pre}user.`user_id` DESC";
			}
			//left join 表 role_user 后 left join role
			$join_role_user = "`{$db_pre}role_user`
					 		  ON ({$db_pre}role_user.user_id={$db_pre}user.user_id)";
			$join_role = "`{$db_pre}role`
					 	 ON ({$db_pre}role_user.role_id={$db_pre}role.role_id)";
			//查找字段
			$field = "{$db_pre}user.*,
					  {$db_pre}role.nickname AS `rolenickname`,
					  {$db_pre}role.name AS `rolename`";
			//统计管理员数量
			$data ['count'] = $_user->join($join_role_user)->join($join_role)->where ( $where )->count ();			
			//初始化分页类
			$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
			$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );			
			//分页代码
			$data ['pages'] = $Page->show ();			
			//当前页数据
			$data ['info'] = $_user->field($field)->join($join_role_user)->join($join_role)->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		} else { //处理通行证模式
			//TODO
		}
		//IP->地址
		if (is_array($data['info'])) {
			import('ORG.Net.IpArea');
			$_ipArea = new IpArea(INCLUDE_PATH . '/ipdata/QQWry.Dat');
			foreach ($data['info'] as $k=>$v) {
				$data['info'][$k]['last_login_location'] = $_ipArea->get($v['last_login_ip']);
			}
		}
		$this->assign ('data', $data);
		$this->assign('in', $this->in);
		$this->display();
	}
	
	/**
	 * @name添加管理员
	 * 
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) $this->_add_ajax();
		//获取要显示的字段，及其相关属性
		$field_data = D('ModelField')->where("`modelid`='{$in['modelid']}' AND `systype`<>'2' AND `status`='1'")->order('`sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,array(),array("repassword"=>'equalTo="#info_password"', "username"=>'remote="'.U('fmanager/add?ajax=checkusername').'"', "email"=>'remote="'.U('fmanager/add?ajax=checkemail').'"'));
		$this->assign( 'form_data', $form );
		//添加进数据库
		if ($this->ispost()) {
			$_user = &$this->_user;
			if (!$_user->autoCheckToken($in)) { //令牌验证
				$this->error ( '请不要非法提交或者重复提交页面！</font>' );
			}
			if (false === $_user->insert($in['info'],(int)$in['info']['isadmin'])) {
				$this->message('<font class="red">' . $_user->getError() . '管理员添加失败！</font>');
			} else {
				$this->message('<font class="green">管理员添加成功！</font>', U("fmanager/manage") );
			}
		}
		$this->assign ( 'modelid', $in['modelid'] );
		//TODO
		$this->display();
	}

	/**
	 * @name处理添加用户时候的ajax请求
	 * 
	 */
	protected function _add_ajax() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'checkusername':
				$_user = D ('User');
				if (true === $_user->validate($in['info'],'username')) {
					die('true');
				} else {
					die('false');
				}
				break;
			case 'checkemail':
				$_user = D ('User');
				if (true === $_user->validate($in['info'],'email')) {
					die('true');
				} else {
					die('false');
				}
				break;
			default:
				;
		}
		exit();
	}
	
	/**
	 * @name编辑管理员
	 * 
	 */
	public function edit() {
		$in = &$this->in;
		$_user = &$this->_user;
		if ($in['do'] == 'status') {
			$this->status();
		}
		
		if ($this->ispost()) {
			if (!$in['info']['user_id']) {
				$this->message('<font class="red">参数错误，没有选择要修改的管理员！</font>');
			}
			if ($in['info']['user_id'] == '1') $this->message('<font class="red">操作终止，不能更改【管理员】状态！</font>');
			if (!$_user->autoCheckToken($in)) { //令牌验证
				$this->error ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			}
			if (false === $_user->update($in['info'],(int)$in['info']['isadmin'])) {
				$this->message('<font class="red">' . $_user->getError() . '管理员信息修改失败！</font>');
			} else {
				$this->message('<font class="greeen">管理员信息修改成功！</font>', U('fmanager/manage') );
			}
		}
		if (!$in['user_id']) {
			$this->message('<font class="red">参数错误，没有选择要修改的管理员！</font>');
		}
		if ($in['user_id'] == 1) {
			$this->message('<font class="red">操作终止，不能更改【管理员】状态！</font>');
		}
		//获取管理员详情
		$data = $_user->getUserData($in['user_id'], true);
		$data['password'] = '';
		//获取要显示的字段，及其相关属性
		$role_user_data = D('RoleUser')->where("`user_id`='{$in['user_id']}'")->find();
		$role_data = D('Role')->find($role_user_data['role_id']);
		$field_data = D('ModelField')->where("`modelid`='{$role_data['modelid']}' AND `systype`<>'2' AND `status`='1'")->order('`sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,$data,array("repassword"=>'equalTo="#info_password"', "username"=>'readonly="readonly"', "email"=>'readonly="readonly"'));
		$this->assign( 'form_data', $form );
		$this->assign( 'data',$data );
		$this->display();
	}
	
	/**
	 * @name修改管理员状态
	 * 
	 */
	protected function status() {
	$in = &$this->in;
		$_user = &$this->_user;
		if (!$in['user_id']) {
			 $this->message('<font class="red">参数错误，没有指定删除项！</font>');
		}
		if (is_numeric($in['user_id'])) {
			if ($in['user_id'] == '1') $this->message('<font class="red">操作终止，不能更改【管理员】状态！</font>');
		}
		if (is_array($in['user_id'])) {
			if (in_array('1',$in['user_id']))  $this->message('<font class="red">操作终止，不能更改【管理员】状态！</font>');
		}
		if (false === $_user->status($in['user_id'],$in['status'])) {
			$this->message('<font class="red">' . $_user->getError() . '操作发生错误！</font>',U('fmanager/manage'));
		} else {
			redirect(U('fmanager/manage'));
		}
		exit ();
	}
	
	/**
	 * @name删除管理员
	 * 
	 */
	public function delete() {
		$in = &$this->in;
		$_user = &$this->_user;
		if (!$in['user_id']) {
			 $this->message('<font class="red">参数错误，没有指定删除项！</font>');
		}
		if (is_numeric($in['user_id'])) {
			if ($in['user_id'] == '1') $this->message('<font class="red">操作终止，【管理员】无法删除！</font>');
		}
		if (is_array($in['user_id'])) {
			if (in_array('1',$in['user_id']))  $this->message('<font class="red">操作终止，【管理员】无法删除！</font>');
		}
		if (true === $_user->delete($in['user_id'])) {
			$this->message('<font class="red">操作成功！</font>',U('fmanager/manage'));
		} else {
			$this->message('<font class="red">' . $_user->getError() . '操作发生错误！</font>',U('fmanager/manage'));
		}
		//TODO
		$this->display();
	}
}
?>