<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ForderAction.class.php
// +----------------------------------------------------------------------
// | Date: Fri Apr 23 14:18:54 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 订单模块
// +----------------------------------------------------------------------
//

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 订单模块
 *
 */
class ForderAction extends FbaseAction {
	
	/**
	 * @name订单管理
	 * @see FbaseAction::manage()
	 */
	public function manage() {
		$in = &$this->in;
		$_mOrder = D ('Order','Admin');
		//初始化数据
		$data = array();
		//查询条件 ,   group  by  ordernum
		$where = array ();
		if (isset($in['status'])) {
			$in['status'] = intval($in['status']);
			$where["status"] = $in['status'];
			$this->assign('status',$in['status']);
		} else {  //显示所有非取消的并且客户已经确认的订单
			$where["status"] = array('gt', '-1'); 
		}
		if ($in['q'] && $in['q'] != '请输入关键字') {
			$in['q'] = urldecode($in['q']);
			$where["{$in['field']}"] = array('like',"%{$in['q']}%");
		}
		//排序条件
		$order = " `orderid` DESC ";
		//统计订单数量
		$data ['count'] = $_mOrder->where ( $where )->count ();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] =  $_mOrder->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();;
//		dump($data);exit;
		$this->assign ( 'data', $data );
		$this->assign('in',$this->in);
		$this->display();
	}
	
	/**
	 * @name修改订单
	 * 
	 */
	public function edit() {
		$in = &$this->in;
		$_mOrder = D ('Order','admin');
		//修改
		if ($this->ispost()) { //保存信息
//			dump($in['info']);exit;
			if (false === $_mOrder->save($in['info'])) {
				$this->message(L(' 修改失败！') . $_mOrder->getError());
			} else {
				$this->message(L(' 修改成功！'), U('forder/manage'));
			}
		}
		if ($in['do']) $this->status();
		$where = array(
			'orderid' => (int)$in['orderid'],
		);
		$data = $_mOrder->where($where)->find();
		if ($data['status']<0) {
			$this->message(L('不能操作已经关闭交易的订单！'));
		}
		//修改完以后，保存操作记录
		$this->assign('data',$data);
		$this->display();
	}
	
	/**
	 * @name更改订单状态，已经完成交易或者已经取消的订单，无法修改状态
	 * 
	 */
	protected function status() {
		$in = &$this->in;
		//更改完后，保存操作记录
		$in['dosubmit'] = 1;
		$in['_tablename'] = 'order';
		$in['status'] = intval($in['status']);
		
		if ($this->ispost()) {
			if (! $in ['_tablename'])
				$this->message ( '没有指定操作表！' );
			$name = $in ['_tablename']; //数据表名
			//		die($this->getInTableName($name));
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				if (! empty ( $in ['info'] [$_keyid] )) { //更新
					$keyid = $_m->save ();
				} else { //添加
					$keyid = $_m->add ();
					if ($keyid) $in['info'][$_keyid] = $keyid;
				}
				if (false !== $keyid) { //添加数据
					if (method_exists ( $_m, 'cache' )) { //调用缓存处理;
						$_m->cache ( ($in['info'][$_keyid] ? $in['info'][$_keyid] : $keyid), $in ['info'] );
					}
					//返回处理信息
					if ($in ['ajax'])
						$this->ajaxReturn ( $in ['info'], '记录保存成功！', 1, 'json' );
					else if($in ['_tablename'] == 'menu')
						$this->message ( '记录保存成功！', '', 0, false);
					else
						$this->message ( '记录保存成功！' );
				} else {
					//返回处理信息
					if ($in ['ajax'])
						$this->ajaxReturn ( '', $_m->getError () . '<br />数据保存失败！', 1, 'json' );
					else
						$this->message ( $_m->getError () . '<br />数据保存失败！' );
				}
			} else {
				if ($in ['ajax'])
					$this->ajaxReturn ( '', $_m->getError (), 1, 'json' );
				else
					$this->message ( $_m->getError ().'记录保存失败！' );
			}
		}
		//获取数据
		if (!empty($in['_tablename'])) {
			$name = $in ['_tablename']; //数据表名
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			if ( $in [$_keyid] ) { //编辑
				$keyid = $in [$_keyid] ;
				$data = $_m->find ( $keyid );
				if (isset($data['parentid']) && $data['parentid']>0) {
					$this->assign('parent_data',$_m->find($data['parentid']));
				}
				$this->assign ( 'data', $data );
			}
		}
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
		exit;
	}
	
	/**
	 * @name查看订单详情
	 * 
	 */
	public function show() {
		$in = &$this->in;
		$_mOrder = D ('Order','admin');
		$where = array(
			'orderid' => (int)$in['orderid'],
		);
		$data = $_mOrder->where($where)->find();
		$this->assign('data',$data);
		$this->display();
	}
	
	/**
	 * @name查看某个订单的日志记录
	 * 
	 */
	public function log() {
		$in = &$this->in;
		
		$this->display();
	}
	
	
	/**
	 * @name模块配置
	 * 
	 */
	public function setting() {
		$in = &$this->in;
		
		$this->display();
	}
	
	/*public function delete() {
		
	}*/
}
?>