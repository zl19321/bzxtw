<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FrbacAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-2
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述:  角色管理
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 角色管理
 *
 */
class FroleAction extends FbaseAction {
	
	/**
	 * @name角色管理
	 */
	public function manage() {
		$in = &$this->in;
		$in ['_tablename'] = 'role';
		$in['where'] = array("isadmin"=>1);  //"status"=>1
		
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
		$this->assign ( 'modelid', $in['modelid'] );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name添加角色
	 */
	public function add() {
		$in = &$this->in;
		if ($in ['ajax'])
			$this->_add_ajax ();
		$in ['_tablename'] = 'role';
		$this->assign('forward',U("frole/manage?modelid={$in['modelid']}"));
		
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
		$this->assign ( 'modelid', $in['modelid'] );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name处理添加时候的ajax请求
	 */
	protected function _add_ajax() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'checkname':
				$_role = D ( 'Role' );
				if (!empty($in ['info']['name']) && $_role->regex($in ['info']['name'],'english')) {
					if ($_role->isNameExists($in['info']['name'])) {
						die('false');
					}
					die('true');
				}
				die('false');
				break;			
			default:				
				break;
		}
		exit ();
	}
	
	/**
	 * @name编辑角色
	 */
	public function edit() {
		$in = &$this->in;
		if ($in ['do'] == 'status') $this->status();
		$in ['_tablename'] = 'role';
		$in ['tpl'] = 'edit';
		
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
		$this->assign('forward',U("frole/manage?modelid={$data['modelid']}"));
		
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
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name删除角色
	 */
	public function delete() {
		$in = &$this->in;		
		$in ['_tablename'] = 'role';		
		
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
		$_model = D ( $name );
		$data = $_m->find ( $in [$_keyid] );
		$this->assign('forward', U("frole/manage?modelid={$data['modelid']}"));
		//安全起见，必须包含删除的记录的主键，或者删除条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		}
		if ($in [$_keyid] ) { //主键筛选
			if (is_array($in[$_keyid])) {
				if (!empty($in [$_keyid])) {
					$option ['where'] = " `{$_keyid}` IN (".implode(',', $in[$_keyid]) .")";
				}
			} else {
				$option ['where'] = array ($_keyid => $in [$_keyid] );
			}
		}
		if ($in ['where']) {
			if (!empty($option ['where'])) {
				@$option['where'] = array_merge($in['where'],$option ['where']);
			} else {
				$option['where'] = &$in['where'];
			}
		}
		if (! empty ( $option )) {
			if (false !== $_m->delete($option)) {
				if (method_exists ( $_m, 'cache' )) { //删除缓存
					if (is_array($in[$_keyid])) {
						if (!empty($in [$_keyid])) {
							foreach ($_keyid as $k) {
								$_m->cache ( $k , null );
							}
						}
					} else if (is_numeric($in [$_keyid])) {
						$_m->cache ( $_keyid , null );
					}
				}
				$this->message('删除成功！');
			} else {
				$this->message($_m->getError() . '删除失败！');
			}
		} else {
			$this->message ( '参数错误，没有指定删除条件！' );
		}
	}
	
	/**
	 * @name启用、禁用角色
	 */
	public function status() {
		$in = &$this->in;
		if (! $in ['role_id'])
			$this->error ( '非法参数' );
		$_role = D ( 'Role' );
		$data = $_role->find ( intval ( $in ['role_id'] ) );
		if (! is_array ( $data )) {
			$this->error ( '角色不存在！' );
		} else {
			$in ['status'] = intval ( $in ['status'] ) > 0 ? '1' : '0';
			$data ['status'] = $in ['status'];
			if (false !== $_role->save ( $data )) {
				redirect ( $this->forward );
			} else {
				$this->message ( '<font class="red">操作失败！</font>' );
			}
		}
		exit ();
	}
	
	
}
?>