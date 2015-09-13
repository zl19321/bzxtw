<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Ffriendlink.class.php
// +----------------------------------------------------------------------
// | Date: 2010-07-26
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 辅助插件 - 友情链接管理
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 辅助插件 - 友情链接管理
 *
 */
class FfriendlinkAction extends FbaseAction {
	protected $type_list;
	/**
	 * @name初始化友情链接数据
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'friendlink';
		$this->assign('q_fields', array('name' => '站点名称', 'id' => 'ID', 'type' => '标识符'));
		$this->assign('status', array('0' => '未审核', '1' => '已审核'));
		
		//链接分类
		$_model = M('friendlink_type');
		$this->type_list = $_model->getField('type_id,type_name');
		$this->assign('type_list', $this->type_list);
		
		$this->assign('in', $this->in);
	}
	
	/**
	 * @name管理入口
	 */
	public function index()
	{
		$this->manage();
	}
	
	/**
	 * @name友情链接管理
	 *
	 */
	public function manage()
	{
		$in = &$this->in;
		$where = array();
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`=' . $in['status'];
		}
		if ($in['type_id']) {
			$where[] = ' `type_id`="' . $in['type_id'] . '"';
		}
		if (!empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		$in['order'] = '`sort` ASC';
		
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
	 * @name添加、编辑 友情链接
	 */
	public function add()
	{
		$in = &$this->in;
		$this->assign('upload_dir', C('UPLOAD_DIR'));
		if ($in['do']) {
			$_model = M('Friendlink');
			$data[$in['do']] = $in[$in['do']];
			$_model->where('id=' . $in['id'])->save($data);
			redirect($this->forward);
		} else {
			$in['info']['type_id'] && ($in['info']['type_name'] = $this->type_list[$in['info']['type_id']]);
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
		}
	}
	
	/**
	 * @name编辑
	 */
	public function edit()
	{
		$in = &$this->in;
		if ($in ['ajax']) {
			$this->_edit_ajax ();
		}
	}
	
	/**
	 * @name编辑
	 */
	public function _edit_ajax()
	{
		$in = &$this->in;
		$_model = M('friendlink');
		switch ($in['ajax']) {
			case 'sort':  //排序
				$in['id'] && $in['id'] = (int)substr($in['id'],5);
				if ($in['id'] && !empty($in['sort'])) {
					$data = $_model->find($in['id']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_model->save($data)) {
							//更新缓存
							die($data['sort']);
						}
					}
				}
				break;
			default:
				break;
		}
		exit ();
	}
	
	/**
	 * @name删除 友情链接
	 */
	public function delete()
	{
		$in = &$this->in;
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名
		//		die($this->getInTableName($name));
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
		$_model = D ( $name );
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
	 * @name分类管理
	 */
	public function manage_type()
	{
		$in = & $this->in;
		$in ['_tablename'] = 'friendlink_type';
		
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
	 * @name编辑
	 */
	public function edit_type()
	{
		$in = & $this->in;
		if ($in['ajax']) {
			$this->_edit_type_ajax();
		}
	}
	
	/**
	 * @name 处理友情链接类型的AJAX请求
	 */
	public function _edit_type_ajax()
	{
		$in = &$this->in;
		$_model = M('friendlink_type');
		
		switch ($in['ajax']) {
			case 'type_name':
				$in['id'] && $in['id'] = (int)substr($in['id'],10);
				if ($in['id'] && !empty($in['type_name'])) {
					$data['type_id'] = $in['id'];
					$data['type_name'] = $in['type_name'];
					if (false !== $_model->save($data)) {
						$friendlink_model = M('friendlink');
						$friendlink_model->where('type_id='.$data['type_id'])->save($data);
						die($data['type_name']);
					}
				}
			break;
		}
	}
	
	/**
	 * @name添加分类
	 */
	public function add_type()
	{
		$in = &$this->in;
		
		if ($this->ispost()) {
			$_model = M('friendlink_type');
			$data['type_name'] = $in['info']['type_name'];
			if ($_model->add($data) !== false) {
				redirect($in['forward']);
				exit;
			}
			
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
	}
	/**
	 * @name删除分类
	 */
	public function delete_type()
	{
		$in = &$this->in;
		$_model = M('friendlink_type');
	    $_model->delete($in['id']);
	    redirect(U('ffriendlink/manage_type'));
	}
}
?>