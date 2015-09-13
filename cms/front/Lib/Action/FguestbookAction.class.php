<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fcontent.class.php
// +----------------------------------------------------------------------
// | Date: Fri Apr 23 15:18:50 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 留言板的前台控制器
// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');
/**
 * @name 留言板前台模块
 *
 */
class FguestbookAction extends FbaseAction {	
	/**
	 * @name栏目数据
	 * @var unknown_type
	 */
	protected $_category_data = array();
	
	/**
	 * @name请求的文件,不包括 .html
	 * @var string
	 */
	protected $_request_file = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		if (CATID) {
			$catid = CATID;
			$in['catid'] = CATID;	
		} elseif ($in['catid']) {
			$catid = intval($in['catid']);
		} else {
			header("Content-type: text/html; charset=utf-8");
			$this->message(('参数错误'));			
		}
		$in['catid'] = $catid;
		$this->_category_data = F ('category_'.$catid);
		$this->_request_file = substr(REQUEST_FILE,0,strlen(REQUEST_FILE)-strlen(C('URL_HTML_SUFFIX')));
		
		if (!$this->_category_data) parent::h404();
		$this->assign('cat', $this->_category_data);	//栏目信息
	}
	
	/**
	 * @name分发到对应的动作
	 */
	protected function _empty() {
		$baseurl = str_replace('?', '/', $this->_urls['baseurl']);
		$method = explode('/', $baseurl);
		$method = $method[0];
		if (method_exists($this, $method)) {
			call_user_func(array($this, $method));
		} else {
			$this->h404();
		}
	}
	/**
	 * @name留言板首页列表页
	 *
	 */
	public function index() {
		$in = &$this->in;
		$category_data = &$this->_category_data;
		//seo设置
		$seo['seotitle'] = $category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name'];
		$seo['seokeywords'] = $category_data['seokeywords'] ? $category_data['seokeywords'] : $category_data['name'];
		$seo['seodescription'] = $category_data['seodescription'] ? $category_data['seodescription'] : $category_data['description'];
		$seo['url'] = $category_data['url'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->assign('p', $this->_page); //当前页码
		$this->assign('pagesize', 10); //每页显示条数
		
		//获取列表数据
		$pagesize = 12;
		$data = array();
		$Guestbook = M('guestbook');
		$where = array(
			'status' => '1',
			'catid' => CATID,
		);
		$data['info'] = $Guestbook->where($where)->page($this->_page . ',' . $pagesize)->select();
		//分页
		$count = $Guestbook->where($where)->count(); // 查询满足要求的总记录数
		$pageurl = $this->_category_data['url'] . 'index_{page}.html';
		$data['pages'] =  multi($count, $this->_page, $pageurl, $pagesize); // 分页显示输出
		$this->assign('data', $data);
		$this->display($category_data['setting']['template']['index']);
	}
		
	/**
	 * @name添加留言
	 *
	 */
	public function add() {
		$in = &$this->in;
		array_map('trim', $in);
		if (!empty($in['content'])) {
			//验证数据来源
			$Guestbook = M('guestbook');
			if (!$Guestbook->autoCheckToken($in)) {
				$this->message(L('请不要非法提交或者重复刷新页面！'), $this->forward);
			}
			// if ($this->_category_data['setting']['islogin'] == 1 && empty($_SESSION['fuserdata']['username'])) {
			// 	$this->message(L('请您先登录，只有登录的用户才能留言！'));				
			// }
			if (!$_SESSION['fuserdata']['username'] && empty($in['username'])) {
				$this->message(L('请输入您的称呼！'));				
			}
			// if (!empty($in['email']) && !preg_match('/\w+@\w+(\.\w+)+/', $in['email'])) {
			// 	$this->message(L('您输入的邮箱地址格式不正确！'), $this->forward);
			// }
			if (empty($in['class'])) {
				$this->message(L('请输入班级！'), $this->forward);
			}	
			// if ($_SESSION['verify'] != md5($in['verify'])) {
			// 	$this->message(L('验证码输入错误！'), $this->forward);
			// }			
			$data = array(
				'catid'			=>	(int)$in['catid'],
				'class'			=>	htmlspecialchars($in['class']),
				'contents'		=>	htmlspecialchars($in['contents']),		
				'status'		=>	($this->_category_data['setting']['isstatus']==1 ? 0 : 1),
				'ip'			=>	get_client_ip(),
				'addtime'		=>	time()
			);
			//过滤敏感词
			//过滤敏感词语
			$cat = D ( 'Category' );
			$cat_data = $cat->field('setting')->where(" `catid`=".$in['catid']."")->find();
			if(isset($cat_data['setting']['isfilter']) && $cat_data['setting']['isfilter']){//如果开启过滤
				$data = $this->filter($data);
			}
			if ($_SESSION['fuserdata']['username']) {
				$data['userid'] = $_SESSION['fuserdata']['user_id'];
				$data['username'] = $_SESSION['fuserdata']['username'];
			} else {
				$data['username'] = mysql_escape_string(htmlspecialchars($in['username']));	
			}
			
			// $in['email'] && $data['email'] = htmlspecialchars($in['email']);
			// $in['qq'] && $data['qq'] = (int)$in['qq'];
			// $in['homepage'] && $data['homepage'] = htmlspecialchars($in['homepage']);
			// $in['telphone'] && $data['telphone'] = htmlspecialchars($in['telphone']);
			// $in['address'] && $data['address'] = htmlspecialchars($in['address']);
			$data['code'] = "HZM".time();
			if (false !== $Guestbook->add($data)) {
				unset($_SESSION['verify']);
				$this->message(L("留言提交成功,您的回执码为：【".$data['code'])."】");				
			} else {
				$this->message(L("留言提交失败，请稍后再试"));
			}
		} else {  //显示发布留言界面
			$category_data = &$this->_category_data;
			//seo设置
			$seo['seotitle'] = $category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name'];
			$seo['seokeywords'] = $category_data['seokeywords'] ? $category_data['seokeywords'] : $category_data['name'];
			$seo['seodescription'] = $category_data['seodescription'] ? $category_data['seodescription'] : $category_data['description'];
			$seo['url'] = $category_data['url'];
			//字符替换
			$seo = parent::meta_replace($seo);
			$this->assign('seo', $seo); //meta信息
			$this->display($this->_category_data['setting']['template']['show']);
		}
	}
	
	/**
	* @name过滤敏感词
	*/
	public function filter($data){
		if(empty($data)) return;
		$filterwords = explode('|',C ('FILTER_WORD'));//敏感词组
		foreach($filterwords as $k => $v){
			$filterwords[$k] = '/'.$v.'/';
		}
		$data['title'] = preg_replace($filterwords,'***',$data['title']);
		$data['content'] = preg_replace($filterwords,'***',$data['content']);
		return $data;
	}
}





