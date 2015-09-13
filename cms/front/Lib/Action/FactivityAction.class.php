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
// | 文件描述: 前台活动报名页面
// +----------------------------------------------------------------------

/**
 * @name 前台活动报名页面
 *
 */
class FactivityAction extends FbaseAction {
	/**
	 * activity 表对象模型实例
	 * @var object
	 */
	protected $_activity = '';
	
	/**
	 * 栏目数据
	 * @var array
	 */
	private $_category_data = array();
	
	/**
	 * 活动详细信息
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * 执行的动作
	 * @var string
	 */
	protected $_action = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		//查找要执行的动作
		$this->_activity = D ('Activity','admin');
		$prefix = C('DB_PREFIX');
		//TODO 当前时间内的活动
		$where = array(
			'url' => $this->_urls['dburl'],			
		);
		$this->_data = $this->_activity->field("`aid`")->where($where)->find();
		if (!empty($this->_data)) {
			$this->_action = 'show';
		} else {  //栏目页			
			if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
				$this->_action = 'index';
			} elseif ($this->_urls['baseurl'] == 'list') {
				$this->_action = 'lists';				
			} else {
				$this->h404();
			}
		}
	}
	
	/**
	 * @name分发操作
	 */
	public function _empty() {
		if (ACTION_NAME == '_empty') {
			$this->h404();
		} else {  //初始化，分析得到要分发到的action			
			if (method_exists($this,$this->_action)) {				
				$this->_category_data = F ("category_".CATID);
				$this->{$this->_action}();
			}
		}
	}
	
	
	/**
	 * @name活动报名频道页
	 * 
	 */
	public function index() {
		$in = &$this->in;
		//seo设置
		$seo['seotitle'] = $this->_category_data['seotitle'] ? $this->_category_data['seotitle'] : $this->_category_data['name'];
		$seo['seokeywords'] = $this->_category_data['seokeywords'] ? $this->_category_data['seokeywords'] : $this->_category_data['name'];
		$seo['seodescription'] = $this->_category_data['seodescription'] ? $this->_category_data['seodescription'] : $this->_category_data['description'];		
		//指定当前页面唯一链接			
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . 'index_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'];
		}
		//字符替换
		$seo = parent::meta_replace($seo);	
		$this->assign('seo',$seo); //meta信息
		$this->assign('cat',$this->_category_data);	//栏目信息
		$this->assign('p',$this->_page); //当前页码
		$this->display($this->_category_data['setting']['template']['index']);
	}
	
	
	
	/**
	 * @name活动列表显示
	 * 
	 */
	public function show() {
		import('Pager',INCLUDE_PATH);
		$in = &$this->in;
		$_activity=D ('Activity');
		$_activity_apply = D ('ActivityApply');
		if ($this->ispost()) {  
			//检查验证码
			if ($_SESSION ['need_verify'] && $_SESSION ['verify'] != md5 ( $in ['info'] ['verify'] )) {
				if ($in ['ajax']) {
					die ( json_encode ( array ('code' => 'n', 'text' => '验证码错误！' ) ) );
				} else {
					$this->message ( L('验证码错误') );
				}
			}		

			//TODO 用户提交报名信息
			//2013-1-15，陈敏 验证传进来的值
			if (empty($in['name'])) {
				$this->message(L('请填写姓名！'));
			} elseif (!(is_numeric($in['tel']))) {
				$this->message(L('电话号码请填写数字！'));
			} elseif(!(is_numeric($in['qq']))){
				$this->message(L('QQ号码请输入数字！'));
			} elseif(!empty($in['email']) && !preg_match('/\w+@\w+(\.\w+)+/', $in['email'])) {
				$this->message(L('请填写正确的邮件地址！'));
			} elseif($_SESSION['verify'] != md5($in['verify'])) {
				$this->message(L('验证码输入错误！'), $this->forward);
			}		
			//判断是否是会员
			$activity_data = $this->_activity->field("`aid`,`out_time`,`vip`")->where("`aid`='{$in['aid']}'")->find();
			if ($in['aid'] && $activity_data ) {
				if ($activity_data['out_time']<time()) {
					$this->message(L('活动已经结束！'));
				}			
				if (!$_activity_apply->autoCheckToken($in)) {
					$this->message(L('请不要重复刷新页面或者非法提交数据！'));
				}
				$in['create_time'] = time();
				$in['ip']=$_SERVER[ "REMOTE_ADDR"];//2013-1-15 陈敏 用于获取用户在报名时的IP地址
				$in['message'] = strip_tags ($in['message']);
				/**
				update_time:2013-1-15
				author:陈敏
				*下面的代码主要判断后台控制会员参加与否,主要是根据后台的vip字段来判断~
				**/
				if(!($activity_data['vip']))
				{
				if (false !== $_activity_apply->add($in))
				{
					$this->message(L('报名信息提交成功！'), $this->forward);
				}
				else
				{
					$this->message(L('只有会员才能参加报名！如果您是会员，请先登录！'.$_activity_apply->getError()));
				}
			}
				else
				{
					$in['name']=$_SESSION ['fuserdata']['username'];
					$in['bianhao']=$_SESSION ['fuserdata']['user_id'];
					if (false !== $_activity_apply->add($in))
				{
					$this->message(L('报名信息提交成功！'), $this->forward);
				}
				else
				{
					$this->message(L('报名信息提交失败！'.$_activity_apply->getError()));
				}
				}
			} else {
				$this->message(L('数据错误！'));
			}
		}
		
		//查询具体记录的所有相关信息：  扩展表、统计表、tag
		$this->_category_data = F ("category_".CATID);
		$this->assign('cat',$this->_category_data);	
		$data = $this->_activity->where(array('aid'=>$this->_data['aid']))->find();
		//seo设置
		$data = parent::meta_replace($data);
		$seo['seotitle'] = &$data['seotitle'];
		$seo['seokeywords'] = &$data['seokeywords'];
		$seo['seodescription'] = &$data['seodescription'];
		
		$apply['count'] = $_activity_apply->where("aid=".$data['aid']." and status=1")->count();
		import("ORG.Util.Page");
		$Page = new Page($apply['count'],5);
		$apply['pages'] = $Page->show();
		//$data['pages'] = $pager->navbar($this->_category_data['url'] . $this->_urls['baseurl'] . '_{page}.html');
		
		$apply['info'] = $_activity_apply->where("aid=".$data['aid']." and status=1")->order("mid DESC")->limit($Page->firstRow.",".$Page->listRows)->select();

		//指定当前页面唯一链接
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . str_replace(C('URL_HTML_SUFFIX'),'',$this->_urls['baseurl']) . '_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL')  . $data['url'];
		}
		$this->assign('forward',$seo['url']);
		$this->assign('url',$seo['url']);
		$this->assign('apply',$apply);
		$this->assign('seo',$seo);
		$this->assign('data',$data);
		$this->display($this->_category_data['setting']['template']['show']);
	}
	

	
}