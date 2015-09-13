<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FuserAction.class.php
// +----------------------------------------------------------------------
// | Date: 上午08:40:55
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 前台会员模块
// +----------------------------------------------------------------------

/**
 * @name前台会员模块
 *
 */
class FuserAction extends FbaseAction {
	
	/**
	 * 
	 * @nameUer实例
	 * @var object
	 */
	protected $_mUser = '';
	
	/**
	 * @name初始化
	 * @see FbaseAction::_initialize()
	 */
	protected function _initialize() {
		parent::_initialize();
		$this->_mUser = D ('User','admin');				
	}
	
	/**
	 * @name登录后会员中心主页
	 * 
	 */	
	public function home() {
		$in = &$this->in;
		$data = $this->_mUser->getfuserdata($_SESSION['fuserdata']['user_id']);
		$this->assign('data',$data);
		$seo['seotitle'] = L ('会员面板') . C('SITE_TITLE_SEPARATOR') . L('会员中心') . C('SITE_TITLE_SEPARATOR') . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
	    $seo['url'] = C ('SITEURL') . 'user/home';
	    $this->assign('seo',$seo);
		$this->display('user/home.html');
	}
	
	/**
	 * @name编辑个人资料、包括修改密码
	 * 
	 */
	public function edit() {
		$in = &$this->in;
		//前台用户修改资料
		if ($this->ispost()) {
			unset($in['info']['username']);
			import('User',INCLUDE_PATH);
			$_user = get_instance_of('User');	
			if (! $_user->autoCheckToken ( $in ))
				$this->message ( L ('<font class="red">请不要非法提交或者重复提交页面！</font>') );	
			//前台更新，过滤不可以修改的信息
			$in['info']['user_id'] = $_SESSION['fuserdata']['user_id'];
			unset($in['info']['role_id']);// 角色信息
			unset($in['info']['username']); // 用户名
			unset($in['info']['last_login_ip']); //
			unset($in['info']['last_login_time']); //
			unset($in['info']['create_time']); //
			unset($in['info']['active']); //
			unset($in['info']['login_count']); //
			unset($in['info']['status']); //
			unset($in['info']['isadmin']); //
			if (false !== $_user->update($in['info'])) {
				$this->message(L ('保存成功！'), __ROOT__ . 'user/edit');
			} else {
				$this->message(L ('保存失败！') . $_user->getError(), __ROOT__ . 'user/edit');
			}
		}
		//获取用户详细信息
		$data = $this->_mUser->getfuserdata($_SESSION['fuserdata']['user_id'],true);
		
		$this->assign('data',$data);
		$seo['seotitle'] = L ('个人资料') . C('SITE_TITLE_SEPARATOR') . L('会员中心') . C('SITE_TITLE_SEPARATOR') . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
	    $seo['url'] = C ('SITEURL') . 'user/edit';
		$this->assign('seo',$seo);
		$this->display('user/edit.html');
	}
	
	/**
	 * @name用户忘记密码
	 * 
	 */
	public function forget() {
		$in = &$this->in;
		//获取系统配置
		if ($this->ispost()) {
			if (! M()->autoCheckToken ( $in ))
				$this->message ( L ('<font class="red">请不要非法提交或者重复提交页面！</font>') );
			//TODO 判断用户名与email是否匹配
			import('User',INCLUDE_PATH);
			$_user = get_instance_of('User');
			$_username = trim($in['username']);
			$_email = trim($in['email']);
			$data = $_user->field("`email`")->getByUsername();
			if ($data['email'] == $_email) {
				$_newPwd = rand_string(8,3,'123456789'); //8位  数字+字母  密码			
				//更新密码				
				if ($_user->updatePassword($_username,$_newPwd)) {
					//TODO 发送新密码到邮箱
					import('SendMail',INCLUDE_PATH);
				    $_sendmail = get_instance_of('SendMail');
				    $_mail_server = C('MAIL_SERVER');
				    $_mail_port = C('MAIL_PORT');
				    $_mail_user = C('MAIL_USER');
				    $_mail_password = C('MAIL_PASSWORD');
				    $_mail_type = C('MAIL_TYPE');
				    $_sendmail->set($_mail_server, $_mail_port, $_mail_user, $_mail_password, $_mail_type);
				    if ($_sendmail->send($_email,  // 收件人
				    					  L('找回密码'),  //邮件标题
				    					  L('新密码').$_newPwd,  //邮件内容 
				    					  $_mail_user)    //发件人
				    	) { // 发送成功
				    	$this->message(L('新密码邮件发送到您的邮箱，请注意查收！'), __ROOT__ . '/login.html');
				    }
				}
				$this->message(L('邮件发送失败，请联系管理员解决！'), __ROOT__ . '/forget.html'); 
			} else {
				$this->message(L('用户名密码不匹配'), __ROOT__ . '/forget.html');	
			}
		}
		$seo['seotitle'] = L ('找回密码') . C('SITE_TITLE_SEPARATOR') . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
		$this->assign('seo',$seo);
		$this->display('user/forget.html');
	}
	
	/**
	 * @name注销登录
	 */
	public function logout() {
		if (isset ( $_SESSION ['fuserdata'] )) {
//			import ( 'Act', INCLUDE_PATH );
//			$_act = get_instance_of ( 'Act' );
//			$_act->clearUser ();
			session_destroy();
			$this->assign ( "forward", __ROOT__ . '/login.html' );
			$this->message ( L ( '登出成功' ),  __ROOT__ . '/login.html');
		} else {
			$this->message ( L ( '已经登出' ) );
		}
	}
}