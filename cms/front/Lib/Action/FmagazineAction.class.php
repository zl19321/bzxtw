<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FpageAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-3
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 资讯内容显示
// +----------------------------------------------------------------------
defined('IN') or die('Access Denied!');
/**
 * @name 资讯内容显示
 *
 */
class FmagazineAction extends FbaseAction {
	
	/**
	 * content 表对象模型实例
	 * @var object
	 */
	protected $_c = '';
	
	/**
	 * @name栏目数据
	 * @var array
	 */
	private $_category_data = array();
	
	/**
	 * @name内容详细信息
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * @name执行的动作
	 * @var string
	 */
	protected $_action = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		//查找要执行的动作
		
		$this->_c = D ('Contentext');
		$where = array(
			'status' => 9,
			'url' => $this->_urls['dburl']
		);
		$dburl = explode("/",$where['url']);
		$count = $this->_c->where($where)->count();

		if (!empty($count)) {
			$this->_action = 'show';
		}elseif(in_array("t",$dburl)){
			$this->_action = 'lists';
		}
		elseif ( in_array("v",$dburl) ){
			$this->_action = 'view';
		} else {  //栏目页			
			if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
				$this->_action = 'index';
			} else {
				if($this->isajax()){
				}else{
					$this->h404();
				}
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
	 * @name栏目页
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
	 * @name列表页
	 * 
	 */
	public function lists() {
		$in = &$this->in;		
		//指定当前页面唯一链接			
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . 'index_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'];
		}
		$dburl = explode("/",$this->_urls['dburl']);
		$data = D("Magazine")->where("id=".$dburl['1'])->find();
		
		$seo['seotitle'] = $data['title'] . " - " . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
		//字符替换
		$this->assign('seo',$seo); //meta信息
		$this->assign('cat',$this->_category_data);	//栏目信息	
		$this->assign('p',$this->_page); //当前页码
		
		$this->assign("data",$data);
		$this->assign("parentid",$data['id']);
		$this->display($this->_category_data['setting']['template']['lists']);
	}
	/**
	 * @name杂志详细页
	 * 
	 */
	public function view() {
		$in = &$this->in;		
		
		$dburl = explode("/",$this->_urls['dburl']);
		$data = D("Magazine")->where("id=".$dburl['1'])->find();
		if (!strstr($data['img_map'], $data['images'])) {
			echo "<font color='red'>上传图片与图片地图不一致！</font>";
		}
		$seo['seotitle'] = $data['title'] . " - " . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
		$this->assign('seo',$seo); //meta信息
		$this->assign('cat',$this->_category_data);	//栏目信息	
		$this->assign("data",$data);
		$this->display($this->_category_data['setting']['template']['view']);
	}


	/**
	 * @name详细内容信息
	 * 
	 */
	public function show() {
		import('Pager',INCLUDE_PATH);
		$in = &$this->in;
		//查询具体记录的所有相关信息：  扩展表、统计表、tag
		$this->_category_data = F ("category_".CATID);
		$this->assign('cat',$this->_category_data);
		$this->assign('comment_open', C('CONTENT_COMMENT_OPEN')); //是否开启评论
		//获取数据
		$options = array(
			'where' => array('url'=>$this->_urls['dburl']),
		);		
		$data = $this->_c->get($options,'all');
		//seo设置
		$data = parent::meta_replace($data);
		$seo['seotitle'] = &$data['seotitle'];
		$seo['seokeywords'] = &$data['seokeywords'];
		$seo['seodescription'] = &$data['seodescription'];
		//内容分页
		$pager = new Pager($data['content'],$this->_page);			
		$data['pages'] = $pager->navbar($this->_category_data['url'] . $this->_urls['baseurl'] . '_{page}.html');
		$content = $pager->content();
		if (empty($content) && $this->_page != 1) {
			$this->h404();
		} else {
			$data['content'] = &$content;
		}
		if(empty($data['content_id']) ||  !isset($data['content_id'])){
			$data['content_id'] = "";
		}
		$this->assign('seo',$seo);
		$this->assign('data',$data);
		$this->display($this->_category_data['setting']['template']['show']);
	}
	
	/**
	 * @name浏览次数
	 *
	 */
	public function count() {
		$in = &$this->in;
		if (!$in['cid'] || !$in['type']) exit ();
		$_count = M ('ContentCount');
		
		$data = $_count->field("`{$in['type']}`")->where("`cid`='{$in['cid']}'")->find();

		if(empty($data))
		{
			$_count->add(array('cid'=>$in['cid']));
			$data = $_count->field("`{$in['type']}`")->where("`cid`='{$in['cid']}'")->find();
		}
		if (isset($data[$in['type']])) {
			
			if ($in['type'] == 'hits') {
				echo (int)$data[$in['type']]+1;
				$_count->execute("update __TABLE__ set `hits`=`hits`+1 where `cid`='{$in['cid']}' limit 1");
			}else if($in['type'] == 'comments')
			{
				echo (int)$data[$in['type']];
			}else if($in['type'] == 'comments_checked')
			{
				echo (int)$data[$in['type']];
			}
			exit ();
		}
	}

}