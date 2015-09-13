<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: BaseAction.class.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 13:44:16 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: condition
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 基础类
 *
 */
class FbaseAction extends Action {
	/**
	 * 请求来源
	 *
	 * @var string
	 */
	public $forward = '';

	/**
	 * 搜集变量、包括 $_GET、$_POST、$_COOKIE
	 * @var array
	 */
	public $in = array ();

	/**
	 * 权限验证对象
	 * @var object
	 */
	public $_auth = '';

	/**
	 * 系统版本
	 * @var string
	 */
	public $version = '4.2';

	/**
	 * 后台模板风格
	 */
	public $_a_theme = 'index';

	/**
	 * 初始化、载入数据表定义、执行权限验证、上下文分析等操作
	 *
	 */
	protected function _initialize() {
		if (strtolower ( MODULE_NAME ) == 'fbase') {
			$this->message ( '页面不存在！' );
		}
		$in = &$this->in;
		$this->initConf ();
		import ( 'ORG.Util.Input' );
		//输入过滤
		$this->in = &Input::getVar ( $_REQUEST );
		$this->forward = $this->in ['forward'] ? $this->in ['forward'] : (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '');
		$this->assign ( 'forward', $this->forward );
		if (false !== $this->needAuth) {//定义了 $needAuth = false 的控制器不需要进行验证
			//检查权限
			$this->checkRbac();
		}

		//$this->_a_theme = $this->initTheme();
		$this->assign('_a_theme', $this->_a_theme);

		$this->assign('userData',$_SESSION['userdata']);
		$this->assign('version',$this->version);
		$this->assign('action_name', ACTION_NAME);
	}

	/**
	 * 初始化定义风格模板
	 *
	 */
	/*protected function initTheme() {
        $in = &$this->in;

        if (empty($_GET['_a_theme'])) {
			return isset($_COOKIE["_a_theme"]) ? $_COOKIE["_a_theme"] : $this->_a_theme;
		} else {
			if (file_exists(TMPL_PATH . C('DEFAULT_THEME') . '/Findex/' . $_GET['_a_theme'] . '.html')) {
				setcookie("_a_theme", $_GET['_a_theme'], time()+3600*24*365);
				return $_GET['_a_theme'];
			} else {
				return isset($_COOKIE["_a_theme"]) ? $_COOKIE["_a_theme"] : $this->_a_theme;
			}
		}
    }*/

	/**
	 * 初始化部分全局定义
	 */
	protected function initConf() {
		//定义相对网站的跟目录
		define ( 'URL_ROOT', C ( 'SITE_TO_ROOT' ) );
		//各资源文件夹目录
		define ( '_PUBLIC_', URL_ROOT . 'admin/Public/' );
		//前台模板文件目录路径
		define('FRONT_TEMPLATE_PATH',FANGFACMS_ROOT . 'public/theme/' . C('DEFAULT_THEME'));
	}


	/**
	 * 检查用户访问权限
	 */
	protected function checkRbac($controller = '',$action = '',$return = false) {
		import ( 'Auth', INCLUDE_PATH );
		$this->_auth = get_instance_of ( 'Auth' );
		//略过开发者的权限检查
		if ($_SESSION['userdata']['username'] == 'developer' || $_SESSION['userdata']['username'] == 'admin') {
			return true;
		}
		if (!$this->_auth->checkRbac ()) {
			if (!$return) {
				if (!empty($this->in ['ajax'])) {
					die('false');
				} else {
					$this->message('登录超时或者没有权限执行此操作！',U('flogin/index?unset=1'));
				}
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * 获取每页显示记录数
	 * @param int $sizename
	 */
	protected function getPageSize($sizename = '') {
		if (! isset ( $sizename ))
			return C ( 'PAGESIZE' ) ? C ( 'PAGESIZE' ) : 20;
		//检查全局设置
		$result = C ( $sizename );
		if (empty ( $result )) {
			//检查cookie
			$result = cookie ( $sizename );
			if (empty ( $result )) {
				//查看是否有传递记录参数
				$in = &$this->in;
				//检查参数
				if ($in [$sizename]) {
					cookie ( $sizename, $in [$sizename] );
					$result = $in [$sizename];
				}
			}
		}
		return $result;
	}

	/**
	 * 提示消息，并跳转到$url
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