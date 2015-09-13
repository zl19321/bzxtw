<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fcontent.class.php
// +----------------------------------------------------------------------
// | Date: 2010-09-17
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 人才招聘
// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');
/**
 * @name 人才招聘
 *
 */
class FsalenetAction extends FbaseAction {
	/**
	 * @name网络营销分类对象
	 * 
	 * @var object
	 */	
	protected $_salenet_cat = null;
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
	protected $_action = null;

	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		if (CATID) {
			$catid = CATID;
		} elseif ($in['catid']) {
			$catid = intval($in['catid']);
		} else {
			$this->message(L('缺少参数catid！'));
		};
		
		$this->_category_data = F ('category_'.$catid);
		$this->_request_file = substr(REQUEST_FILE,0,strlen(REQUEST_FILE)-strlen(C('URL_HTML_SUFFIX')));
		$this->assign('cat', $this->_category_data);
	}
	
	/**
	 * @name分发到对应的动作
	 */
	protected function _empty() {
		$in = &$this->in;
		if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
			$this->_action = 'index';
		} else {
			$this->_action = 'show';
		}
		if (method_exists($this,$this->_action)) {				
			$this->_category_data = F ("category_".CATID);
			$this->{$this->_action}();
		}
	}
	
	/**
	 * @name频道页
	 */
	public function index()
	{
		$in = &$this->in;

		$category_data = $this->_category_data;
		$seo['seotitle'] = $category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name'];
		$seo['seokeywords'] = $category_data['seokeywords'] ? $category_data['seokeywords'] : $category_data['name'];
		$seo['seodescription'] = $category_data['seodescription'] ? $category_data['seodescription'] : $category_data['description'];
		
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->assign('p', $this->_page); //当前页码
		$this->display($this->_category_data['setting']['template']['lists']);
	}
	
	/**
	 * @name职位详情
	 */
	public function show()
	{
		$in = &$this->in;
	

        $_salenet = M('Salenet');
        
        $data = $_salenet->where('province = '.$in['province'])->select();

		$this->assign('data', $data);
        
        $_ps = M('Province');
        
        
        $p = $_ps->where('id = '.$in['province'])->find();

        $this->assign('p',$p);
            
		$category_data = $this->_category_data;
		$seo['seotitle'] = $job_data['title'] . '-' .($category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name']);
		$seo['seokeywords'] = $job_data['title'];
		$seo['seodescription'] = $job_data['notes'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息

		$this->display($this->_category_data['setting']['template']['show']);
	}
    
     public function view(){
        

        
        $in = &$this->in;
        
        $cat['catid'] = 37;
        
        $cat['parentid'] = 7;
        
        $this->assign('cat',$cat);
        
        $_salenet = M('Salenet');
        
        
        
        $data = $_salenet->where('sid = '.$in['sid'])->find();
        
        $_ps = M('Province');
        
        $p = $_ps->where('id = '.$data['province'])->find();

        

        $this->assign('p',$p);

		$this->assign('data', $data);
        
        $this->display('salenet/view.html');
        
    }
	
	

}