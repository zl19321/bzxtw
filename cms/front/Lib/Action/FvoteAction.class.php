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
 * @name投票问卷
 *
 */
class FvoteAction extends FbaseAction
{
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
	 *
	 */
	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		if (CATID) {
			$catid = CATID;
		} elseif ($in['catid']) {
			$catid = intval($in['catid']);
		} else {
			$this->message(L('参数错误'));			
		}
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
	 * @name投票列表页
	 *
	 */
	public function index()
	{
		$in = &$this->in;

		$category = $this->_category_data;
		if (empty($category['setting']['template']['index'])) $this->message('请先设置问卷列表模板！');
		$category_data = &$this->_category_data;
		//seo设置
		$seo['seotitle'] = $category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name'];
		$seo['seokeywords'] = $category_data['seokeywords'] ? $category_data['seokeywords'] : $category_data['name'];
		$seo['seodescription'] = $category_data['seodescription'] ? $category_data['seodescription'] : $category_data['description'];

		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->assign('p', $this->_page); //当前页码
		$this->assign('pagesize', 10); //每页显示条数

		$_model = M('vote_subject');

		$where =array();
		$where['status'] = 1;
		$where['catid'] = $this->_category_data['catid'];
		$where['todate'] = array('gt', date('Y-m-d'));
		$data['info'] = $_model->where($where)->select();

		foreach ($data['info'] AS &$d) {
			$d['url'] = $this->_category_data['url'] . 'vote?subjectid='.$d['subjectid'];
		}
		//分页
		$count = $_model->where($where)->count(); // 查询满足要求的总记录数
		$pageurl = $this->_category_data['url'] . 'index_{page}.html';
		$data['pages'] =  multi($count, $this->_page, $pageurl, 10, false); // 分页显示输出
		$this->assign('data', $data);
		$this->display($category['setting']['template']['index']);
	}

	/**
	 * @name处理投票
	 *
	 */
	public function add()
	{
		$in = &$this->in;
		$subjectid = intval($in['subjectid']);
		$_model = D('vote_subject', 'admin');
		$subject = $_model->find($subjectid);
		if ($subject['status'] == 0) {
			$this->message('操作失败，投票已关闭！');
		}
		if (time() - strtotime($subject['todate']) > 0) {
			$this->message('操作失败，投票已过期！');
		}
		//验证码
		if($subject['enablecheckcode']){
			if(!$in['code'] || md5($in['code']) != $_SESSION['verify']){
				$this->message('请输入正确的验证码！');
			}
		}
		//判断权限
		if ($_SESSION['fuserdata']['user_id'] == 999999) $permission = 1;
		else $permission = 0;
		if (isset($_SESSION['fuserdata']['user_id'])) {
			$subject['groupidsvote'] = unserialize($subject['groupidsvote']);
			if (is_array($subject['groupidsvote'])) {
				foreach ($_SESSION['fuserdata']['roles'] AS $role) {
					if (in_array($role, $subject['groupidsvote'])) {
						$permission = 1;
						break;
					}
				}
			}
		} else {
			if ($subject['allowguest']==1) $permission = 1;
		}
		if (!$permission) $this->message('非常抱歉，您没有权限进行此项操作！');

		//判断投票频率
		$vote_data_model = M('vote_data');
		$vote_data_where = array();
		$vote_data_where['subjectid'] = $subjectid;
		$vote_data_where['ip'] = $_SERVER[ "REMOTE_ADDR"];
		$vote_data_where['time'] = array('gt', time()-$subject['interval']);
		if ($vote_data_model->where($vote_data_where)->find()) {
			$this->message('您已经投过票了，请不要重复投票！');
		}
		//print_r($subject);exit;
		/*
		//判断投票选项
		if($subject['ischeckbox']){
			//多选
			if(!empty($in['data'][$in['subjectid']])){
				$valuenum = count($in['data'][$in['subjectid']]);
				if(isset($subject['maxval']) && isset($subject['minval'])){
					if($valuenum < $subject['minval']){
						$this->message('您选择的选项过少，请重新选择！');
					}else if($valuenum > $subject['maxval']){
						$this->message('您选择的选项过多，请重新选择！');
					}
				}
			}else{
				$this->message('选项不能为空！');
			}
		}else{
			if(empty($in['data'][$in['subjectid']])){
				$this->message('选项不能为空！');
			}
		}
		*/
		//插入数据
		$vote_data = array();
		$vote_data['subjectid'] = $subjectid;
		$vote_data['time'] = time();
		$vote_data['ip'] = $_SERVER[ "REMOTE_ADDR"];
		$vote_data['data'] = serialize($in['data']);

		if ($_SESSION['fuserdata']['user_id']) {
			$vote_data['user_id'] = $_SESSION['fuserdata']['user_id'];
			$vote_data['username'] = $_SESSION['fuserdata']['username'];
		} else {
			$vote_data['user_id'] = 0;
			$vote_data['username'] = 'Guest';
		}

		$vote_data_model = M('vote_data');
		$vote_data_model->add($vote_data);

		if ($subject['allowview'] == 1) {  //允许查看结果
			redirect(__ROOT__.'/vote/show?subjectid='.$subjectid);
		} else {
			$this->message('投票成功！', $this->forward);
		}
	}

	/**
	 * @name投票页
	 *
	 */
	public function vote()
	{
		$in = &$this->in;

		$subjectid = intval($in['subjectid']);
		if (!$subjectid) $this->message('参数错误！');

		$category = &$this->_category_data;
		if (empty($category['setting']['template']['vote'])) $this->message('没有设定栏目显示页面！');

		$_model = M('vote_subject');
		$data = $_model->where('status=1')->find($subjectid);

		$option_model = M('vote_option');
		//获取选项
		$where = array();
		if ($data['ismultiple'] == 1) {  //问卷
			$where['parentid'] = $subjectid;
			$where['status'] = 1;
			$data['child'] = $_model->where($where)->order('`sort` ASC')->findAll();
			foreach ($data['child'] AS &$v) {
				$v['option'] = $option_model->where('subjectid='.$v['subjectid'])->findAll();
				foreach ($v['option'] AS &$option) {
					$option['image'] = __ROOT__ . '/' . 'public/uploads/' . $option['image'];
				}
			}
		} else {  //投票
			$data['option'] = $option_model->where('subjectid='.$subjectid)->findAll();
			foreach ($data['option'] AS &$option) {
				$option['image'] = __ROOT__ . '/' . 'public/uploads/' . $option['image'];
			}
		}
		$this->assign('data', $data);
		$this->display($category['setting']['template']['vote']);
	}
	
	/**
	 * @name验证码
	 */
	public function verify() {
		import ( "ORG.Util.Image" );
		Image::buildImageVerify ();
	}

	/**
	 * @name结果页
	 *
	 */
	public function show()
	{
		$in = &$this->in;

		$category = $this->_category_data;
		$subjectid = intval($in['subjectid']);
		if (!$subjectid) $this->message('参数错误！');

		if (empty($category['setting']['template']['show'])) $this->message('没有设定投票结果显示页面！');

		$_model = D('vote_subject', 'admin');
		$subject = $_model->find($subjectid);
		
		//判断权限
		if ($_SESSION['fuserdata']['user_id'] == 999999) $permission = 1;
		else $permission = 0;

		if (isset($_SESSION['fuserdata']['user_id'])) {
			$subject['groupidsview'] = unserialize($subject['groupidsview']);
			if (is_array($subject['groupidsview'])) {
				foreach ($_SESSION['fuserdata']['roles'] AS $role) {
					if (in_array($role, $subject['groupidsview'])) {
						$permission = 1;
						break;
					}
				}
			}
		} else {
			if ($subject['allowguest']==1) $permission = 1;
		}
		if (!$permission) {
			if ($in['ajax']) die(json_encode(array('code' => 'error', 'msg' => '非常抱歉，您没有权限查看结果！')));
			else $this->message('非常抱歉，您没有权限查看结果！');
		}
		if(!$subject['status']){
			$this->message('非常抱歉，投票已经关闭！');
		}

		$_vote_data_model = M('vote_data');
		$_option_model = M('vote_option');

		$subject['total_vote_num'] = 0;
		if ($subject['ismultiple'] == 1) { //问卷
			$subject_child = $_model->where('parentid='.$subject['subjectid'])->order('`sort` ASC')->findAll();
		} else { //投票
			$subject_child = array($subject);
		}

		$vote_data = $_vote_data_model->where('subjectid='.$subjectid)->findAll();

		foreach ($subject_child AS &$sc) {
			$sc['total_vote_num'] = 0;  //总投票数
			$vote_option = $_option_model->where('subjectid='.$sc['subjectid'])->findAll();
			foreach ($vote_option AS $vo) {
				$vo['vote_num'] = 0;
				$sc['options'][$vo['optionid']] = $vo;
			}

			if (is_array($vote_data)) {
				foreach ($vote_data AS $vd) {
					$vd['data'] = unserialize($vd['data']);
					if (is_string($vd['data'][$sc['subjectid']])) $vd['data'][$sc['subjectid']] = array($vd['data'][$sc['subjectid']]);
					foreach ($vd['data'][$sc['subjectid']] AS $o_id) {
						if(array_key_exists($o_id, $sc['options'])) {
							$sc['options'][$o_id]['vote_num']++;
							$sc['total_vote_num']++;
							$subject['total_vote_num']++;
						}
					}
				}
			}
		}
		$subject['child'] = $subject_child;
		if ($this->isAjax()) {
			die(json_encode(array('code'=>'ok', 'data'=>$subject)));
		} else {
			$this->assign('subject', $subject);
			$this->display($category['setting']['template']['show']);
		}
	}
}