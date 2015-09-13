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
// | 文件描述: 评论的前台控制器
// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');
/**
 * @name 评论前台模块
 *
 */
class FcommentAction extends FbaseAction {	
	/**
	 * 栏目数据
	 * @var unknown_type
	 */
	protected $_category_data = array();
	
	/**
	 * 请求的文件,不包括 .html
	 * @var string
	 */
	protected $_request_file = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		$arr=explode('-',$this->_urls['baseurl']);
		
		if($arr[0] == 'show')
		{
			$in['catid'] = $arr[1];
			$in['newsid'] = $arr[2];
			$in['cid'] = $arr[2];
		}

		if (CATID) $catid = CATID;
		elseif ($in['catid']) $catid = intval($in['catid']);
		else {
			header("Content-type: text/html; charset=utf-8");
			die('<script>alert("缺少参数catid！"); location.href="' . $this->forward . '";</script>');
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
	 * @name评论展示/列表页
	 *
	 */
	public function show() {

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
		$news = D ('Content','admin');
		$data['news']=$news->get($in['cid'],'all');

		$Comment = M('Comment');
		$where = array(
			'status' => '1',
			'newsid' => $in['newsid'],
		);
		$data['info'] = $Comment->where($where)->page($this->_page . ',' . $pagesize)->select();
		//分页
		$count = $Comment->where($where)->count(); // 查询满足要求的总记录数
		$pageurl = $this->_category_data['url'] . 'index_{page}.html';
		$data['pages'] =  multi($count, $this->_page, $pageurl, $pagesize); // 分页显示输出
		if($this->isajax()) {
			
		} else {

			$this->assign('data', $data);
			$this->display('system/comment/default_comment_list.html');
		
		}
		
	}
	/**
	 * @name添加评论
	 *
	 */
	public function add() {
		$in = &$this->in;
		array_map('trim', $in);
		header("Content-type: text/html; charset=utf-8");
		if (!empty($in['comment'])) {
			//验证数据来源
			$Comment = M('comment');
			if (!$Comment->autoCheckToken($in)) {
				$this->message(L('请不要非法提交或者重复刷新页面！'), $this->forward);
			}

			$data = array(
				'catid'			=>	(int)$in['catid'],
				'comment'		=>	htmlspecialchars($in['comment']),		
				'status'		=>	($this->_category_data['setting']['isstatus']==1 ? 1 : 0),
				'ip'			=>	get_client_ip(),
				'create_time'		=>	time(),
				'newsid'		=>  (int)$in['newsid'],
			);
			//过滤敏感词语
			$cat = D ( 'Category' );
			$cat_data = $cat->field('setting')->where(" `catid`=".$in['catid']."")->find();
			if(isset($cat_data['setting']['isfilter']) && $cat_data['setting']['isfilter']){//如果开启过滤
				$data = $this->filter($data);
			}

			if( C('COMMENT_USER_LOGIN') == 1 )
			{
				if ($this->_category_data['setting']['islogin'] == 1 && empty($_SESSION['fuserdata']['username'])) {
					$this->message(L('请您先登录，只有登录的用户才能评论！'));				
				}

				if ($_SESSION['fuserdata']['username']) {
					$data['userid'] = $_SESSION['fuserdata']['user_id'];
					$data['username'] = $_SESSION['fuserdata']['username'];
				} else {
					$data['username'] = mysql_escape_string(htmlspecialchars($in['username']));	
				}
			}

			/*
			if ($_SESSION['verify'] != md5($in['verify'])) {
				$this->message(L('验证码输入错误！'), $this->forward);
			}
			*/

			if (false !== $Comment->add($data)) {
				//unset($_SESSION['verify']);
				
				$_count = M ('ContentCount');
				$_count->execute("update __TABLE__ set `comments`=`comments`+1 where `cid`='{$in['newsid']}' limit 1");
				$this->message(L("评论提交成功！"));				
			} else {
				$this->message(L("评论提交失败，请稍后再试！"));
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
		$data['comment'] = preg_replace($filterwords,'***',$data['comment']);
		return $data;
	}
}





