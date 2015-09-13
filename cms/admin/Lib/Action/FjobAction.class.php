<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FjobAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-09-17
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 辅助插件 - 人才招聘
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 辅助插件 - 人才招聘
 *
 */
class FjobAction extends FbaseAction {
	/**
	 * @name初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'job';
		
		if ($in['catid']) {
			$category = F('category_'.$in['catid'], '', ALL_CACHE_PATH.'cache/');
			$this->assign('category', $category);
			$this->assign('catid', $in['catid']);
			$this->checkPermissions($_SESSION['userdata'], $category['permissions']['admin']);
		} else $this->message('<font class="red">缺少catid参数！</font>');
		
		$this->assign('q_fields', array('name' => '职位名称', 'id' => '职位ID', 'department' => '所属部门'));
		$this->assign('status', array('0' => '待审', '1' => '已审'));
		$this->assign('in', $this->in);
	}
	
	/**
	 * @name检查权限
	 * @param $userData
	 * @param $permissions
	 */
	private function checkPermissions($userData,$permissions) {
		if ($userData['username'] == 'developer') return true;
		$has = false;
		if (is_array($userData['roles'])) {
			foreach ($userData['roles'] as $v) {  //如果有一个角色有权限   那就有权限
				if (in_array($v,$permissions[ACTION_NAME])) $has = true;
			}
		}
		if (!$has) {
			$this->message('<font class="red">无权访问！</font>');
		}
		return true;
	}
	
	/**
	 * @name职位列表
	 */
	public function manage()
	{
		$in = &$this->in;
		$where = array();
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`="' . $in['status'] . '"';
		}
		if (isset($in['q']) && !empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		
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
	 * @name添加职位
	 */
	public function add()
	{
		$in = &$this->in;
		$this->assign('end_time', date('Y-m-d', time()+3600*24*31*12));
		$this->assign('editer_html', Html::editor('info[notes]', '', C('EDITOR_TYPE'), array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
		if ($this->ispost()) {
			$in['info']['create_time'] = $in['info']['update_time'] = time();
			$in['info']['end_time'] = strtotime($in['info']['end_time']);
			
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
	 * @name编辑职位
	 */
	public function edit()
	{
		$in = &$this->in;
		
		if (!$in['id']) $this->message('<font class="red">参数不完整！</font>');
		if ($in['ajax']) {
			$this->_ajax_edit();
			exit;
		}
		if ($this->ispost()) {
			$in['info']['update_time'] = time();
			$in['info']['end_time'] = strtotime($in['info']['end_time']);
			
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
		$job = M('Job')->find($in['id']);
		$this->assign('editer_html', Html::editor('info[notes]', $job['notes'], C('EDITOR_TYPE'), array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
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
	 * @nameAJAX编辑
	 */
	private function _ajax_edit()
	{
		$in = &$this->in;
		$_model = M('Job');
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
	 * @name修改状态
	 */
	public function check()
	{
		$in = &$this->in;
		if (!$in['id']) $this->message('<font class="red">参数不完整！</font>');
		$data = array(
			'id'	=>	$in['id'],
			'status'	=>	$in['status']
		);
		M('Job')->save($data);
		redirect($this->forward);
	}
	
	/**
	 * @name删除职位
	 */
	public function delete()
	{
		//$this->in['id'] = $this->in['info']['id'];
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
        //fangfa 2013-1-15 解决不能删除的问题
        if($in['info']['id']){
            $id = implode(',',$in['info']['id']);
            $option['where'] = " `id` in (".$id.") ";
        }else{
            $id = $in['id']; 
            $option['where'] = " `id` in (".$id.") ";
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
	 * @name简历列表
	 */
	public function resume_manage()
	{
		$in = &$this->in;
		$in['_tablename'] = 'job_apply';
		if (!array_key_exists('job_id', $in)) $this->message('<font class="red">参数不完整！</font>');
		
		$where = array();
		$where[] = ' `job_id`=' . $in['job_id'];
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`="' . $in['status'].'"';
		}
		if (isset($in['user_sex']) && $in['user_sex'] != 'all') {
			$where[] = ' `user_sex`="' . $in['user_sex'].'"';
		}
		if (isset($in['q']) && !empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		
		$this->assign('status', array('未阅' => '未阅', '已阅' => '已阅', '候选' => '候选', '在职' => '在职'));
		$this->assign('user_sex', array('all'=>'不限', '男' => '男', '女' => '女'));
		$this->assign('q_fields', array('user_name' => '姓名', 'user_card' => '身份证'));
		
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
	 * @name查看简历
	 */
	public function resume_show() {
			$in = &$this->in;
			if (empty($in['apply_id'])) {
				die('缺少参数apply_id！');
			}
			$_model = M('job_apply');
			$data = $_model->find($in['apply_id']);
			
			$this->assign('data', $data);
			$this->assign('status', array('未阅' => '未阅', '已阅' => '已阅', '候选' => '候选', '在职' => '在职'));
			$this->display();
	}
	
	/**
	 * @name删除简历
	 */
	public function resume_delete()
	{
		$in = &$this->in;
		$in['_tablename'] = 'job_apply';
		if (!empty($in['info']['id'])) {
			$this->in['id'] = $this->in['info']['id'];
		}
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
	 * @name编辑简历
	 */
	public function resume_edit()
	{
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_resume_edit();
		}
	}
	
	/**
	 * @nameAJAX编辑
	 */
	protected function _ajax_resume_edit()
	{
		$in = &$this->in;
		$_model = M('job_apply');
		$data['status'] = $in['status'];
		$data['id'] = $in['job_apply_id'];
		if ($_model->save($data)) {
			die(json_encode('ok'));
		} else die(json_encode('error'));
	}
}
?>