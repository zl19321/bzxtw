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
// | 文件描述: 单页
// +----------------------------------------------------------------------

/**
 * @name单页
 * @author netwom
 *
 */
class FpageAction extends FbaseAction {


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
		$this->_category_data = F ('category_'.CATID);
		$this->_request_file = REQUEST_FILE;
		$this->_request_file = substr($this->_request_file,0,strlen($this->_request_file)-strlen(C('URL_HTML_SUFFIX')));
		if (!$this->_category_data) {
			parent::h404();
		}
	}


	/**
	 * @name分发ACTION
	 */
	public function _empty() {
		if (ACTION_NAME == '_empty') {
			$this->h404();
		} else {  //初始化
			$this->show();
		}
	}

	/**
	 * @name显示单页详细信息
	 *
	 */
	public function show() {
		import('Pager',INCLUDE_PATH);
		$in = &$this->in;
		$category_data = &$this->_category_data;
		$this->assign('cat',$category_data);
		//栏目数据
		//如果有子栏目，则默认载入第一个子栏目的数据
//		if (!empty($category_data['childrenids'])) {
//			$catid = $category_data['childrenidarr'][0];
//		} else {
			$catid = CATID;
//		}
		//分页
		$data = D('Page')->where("`catid`='".$catid."'")->find();
		$data = array_merge($category_data,(array)$data);
		$pager = new Pager($data['content'],$this->_page);
		$data['pages'] = $pager->navbar($this->_urls['baseurl'] . '_{page}.html');
		$content = $pager->content();
		if (empty($content) && $this->_page != 1) {
			$this->h404();
		} else {
			$data['content'] = &$content;
		}
		//seo设置
		$seo['seotitle'] = $data['seotitle'] ? $data['seotitle'] : $data['name'];
		$seo['seokeywords'] = $data['seokeywords'] ? $data['seokeywords'] : $data['name'] ;
		$seo['seodescription'] = $data['seodescription'] ? $data['seodescription'] : $data['description'] ;
		$seo = parent::meta_replace($seo);
		//指定当前页面唯一链接
		if ($this->_page > 1) {
			$seo['url'] = str_replace(C('URL_HTML_SUFFIX'),'',$data['url']) . '_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = $data['url'];
		}
		$this->assign('seo',$seo);
		$this->assign('data',$data);
		$this->assign('page',(int)$in['p']);
		$this->display($category_data['setting']['template']['show']);
	}

}