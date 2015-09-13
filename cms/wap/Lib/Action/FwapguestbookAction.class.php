<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: FwapAction.class.php

// +----------------------------------------------------------------------

// | Date: 2013-06-17

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述:  

// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');

/**

 * @name 资讯内容显示

 *

 */

class FwapguestbookAction extends FbaseAction {

	

	/**

	 * @name栏目数据

	 * @var array

	 */

	private $_category_data = array();

	/**

	 * @name初始化

	 */

	protected function _initialize() {
         parent::_initialize();
		  $in = &$this->in;
          $_mobile_menu = D('Mobilemenu');		   
		  $this->_category_data= $_mobile_menu->where('id = '.ID)->find();

		  
	   if ( ID && !empty($this->_category_data)) {
		   $in['catid'] =$catid =  $this->_category_data['catid'];
		}  else {
			header("Content-type: text/html; charset=utf-8");
			$this->message(('参数错误'));	
parent::h404();			
		}
		$this->assign('cat', $this->_category_data);	//栏目信息 
	    if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
				$this->index();
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
          
		$setting =  $this->_category_data['setting'];
		
		$this->_category_data['setting'] = eval("return {$setting};");
		 
		 
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
			  
		$this->display($this->_category_data['setting']['template']['index']);

	}
}