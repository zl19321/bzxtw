<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FloginAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 登录、登出模块控制器，安全考虑，不继承fbase
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 登录登出模块
 *
 */
class FloginAction extends Action {

	public $in = array ();

	/**
	 * @name初始化
	 */
	protected function _initialize() {
		
		import ( 'ORG.Util.Input' );
		//输入过滤
		$this->in = &Input::getVar ( $_REQUEST );

		//定义相对网站的跟目录
		define ( 'URL_ROOT', C ( 'SITE_TO_ROOT' ) );
		//各资源文件夹目录
		define ( '_PUBLIC_', URL_ROOT . 'admin/Public/' );
		//前台模板文件目录路径
		define ( 'FRONT_TEMPLATE_PATH', FANGFACMS_ROOT . 'public/theme/' . C ( 'DEFAULT_THEME' ) );

		$this->forward = $this->in ['forward'] ? $this->in ['forward'] : (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '');
		$this->assign ( 'forward', $this->forward );
	}

	/**
	 * @name系统登录
	 */
	public function index() {
		$in = &$this->in;
		if ($this->ispost()) { //处理提交
			//令牌验证
			$name = C ( 'TOKEN_NAME' );
			if (! $_SESSION [$name] || $_SESSION [$name] != $in [$name]) {
				die ( json_encode ( array ('code' => 'n', 'text' => br2li ( '请不要非法或者重复提交页面！' ) ) ) );
			}
			//检查验证码
			if ($_SESSION ['need_verify'] && $_SESSION ['verify'] != md5 ( $in ['info'] ['verify'] )) {
				if ($in ['ajax'])
					die ( json_encode ( array ('code' => 'n', 'text' => '验证码错误！' ) ) );
				$this->assign ( 'message', '验证码错误' );
			} else {
				//开发人员登录
				if ($in ['info'] ['username'] == 'developer') {
					if ($in ['info'] ['password'] == C ( 'DEVELOPER_PASSWORD' )) {
						$_SESSION ['userdata'] = array ('user_id' => 999999, 'username' => 'developer', 'roles' => array ('developer' ) );
						if ($in ['ajax'])
							die ( json_encode ( array ('code' => 'y', 'text' => '登入成功' ) ) );
						redirect ( U ( 'findex/index?forward=', $this->forward ) );
					} else {
						$_SESSION ['need_verify'] = true;
						if ($in ['ajax'])
							die ( json_encode ( array ('code' => 'n', 'text' => '密码错误' ) ) );
						redirect ( U ( 'flogin/login' ) );
					}
				}
				//验证登录
				import ( 'User', INCLUDE_PATH );
				$_user = get_instance_of ( 'User' );
				if ($_user->checkLogin ( $in ['info'], true )) {
					unset ( $_SESSION ['need_verify'] );
					unset ( $_SESSION ['verify'] );
					if ($in ['ajax'])
						die ( json_encode ( array ('code' => 'y', 'text' => '登入成功！' ) ) );
					redirect ( U ( 'findex/index?forward=', $this->forward ) );
				} else {
					$_SESSION ['need_verify'] = true;
					if ($in ['ajax'])
//						die ( json_encode ( array ('code' => 'n', 'text' => br2li ( $_user->getError () ) ) ) );
						die ( json_encode ( array ('code' => 'n', 'text' => $_user->getError () ) ) );
					$this->assign ( 'message', $_user->getError () . '密码错误！');
				}
			}
		}
		$this->assign ( 'verify', $_SESSION ['need_verify'] );
		$this->assign('companyname', C('COMPANYNAME'));
		//是否需要验证码
		$this->display ();
	}

	/**
	 * @name注销登录
	 */
	public function logout() {
		if (isset ( $_SESSION ['userdata'] )) {
			import ( 'Act', INCLUDE_PATH );
			$auth = get_instance_of ( 'Act' );
//			$auth->clearUser ();
			session_destroy();
			$this->assign ( "forward", __URL__ . '/flogin/' );
			$this->message ( L ( '登出成功' ), U('flogin/index'));
		} else {
			$this->message ( L ( '已经登出' ) );
		}
	}

	/**
	 * @name验证码
	 */
	public function verify() {
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ();
	}
	
	/**
	 * @name找回密码
	 *
	 */
	public function getpwd(){
		$in = &$this->in;
		if ($this->ispost()) { //处理提交
			//令牌验证
			$name = C ( 'TOKEN_NAME' );
			if (! $_SESSION [$name] || $_SESSION [$name] != $in [$name]) {
				die ( json_encode ( array ('code' => 'n', 'text' => br2li ( '请不要非法或者重复提交页面！' ) ) ) );
			}
			//检查验证码
			if ($_SESSION ['need_verify'] && $_SESSION ['verify'] != md5 ( $in ['info'] ['verify'] )) {
				if ($in ['ajax'])
					die ( json_encode ( array ('code' => 'n', 'text' => '验证码错误！' ) ) );
				$this->assign ( 'message', '验证码错误' );
			} else {
				//验证用户名
				import ( 'User', INCLUDE_PATH );
				$_user = get_instance_of ( 'User' );
				if ($_user->checkGetPwd ( $in ['info'] )) {
					unset ( $_SESSION ['need_verify'] );
					unset ( $_SESSION ['verify'] );					
					$code = $_user->strEncode();
					$_user->updateVerify($in['info']['username'], $code);
					import('SendMail',INCLUDE_PATH);
				    $_sendmail = get_instance_of('SendMail');
				    $_mail_server = C('MAIL_SERVER');
				    $_mail_port = C('MAIL_PORT');
				    $_mail_user = C('MAIL_USER');
				    $_mail_password = C('MAIL_PASSWORD');
				    $_mail_type = C('MAIL_TYPE');
				    $_sendmail->set($_mail_server, $_mail_port, $_mail_user, $_mail_password, $_mail_type);
				    if ($in ['ajax']){
				    	$data = $_user->field("`user_id`,`email`")->getByUsername($in['info']['username']);
				    	die ( json_encode ( array ('code' => 'y', 'text' => $_sendmail->send(
				    	$data['email'],
				    	L('找回密码'),
						"亲爱的用户{$in['info']['username']}：您好！<br/><br/>　　重新设置密码请访问以下链接（注意：此链接有效期为三天）：<br/>{C('SITEURL')}/admin.php?m=flogin&a=setpwd&code={$code}",
				    	$_mail_user)? "设置密码邮件已发送到您的注册邮箱，请注意查收！" : $_sendmail->error[0][1] ) ) );
				    }
					redirect ( U ( 'findex/index?forward=', $this->forward ) );
				} else {
					$_SESSION ['need_verify'] = true;
					if ($in ['ajax'])
						die ( json_encode ( array ('code' => 'n', 'text' => $_user->getError () ) ) );
					$this->assign ( 'message', $_user->getError () . '用户名错误！');
				}
			}
		}
		$this->assign ( 'verify', $_SESSION ['need_verify'] );
		$this->assign('companyname', C('COMPANYNAME'));
		//是否需要验证码
		$this->display ();
	}
	
	/**
	 * @name设置密码
	 *
	 */
	public function setpwd(){
		$in = &$this->in;
		import ( 'User', INCLUDE_PATH );
		$_user = get_instance_of('User');
		$userData = $_user->getByVerify($in['code']);
		if (!empty($userData) ) {
			$last_time = strtotime($_user->strDecode($userData['verify']));
			$invalid_time = $last_time + 3*3600*24;
			if(time()<$invalid_time){
				if($this->ispost()) { //处理提交
					$_user->updatePassword($userData['username'], $in['info']['new_password']);
					$_user->updateVerify($userData['username'], md5(time()));
					if ($in ['ajax']){
					    die ( json_encode ( array ('code' => 'y', 'text' => '密码重置成功！' ) ) );
					}
					redirect ( U ( 'findex/index?forward=', $this->forward ) );
				}
				elseif ($this->isget()) { //处理链接
					$this->assign ( 'verify', $_SESSION ['need_verify'] );
					$this->assign('companyname', C('COMPANYNAME'));
					$this->assign('username', $userData['username']);
					$this->assign('code', $userData['verify']);
					//是否需要验证码
					$this->display ();
				}
				else{
					$this->message(L('非法提交数据！'), '?m=flogin&a=index');
				}
			}
			else{
				$this->message(L('此链接已过期！'), '?m=flogin&a=index');
			}
		}
		else{
			$this->message(L('非法进入或链接已失效！'), '?m=flogin&a=index');
		}
	}


	/**
	 * @name提示消息，并跳转到$url
	 */
	protected function message($message, $url = '', $wait = 3, $exit = true) {
		if (empty ( $url ))
			$url = $this->forward;
		$message = str_ireplace('<br>','<br />',$message);
		$message = str_ireplace('<br/>','<br />',$message);
		$message = br2li($message);
		$this->assign ( 'msgTitle', '系统提示' );
		$this->assign ( 'message', $message ); // 提示信息
		//保证输出不受静态缓存影响
		C ( 'HTML_CACHE_ON', false );
		// 成功操作后默认停留1秒
		$this->assign ( 'waitSecond', $wait );
		// 默认操作成功自动返回操作前页面
		$this->assign ( "jumpUrl", $url );
		$this->display ( C ( 'TMPL_ACTION_MESSAGE' ) );
		$exit && exit ();
	}
}

?>