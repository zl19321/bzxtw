<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FtagAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 广告管理
// +----------------------------------------------------------------------
defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 广告管理
 *
 */
class FadAction extends FbaseAction {
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'ad';
		if ($in['ajax']) {
			$this->_ajax();
		}
	}
	
	/**
	 * @name标签管理
	 */
	public function manage() {
		$in = &$this->in;
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();

		//操作条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		} else {
			$option['order'] = "`{$_keyid}` DESC ";
		}
		if ( $in [$_keyid] ) { //主键筛选
			$option ['where'] = array ($_keyid => $in [$_keyid] );
		}
		if ($in ['where']) {
			$option['where'] = &$in['where'];
		}

		//获取数据
		//初始化分页类
		$data = array ();

		//统计记录数
		$data ['count'] = $_m->where ( $option['where'] )->count ();

		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name添加广告
	 */
	public function add() {
		$in = &$this->in;
		if ($this->ispost()) {
			$in['info']['setting'] = $in['info'][$in['info']['type']]['setting'];
			$in['forward'] = U ('fad/manage');
			if (! $in ['_tablename'])
				$this->message ( '没有指定操作表！' );
			$name = $in ['_tablename']; //数据表名
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
		$this->assign('nowtime',time());
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
	}
	
	/**
	 * @name编辑广告
	 */
	public function edit() {
		$in = &$this->in;
		$in['aid'] = intval($in['aid']);
		$_ad = D ('Ad');
		if ($this->ispost()) {			
			$in['info']['setting'] = $in['info'][$in['info']['type']]['setting'];			
			$in['forward'] = U ('fad/manage');
			if (! $in ['_tablename'])
				$this->message ( '没有指定操作表！' );
			$name = $in ['_tablename']; //数据表名
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
		
		if (!$in['aid']) $this->message('<font class="red">参数错误！</font>');
		$data = $_ad->where("`aid`='{$in['aid']}'")->find();		
		$this->assign('data',$data);
		$this->display();
	}
	
	/**
	 * @name处理ad方面的ajax请求
	 */
	protected function _ajax() {
		$in = &$this->in;
		switch($in['ajax']) {
			case '':
				break;
		}
		exit ();
	}
	
	/**
	*@nameAJAX获取AD表单
	*/
	public function ajax_getform(){
		$in = &$this->in;
		if($in['is_ajax']){
			if(isset($in['form_id'])){
				if(isset($in['aid'])){
					$in['aid'] = intval($in['aid']);
					$_ad = D ('Ad');
					$data = $_ad->where("`aid`='{$in['aid']}'")->find();		
					$this->assign('data',$data);
				}
				$this->display('ad_'.$in['form_id']);
				exit();
			}
		}
	}

	/**
	 * @name预览
	 */
	public function preview() {
		$in = &$this->in;
		$_ad = D ('Ad');
		$in['aid'] = intval($in['aid']);
		if (!$in['aid']) $this->message('<font class="red">参数错误！</font>');		
		$data = $_ad->find($in['aid']);
		if (!empty($data)) {
			import('Ad',INCLUDE_PATH);
			$ad = new Ad($data['type'],$data['setting']);
			echo $ad->get();
		}
	}
    
    public function delete(){
        
        $in = &$this->in;
        $_ad = D ('Ad');
        $in['aid'] = intval($in['aid']);
        if (!$in['aid']) $this->message('<font class="red">参数错误！</font>');	
        $_ad->where('aid = '.$in['aid'])->delete();
        $this->message ( '删除成功！' );
    }
}
?>