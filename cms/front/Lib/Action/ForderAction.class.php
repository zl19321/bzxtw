<?php 
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Forder.class.php
// +----------------------------------------------------------------------
// | Date: 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 
// +----------------------------------------------------------------------
defined('IN') or die('Access Denied!');
/**
 * @name 订单模块
 *
 */
class ForderAction extends FbaseAction {
	
	/**
	 * content 表对象模型实例
	 * @var object
	 */
	protected $_c = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		//在此验证用户是否已登录，如未登录，则转到登陆页面
		
	}
	
	/**
	 * @name提交购物车物品，生成订单，等待用户确认
	 * 
	 */
	public function submit() {
		$in = &$this->in;
		if ($this->ispost()) {//用户提交订单							
			$_mOrder = D ('Order','admin');
			$in['info']['ordername'][0] = array(
			    'cid' => $in['info']['cid'] ? $in['info']['cid'] : 0,
			    'name' => $in['info']['title'],
			    'pageurl' => $in['info']['pageurl'] ? $in['info']['pageurl'] : '#',
			    'price' => $in['info']['price'] ? $in['info']['price'] : 0,
			    'number' => $in['info']['number'] ? $in['info']['number'] : 1,
			);
			//生成订单号				
			$in['info']['ordernum'] = $_mOrder->getOrderNum($in['flag'] ? $in['flag'] : "C");
			$in['info']['total'] = $in['info']['price'] * $in['info']['number'];
			$in['info']['userid'] = 0; //此处为登录用户的用户ID号
			$in['info']['username'] = ''; //此处为登录用户的用户名
			
			if (false !== $_mOrder->add($in['info'])) {  //生成订单信息，待用户确认提交
				//TODO
				$this->message(L('订单提交成功，管理员会稍后和您取得联系！'), $in['info']['pageurl']);
				tag('after_create_order_success');
			} else {
				$this->message(L('订单提交失败！') . '<br />' . $_mOrder->getError());
				tag('after_create_order_fail');
			}
		}
		$this->h404();
	}
	
	/**
	 * @name添加订单
	 *
	 */
	public function add(){
		$in = &$this->in;
		$this->_c = D ('Contentext');
		//获取数据
		$options = array(
			'where' => array('cid' => $in['cid']),
		);
		$data = $this->_c->get($options,'all');
		$category_data = F ( 'category_' . $data['catid'] );
		$data['pageurl'] = $category_data['catdir'].'/'.$data['url'];
		$data['forward'] = $this->forward;
		$this->assign('data',$data);
		//print_r($data);
		//print_r($in);
		$this->display('order/'.$in['a'].'.html');
	}
	
	/**
	 * @name确认订单
	 *
	 */
	public function confirm(){
		$in = &$this->in;
		$this->_c = D ('Contentext');
		//获取数据
		$options = array(
			'where' => array('cid' => $in['cid']),
		);
		$data = $this->_c->get($options,'all');
		$category_data = F ( 'category_' . $data['catid'] );
		$data['pageurl'] = $category_data['catdir'].'/'.$data['url'];
		$data['forward'] = $this->forward;
		$data['total'] = $data['price']*$in['info']['number'];
		$this->assign('data',$data);
		$this->assign('in',$in['info']);
		$this->display('order/'.$in['a'].'.html');
	}
}