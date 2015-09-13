<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FindexAction.class.php
// +----------------------------------------------------------------------
// | Date: Thu Apr 22 15:43:11 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 前台首页
// +----------------------------------------------------------------------

defined ( 'IN' ) or die ( 'Access Denied' );
/**
 * @name 前台首页
 *
 */
class FindexAction extends FbaseAction {
	
	/**
	 * @name 初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		//检查 REQUEST_FILE
		if (REQUEST_FILE != 'index' . C('URL_HTML_SUFFIX')) {
            if(!method_exists($this,ACTION_NAME)) {
                $this->h404();
            }
		}
	}
	
	/**
	 * @name空模块
	 * 
	 */
	public function _empty() {
		if (ACTION_NAME == '_empty') {
			$this->h404();
		} else {  //分发到index
			$this->index();
		}
	}

	/**
	 * @name系统首页
	 * 
	 */
	public function index() {
		$in = &$this->in;	
		
		$content = M('content');
		$infocont = $content->where("catid=22")->LIMIT(0,6)->order('create_time desc')->SELECT();
		$this->assign('infocont',$infocont);	
		//站点信息：站点标题、关键字、描述
		$seo['seotitle'] = C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');		
	    $seo['url'] = C ('SITEURL');
		$this->assign('seo',$seo);
		$this->display('index.html');
	}
	
	/**
	 * @name验证码
	 */
	public function verify() {
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ();
	}
	

	/**
	 * @name用户注册
	 * 
	 */
	public function register() {		
		$in = &$this->in;
		if (!C('USER_OPEN_REGISTER')) { // 检查是否开启前台用户注册
			$this->message('系统暂未开放用户注册功能！');
		}
		if ($in['ajax']) $this->_ajax_register();
		if ($this->ispost()) {
			$in['info']['role_id'] = $role_id = C('USER_DEFAULT_ROLE_ID');  //赋予用户默认角色
			import('User', INCLUDE_PATH);
			$_user = get_instance_of('User');
			if (!$_user->autoCheckToken($in)) 
				$this->message(L('请不要非法提交或者重复提交页面！')); 
			if (C('USER_NEED_ACTIVE')) $in['info']['status'] = '0';  //置为未激活状态
			else $in['info']['status'] = '1';  //无需验证，直接激活
			$result = $_user->register($in['info'],$role_id,0);
			if (true === $result) {
				if (C('USER_NEED_ACTIVE')) { //需要激活					
					$this->message( L('下一步，到邮箱激活即可！'), __ROOT__);
				} else {  //不需要激活					
					$this->message( L('恭喜您，注册成功！'), __ROOT__ . '/login.html');
				}
			} else {
				$this->message( L('注册失败！') . $_user->getError());
			}
		}
		$url = array_search(array('Findex','register'), C('_routes_'));
		!empty($url) && $seo['url'] = C ('SITEURL') . '/' . $url;
		$seo['seotitle'] = L('用户注册') . C('SITE_TITLE_SEPARATOR') .C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
		$this->assign('forward',$seo['url']);
		$this->assign('seo',$seo);		
		$this->display('user/register.html');
	}
	
	
	/**
	 * @name处理注册时候的ajax请求
	 * 输出json字符串
	 */
	protected function _ajax_register() {
		$in = &$this->in;
		import('User', INCLUDE_PATH);
		$_user = get_instance_of('User');
		switch ($in['do']) {
			case 'checkusername':
				$username = trim($in['info']['username']);
				if (!$_user->getByUsername($username)) {
					die('true');
				} else {
					die('false');
				}	
				break;
			case 'checkemail':
				$email = trim($in['info']['email']);
				if (!$_user->getByEmail($email)) {
					die('true');
				} else {
					die('false');
				}				
				break;
			default:
				break;
		}
	}
	
	/**
	 * @name登入系统
	 * 
	 */
	public function login() {
		$in = &$this->in;
		if (!empty($_SESSION['fuserdata'])) { //已经登录，无需重复登录
			$this->message(L ('已经登录，无需重复登录！'), __ROOT__ . '/user/home');
		}
		
		if ($this->ispost()) { //处理提交
			//令牌验证
			$name = C ( 'TOKEN_NAME' );
			if (! $_SESSION [$name] || $_SESSION [$name] != $in [$name]) {
				$this->message(L ('请不要非法或者重复提交页面'), __ROOT__ . '/user/home');				
			}
			//检查验证码
			if ($_SESSION ['need_verify'] && $_SESSION ['verify'] != md5 ( $in ['info'] ['verify'] )) {
				$this->assign ( 'message', L ('验证码错误') );
			} else {				
				//验证登录
				import ( 'User', INCLUDE_PATH );
				$_user = get_instance_of ( 'User' );
				if ($_user->checkLogin ( $in ['info'], false )) {
					unset ( $_SESSION ['need_verify'] );
					unset ( $_SESSION ['verify'] );
					$this->message(L('登录成功！'), __ROOT__ . '/user/home');					
				} else {
					$_SESSION ['need_verify'] = true;					
					$this->assign ( 'message', $_user->getError () . L ('密码错误！'));
				}
			}
		}
		$this->assign ( 'verify', $_SESSION ['need_verify'] );		
		$url = array_search(array('Findex','login'), C('_routes_'));
	    !empty($url) && $seo['url'] = C ('SITEURL') . '/' . $url;
		$seo['seotitle'] = L('用户登录') . C('SITE_TITLE_SEPARATOR') .C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');	
		$this->assign('seo',$seo);
		$this->display('user/login.html');
	}

	/**
	 * @name网站地图
	 *
	 */
	public function sitemap() {
	    $in = &$this->in;
	    $_category = D ('Category','admin');	    
	    $url = array_search(array('Findex','sitemap'), C('_routes_'));
	    !empty($url) && $seo['url'] = C ('SITEURL') . '/' . $url;
	    $seo['seotitle'] = L('网站地图') . C('SITE_TITLE_SEPARATOR') .C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');	
	    //TODO 生成sitemap的ul li标签
	    $data = '';
	    $this->assign('data',$data);
	    $this->assign('seo',$seo);
	    $this->display('sitemap.html');
	}
	

    /**
	 * @name链接重定向
	 *
	 */
    public function redirect() {
        $in = &$this->in;
        if(!$in['u']) $this->h404();
        $url = urldecode($in['u']);
        redirect($url);
        exit;
    }

	 /**
	 * @name链接重定向
	 *
	 */
    public function form() {
		$seo['seotitle'] = C('SEOTITLE');
		$this->assign('seo',$seo);
		$this->display('form.html');
    }

    /**
     * 组图
     */
    public function image(){
    	$in = &$this->in;
    	$picture = M('content_picture');
    	$images = $picture->field('images,content')->where('cid='.$in['cid'])->select();
    	eval("\$imgs=".$images[0]['images'].";");
    	$arr = null;
    	foreach ($imgs as $key => $value) {
    		$arr['image'][$key] = $value[1];
    		$arr['name'][$key] = $value[2];
    		$arr['key'] = $key+1;
    	}
    	$arr['cont'] = $images[0]['content'];
    	echo json_encode($arr);exit();
    }
    /**
     * 留言回复
     */
    public function rely(){
    	$in = &$this->in;
    	$guestbook = M('guestbook');
    	$res = $guestbook->where(" code = '".$in['code']."' ")->select();
    	$rs = $res['0']['reply'];
    	echo json_encode($rs);exit();
    }
    /**
     * 查找文件
     */
    public function searchfile(){
    	$in=&$this->in;
    	$model = new model;
    	$file = $model->query("SELECT * from fangfa_content con LEFT JOIN (select * from fangfa_content_download) down on down.cid = con.cid where con.catid='107' and con.title like '%".$in['key']."%'");
    	for ($i=0; $i <count($file) ; $i++) { 
    		$file[$i]['create_time'] = date("m-d",$file[$i]['create_time']);
    	}
    	echo json_encode($file);exit();
    }
}
?>