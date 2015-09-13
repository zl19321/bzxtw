<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: BaseAction.class.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 13:44:16 CST 2010
// +----------------------------------------------------------------------
// | 文件描述: 投票问卷
// +----------------------------------------------------------------------
defined('IN') or die('Access Denied!');
/**
 * @name 投票问卷
 * @author netwom
 *
 */
class FaskAction extends FbaseAction 
{
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
	 *
	 */
	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		if (CATID) $catid = CATID;
		elseif ($in['catid']) $catid = intval($in['catid']);
		else die('<script>alert("缺少参数catid！"); location.href="' . $this->forward . '";</script>');
		
		$this->_category_data = F ('category_'.$catid);
		$this->_request_file = substr(REQUEST_FILE,0,strlen(REQUEST_FILE)-strlen(C('URL_HTML_SUFFIX')));
	
		$this->assign('cat', $this->_category_data);
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
	 * @name问答列表页
	 */
	public function index()
	{
		$in = &$this->in;
		$category = $this->_category_data;
		$pagesize = 1;
		if (empty($category['setting']['template']['index'])) $this->message('请先设置问答列表页模板！');

		//seo设置
		$seo['seotitle'] = $category['seotitle'] ? $category['seotitle'] : $category['name'];
		$seo['seokeywords'] = $category['seokeywords'] ? $category['seokeywords'] : $category['name'];
		$seo['seodescription'] = $category['seodescription'] ? $category['seodescription'] : $category['description'];
		
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->assign('p', $this->_page); //当前页码
		$this->assign('pagesize', $pagesize); //每页显示条数
		
		$_model = M('ask');
		$where = array();
		$where['status'] = 1;
		$where['parentid'] = 0;
		$where['catid'] = array('IN', $category['childrenids_self']);
		if (is_numeric($in['ask_category_id'])) $where['ask_category_id'] = $in['ask_category_id'];
		$data['info'] = $_model->where($where)->order('ask_id DESC')->page($this->_page . ',' . $pagesize)->select();
		foreach ($data['info'] AS &$d) {
			$d['url'] = $category['url'] . 'show?ask_id=' . $d['ask_id'];
		}
		//分页
		$count = $_model->where($where)->count(); // 查询满足要求的总记录数
		$pageurl = $category['url'] . 'index_{page}.html';
		if (is_numeric($in['ask_category_id'])) $pageurl .= '?ask_category_id='.$in['ask_category_id'];
		$data['pages'] =  multi($count, $this->_page, $pageurl, $pagesize, false); // 分页显示输出
		
		$this->assign('data', $data);
		$this->display($category['setting']['template']['index']);
	}
	
	/**
	 * @name问答详情页
	 */
	public function show()
	{
		$in = &$this->in;
		$category = $this->_category_data;
		if (empty($in['ask_id'])) $this->message('参数错误！');
		if (empty($category['setting']['template']['show'])) $this->message('请先设置问答详情页模板！');
		//seo设置
		$seo['seotitle'] = $category['seotitle'] ? $category['seotitle'] : $category['name'];
		$seo['seokeywords'] = $category['seokeywords'] ? $category['seokeywords'] : $category['name'];
		$seo['seodescription'] = $category['seodescription'] ? $category['seodescription'] : $category['description'];
		$seo['url'] = $category['url'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		
		$_model = M('ask');
		$in['ask_id'] = intval($in['ask_id']);
		$data = $_model->find($in['ask_id']);
		
		$where = array();
		$where['status'] = 1;
		$where['parentid'] = $in['ask_id'];
		if ($data['good_answer'] > 0) {
			$data['good_answer'] = $_model->find($data['good_answer']);
			$where['ask_id'] = array('neq', $data['good_answer']['ask_id']);
			$data['answer_list'] = $_model->where($where)->select();
		} else {
			$data['answer_list'] = $_model->where($where)->select();
		}
		
		$this->assign('data', $data);
		$this->assign('fuserdata', $_SESSION['fuserdata']);
		$this->display($category['setting']['template']['show']);
	}
	
	/**
	 * @name提交问答表单页
	 */
	public function add()
	{
		$in = &$this->in;
		$category = $this->_category_data;
		header("Content-type: text/html; charset=utf-8");
		
		if (!isset($in['ask_id']) && $this->_category_data['setting']['allowAsk'] == 1 && empty($_SESSION['fuserdata']['username'])) {
			if ($in['ajax']) die(array('code' => 'error', 'msg' => '请您先登录！'));
			else die('<script>alert("请您先登录！"); location.href="' . $this->forward . '";</script>');
		}
		
		if ($in['ask_id'] && $this->_category_data['setting']['allowAnswer'] == 1 && empty($_SESSION['fuserdata']['username'])) {
			if ($in['ajax']) die(array('code' => 'error', 'msg' => '请您先登录！'));
			else die('<script>alert("请您先登录！"); location.href="' . $this->forward . '";</script>');
		}
		
		if (!empty($in['content'])) {
			if ($_SESSION['verify'] != md5($in['verify'])) {
				if ($in['ajax']) die(array('code' => 'error', 'msg' => '验证码输入错误！'));
				else die('<script>alert("验证码输入错误！"); location.href="' . $this->forward . '";</script>');
			}
			if (empty($_SESSION['fuserdata']['username']) && empty($in['username'])) {
				if ($in['ajax']) die(array('code' => 'error', 'msg' => '请输入您的昵称！'));
				else die('<script>alert("请输入您的昵称！"); location.href="' . $this->forward . '";</script>');
			}
			if (!isset($in['parentid']) && empty($in['title'])) {
				if ($in['ajax']) die(array('code' => 'error', 'msg' => '请输入您要发表的问题！'));
				else die('<script>alert("请输入您要发表的问题！"); location.href="' . $this->forward . '";</script>');
			}
			
			$in['create_time'] = $in['update_time'] = time();
			$in['ip'] = $_SERVER['REMOTE_ADDR'];
			if ($_SESSION['fuserdata']['user_id']) $in['user_id'] = $_SESSION['fuserdata']['user_id'];
			
			$_model = D('ask', 'admin');
			array_map('trim', $in);
			$in['catid'] = intval($in['catid']);
			$in['ask_category_id'] = intval($in['ask_category_id']);
			$in['parentid'] = intval($in['parentid']);
			$in['content'] = htmlspecialchars($in['content']);
			$in['title'] = htmlspecialchars($in['title']);
			array_map('mysql_escape_string', $in);
			$_model->create($in);
			if ($_model->add()) {
				if ($in['parentid']) $_model->updateAnswerNum($in['parentid']);
				if ($in['ajax']) die(array('code' => 'ok', 'msg' => '提交成功！'));
				else die('<script>alert("提交成功！"); location.href="' . $this->forward . '";</script>');
			} else{
				if ($in['ajax']) die(array('code' => 'ok', 'msg' => '提交失败！'));
				else die('<script>alert("提交失败！"); location.href="' . $this->forward . '";</script>');
			}
		} else {
			if (empty($category['setting']['template']['form'])) $this->message('请先设置问答提交问题页模板！');
		}
		//seo设置
		$seo['seotitle'] = $category['seotitle'] ? $category['seotitle'] : $category['name'];
		$seo['seokeywords'] = $category['seokeywords'] ? $category['seokeywords'] : $category['name'];
		$seo['seodescription'] = $category['seodescription'] ? $category['seodescription'] : $category['description'];
		$seo['url'] = $category['url'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		
		//同类型分类下拉列表json
		import ( 'Tree', INCLUDE_PATH );
		$tree = get_instance_of ( 'Tree' );
		$ask_category_model = M('ask_category');
		$ask_category_where = array();
		$ask_category_where['status'] = 1;
		$ask_category_where['catid'] = $category['catid'];
		$ask_categorys = $ask_category_model->where($ask_category_where)->field("`ask_category_id` AS `id`,`name`,`parentid`")->order('`sort` ASC')->findAll();
		$tree->init ( $ask_categorys );
		$str = "<option value='\$id' \$selected>\$spacer\$name</option>\n";
		$ask_categorys_option = $tree->get_tree ( 0, $str, 0);
		$this->assign ( 'ask_categorys_option',$ask_categorys_option );	//已有分类
		
		$this->assign('fuserdata', $_SESSION['fuserdata']);
		$this->assign('in', $in);
		$this->display($category['setting']['template']['form']);
	}
}