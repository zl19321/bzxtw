<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FdbAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 个人信息处理
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 个人信息处理
 *
 */
class FprofileAction extends FbaseAction {
	
	protected $needAuth = false;
	
	/**
	 * 用户操作类对象
	 */
	
	protected $_user = '';
	
	/**
	 * 用户信息
	 * @var unknown_type
	 */
	protected $profile_data = array();
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		//验证权限
		if ($in['user_id'] && ! (int)$_SESSION['userdata']['user_id'] ) {
			$in['user_id'] = $_SESSION['userdata']['user_id'];
		}
		import('User', INCLUDE_PATH);
		$this->_user = get_instance_of('User');
		$this->profile_data = $this->_user->getUserData($in['user_id']);
	}
	
	/**
	 * @name编辑个人资料
	 */	
	public function edit() {
		$in = &$this->in;
		$_user = &$this->_user;
		if (!$_SESSION['userdata']['user_id']) {
			$this->message('<font class="red">你还没有登录，请登录！</font>', U('flogin/index'));
		}
		if ($_SESSION['userdata']['username'] == 'developer') {
			$this->message('<font class="red">操作终止，不能更改此管理员信息！</font>', U('findex/home'));
		}
		if ($this->ispost()) {
			if (!$_user->autoCheckToken($in)) { //令牌验证
				$this->error ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			}
			if (false === $_user->update($in['info'],(int)$in['info']['isadmin'])) {
				$this->message('<font class="red">' . $_user->getError() . '个人资料修改失败！</font>');
			} else {
				$this->message('<font class="greeen">个人资料修改成功！</font>', U('fprofile/edit') );
			}
		}
		$data = $_user->getUserData($_SESSION['userdata']['user_id']);
		$this->assign('data',$data);
		$this->display();
	}
	
	/**
	 * @name修改个人密码
	 */
	public function pwd() {
		extract($this->in);
		$_user = &$this->_user;
		if (!$_SESSION['userdata']['user_id']) {
			$this->message('<font class="red">你还没有登录，请登录！</font>', U('flogin/index'));
		}
		if ($_SESSION['userdata']['username'] == 'developer') {
			$this->message('<font class="red">操作终止，不能更改此管理员信息！</font>', U('findex/home'));
		}
		if ($this->ispost()) {
			if (!$_user->autoCheckToken($_POST)) { //令牌验证
				$this->error ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			}
			if ($_user->changePassword($_SESSION['userdata']['username'], $old_password, $password)) {
				$this->message('<font class="green">密码修改成功！</font>', U('fprofile/pwd'));
			} else {
				$this->error('<font class="red">密码修改失败，请重新修改！</font>');
			}
		}
		$this->display();
	}
	
	/**
	 * @name注销登录
	 */
	public function logout() {
		unset($_SESSION);
		session_destroy();
		$this->message('<font class="green">退出成功,请重新登录！</font>', U('/flogin/index'));
	}
}
