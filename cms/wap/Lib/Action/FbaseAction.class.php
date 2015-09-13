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

defined('IN') or die('Access Denied!');

/**

 * @name基础类

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

	 * url初步分析，包括

	 * array(

	 * 	'catdir' = > '', //栏目文件夹名称

	 * 	'dburl' => '',  //数据存数url ，初步参考，具体是否正确，根据模块的url特点自己决定

	 * 	'other' => '',  //其余的url串，除去栏目文件夹名称以后留下的字符串

	 * );

	 * @var array

	 */

	public $_urls = array();



	/**

	 * 当前页码，最靠近url后缀的，以'_'开始后的数字，例如：  news/201007/tests-123-456_2.html ，则为 2

	 * @var unknown_type

	 */

	public $_page = 1;



	/**

	 * @name初始化、载入数据表定义、执行权限验证、上下文分析等操作

	 *

	 */

	protected function _initialize() {

		if (strtolower ( MODULE_NAME ) == 'fbase') {

			$this->h404 ( '页面不存在！' );

		}

		$in = &$this->in;

		import ( 'ORG.Util.Input' );

		import('Fftag',INCLUDE_PATH);

		//输入过滤

		$this->in = &Input::getVar ( $_REQUEST );

		$this->forward = $this->in ['forward'] ? $this->in ['forward'] : (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '');

		//安全过滤，方式XSS攻击

		$this->in = filter_xss($this->in, ALLOWED_HTMLTAGS);



		$this->assign ( 'forward', $this->forward );

		//如果是调试模式，则不生成任何静态文件

		if (IN_DEBUG) C('CREATE_HTML',false);

		//分析要执行的controller

		$this->_baseinit();

		$this->initTheme();

		//当前页码(可选)

		$this->assign('page',$this->_page);

		$this->assign('siteurl',C('SITEURL'));

	}



	/**

	 * @name初始化定义风格模板

	 *

	 */

    protected function initTheme() {

        $in = &$this->in;

    }

    

    

	/**

	 * @name检查用户访问权限

	 * 如果在act中出现了控制器的权限定义，则进行验证，否则默认不验证

	 */

	protected function checkRbac($controller = '',$action = '',$return = false) {

		import ( 'Auth', INCLUDE_PATH );

		$this->_auth = get_instance_of ( 'Auth' );

		//略过开发者的权限检查

		if ($_SESSION['fuserdata']['username'] == 'developer' || $_SESSION['fuserdata']['username'] == 'admin') {

			return true;

		}

		if (!$this->_auth->checkRbac ()) {

			if (!$return) {

				if (!empty($this->in ['ajax'])) {

					die('false');

				} else {

					$this->message('登录超时或者没有权限执行此操作！', __ROOT__ .'/login.html');

				}

			} else {

				return false;

			}

		}

		return true;

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

		$this->assign ( 'msgTitle', L('信息提示') );

		$this->assign ( 'message', $message ); // 提示信息

		//保证输出不受静态缓存影响

		C ( 'HTML_CACHE_ON', false );

		// 成功操作后默认停留1秒

		$this->assign ( 'waitSecond', $wait );

		// 默认操作成功自动返回操作前页面

		$this->assign ( "jumpUrl", $url );

		$this->display ( 'message.html' );

		$exit && exit ();

	}



	//将模板自动重定位到前台模板绝对路径

	protected function fetch($templateFile='',$charset='',$contentType='text/html') {

    	if (!file_exists($templateFile)) $templateFile = TMPL_PATH . $templateFile;

        return parent::fetch( $templateFile ,$charset,$contentType);

    }



    //静态模块自动生成静态文件，将模板自动重定位到前台模板绝对路径

	protected function display($templateFile='',$charset='',$contentType='text/html') {

		$in = &$this->in;

		if (file_exists(TMPL_PATH . $templateFile)) $templateFile = TMPL_PATH . $templateFile;

		if ( C('CREATE_HTML') && ($in['ishtml'])) {

			$htmlfile = $this->getHtmlFilePath();

			$content = parent::fetch( $templateFile ,$charset,$contentType);

			if (!empty($content)) {

				mk_dir(dirname($htmlfile));

				if (file_put_contents($htmlfile,$content,LOCK_EX)) {

					echo $content;

				} else {

					//TODO 保存log

					if(C('LOG_RECORD')) Log::write("模板文件：{$templateFile},生成路径:{$htmlfile},保存失败或者是空文件~",Log::NOTIC);

				}

			} else {

				//TODO  保存log

				if(C('LOG_RECORD')) Log::write("模板文件：{$templateFile},生成路径:{$htmlfile},编译得到空内容",Log::NOTICE );

			}

			exit;

		} else {

			parent::display( $templateFile ,$charset,$contentType);

		}

    }



    /**

     * @name分析url得到静态模块的要生成的静态文件的绝对地址

     *

     */

    private function getHtmlFilePath() {

    	$html_suffix = C('URL_HTML_SUFFIX');

    	if (substr($_SERVER['REQUEST_URI'],-strlen($html_suffix)) == $html_suffix) {

    		$filepath = FANGFACMS_ROOT . REQUEST_FILE;

    	} else {

    		$filepath = FANGFACMS_ROOT . REQUEST_FILE;

    	}

    	return str_replace('//','/',$filepath);

    }



	/**

	 * @name发送404页面

	 */

	protected function h404($msg = '') {

		send_http_status(404);
		header('Location: error.html');
		exit ();

	}



	/**

	 * @name初始化，获取页码，并取得除去文件夹名称以及 '_'+分页码  后的url

	 */

	protected function _baseinit() {

		$request_file = REQUEST_FILE;
		
		$url_suffix = C('URL_HTML_SUFFIX');

		if (false !== strpos($request_file,'/')) {  // 形如 :  news/XXX....的REQUEST_FILE

			$param = explode('/',$request_file);

			$this->_urls['catdir'] = array_shift($param);

			$this->_urls['other'] = implode('/',$param);

			$rountes = C ('_routes_');

			//查找页码

			$last_param = array_pop($param);

			if (preg_match('/_(\d+)\.html/i', $last_param, $match)) {

				$page_param = explode('_',$last_param);

				$page = $match[1];

				//$page = str_replace($url_suffix,'',array_pop($page_param));

				if (is_numeric($page) && $page>0) {

					$this->_page = $page;

				}

				$this->_urls['baseurl'] = !empty($param)

										? implode('/',$param) . '/' . $page_param[0]

										: $page_param[0];

			} else {

				$p_len = 0;

				if (strpos($last_param, '?')) {

					$p_len = strlen(substr($last_param, strpos($last_param, '?')));

				}

				$this->_urls['baseurl'] = !empty($param)

										? implode('/',$param) . '/' . substr($last_param,0,strlen($last_param)-strlen($url_suffix)-$p_len)

										: substr($last_param,0,strlen($last_param)-strlen($url_suffix)-$p_len) ;

			}

			$this->_urls['dburl'] = $this->_urls['baseurl'] . $url_suffix;

		} else { //形如 :  *_num.html的REQUEST_FILE

			if (strpos($request_file,'_') && strpos($request_file,$url_suffix)) {  //含有页码参数的请求

				$page_param = explode('_',$request_file);

				$this->_urls['baseurl'] = $page_param['0'];

				$this->_urls['dburl'] = $this->_urls['baseurl'] . $url_suffix;  //数据中存储的是  不带页码的链接地址

				$page = str_replace($url_suffix,'',array_pop($page_param));

				if (is_numeric($page) && $page>0) {

					$this->_page = $page;

				}

			} else { //不含页码参数的请求

				$this->_urls['baseurl'] = str_replace($url_suffix,'',$request_file);

				$this->_urls['dburl'] = $request_file;  //数据中存储的是  不带页码的链接地址

			}

			$this->_urls['other'] = $request_file;

			$this->_urls['catdir'] = '';

		}

	}



	/**

	 * @name替换meta中的特殊字符

	 */

	protected function meta_replace($data) {

		$in = &$this->in;

		if (is_array($data)) {

			$data = array_map(array($this,'meta_replace'),$data);

			return $data;

		} else if(is_string($data)) {			

			//列表page、站点title、站点keywords、站点description

			return $data = str_replace(

				array('{page}','{stitle}','{skeywords}','{sdescription}'),

				array($in['p'] ? $in['p'] : ($this->_page ? (int)$this->_page : 1),C('SEOTITLE'),C('SEOKEYWORDS'),C('SEODESCRIPTION')),

				$data

			);

		}		

	}

}

?>