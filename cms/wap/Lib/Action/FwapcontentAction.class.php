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

class FwapcontentAction extends FbaseAction {

	

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

        

        $_c = D ('Content');


		$where = array(

			'url' => $this->_urls['dburl'],

			'status' => '9'

		);

		$this->_data = $_c->field("`cid`")->where($where)->find();





		if (!empty($this->_data)) {

			$this->_action = 'show';

		} else {  //栏目页			

			if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {

				$this->_action = 'index';



			} else {

				if($this->isajax())

				{



				}else

				{

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



				$this->_category_data = F ("wap_".CATID.'.class');



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



        $_mobile_menu = D('Mobilemenu');

        $this->_category_data = $_mobile_menu->where('id = '.ID)->find();

        $setting = $this->_category_data['setting']; 

		$catid=$this->_category_data['catid'];//当前栏目的catid

		$content_product=M('Content_product');//实例化content_product

		$content=M('Content');//实例化content

		$pro_cid=$content->where('catid='.$catid)->order('sort ASC,cid DESC')->limit(9)->select();//获取满足条件产品的cid

		$cm_cid=array();//用于存放满足条件的数字的cid

		for($i=0;$i<count($pro_cid);$i++)

		{

			$cm_cid[]=$pro_cid[$i]['cid'];//循环出满足条件的产品的cid

		}

        $this->_category_data['setting'] = eval("return {$setting};");

        

        $this->_category_data['cache'] = F('wap_'.ID.'.class','',INCLUDE_PATH.'wap/'); 



		//字符替换

		$this->assign('catid',$catid);

		$seo = parent::meta_replace($seo);		

		$this->assign('seo',$seo); //meta信息

		$this->assign('cat',$this->_category_data);	//栏目信息	

		$this->assign('p',$this->_page); //当前页码

		$this->display($this->_category_data['setting']['template']['index']);

	}

	

	

	/**

	 * @name详细内容信息

	 * 

	 */

	public function show() {

		import('Pager',INCLUDE_PATH);

		$in = &$this->in;

		$db_pre = C('DB_PREFIX');
		
        $_mobile_menu = D('Mobilemenu');

        $this->_category_data =$_mobile_menu->where('id = '.ID)->find();

        $setting = $this->_category_data['setting'];

		$this->assign('cat',$this->_category_data);


        $this->_category_data['setting'] = eval("return {$setting};");

		$PC_cat = F("category_".$this->_category_data['catid']);
		
		$Model = F("model_".$PC_cat['modelid']);
		
		$_content = M ('Content');

        $where = array();

        $where= $db_pre."content.cid=".$this->_data['cid'];     		
        $data = $_content->join("LEFT JOIN  {$db_pre}".$Model['tablename']." ON {$db_pre}".$Model['tablename'].".cid = {$db_pre}content.catid")->where($where)->find();
        $data['cid'] =  $this->_data['cid'];   
		
		$pre = $_content->where("`cid` < ".$data['cid']." and `status` = 9 and `catid`=".$data['catid']."")->order('cid DESC')->limit('1')->find();

		$next = $_content->where("`cid` > ".$data['cid']." and `status` = 9 and `catid`=".$data['catid']."")->order('cid asc')->limit('1')->find();

		
		$cat = $this->_category_data;

		

		if (!empty($pre)) {

			$data['pre_title'] = $pre['title'];

			$data['pre_url']   = $cat['url'] ."/". $pre['url'];

		} else {

			$data['pre_title'] = '没有了';

			$data['pre_url']   = '#';

		}

		if (!empty($next)) {

			$data['next_title'] = $next['title'];

			$data['next_url']   = $cat['url'] ."/". $next['url'];

		} else {

			$data['next_title'] = '没有了';

			$data['next_url']   = '#';

		}
		$this->assign('seo',$seo);

		$this->assign('comment',$comment);
		
		
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