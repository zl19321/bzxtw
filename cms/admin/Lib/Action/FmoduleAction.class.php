<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FmoduleAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-12
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 模块管理
// +----------------------------------------------------------------------

/**
 * @name 模块管理
 *
 */
class FmoduleAction extends FbaseAction {

	/**
	 * @name模块管理
	 */
	public function manage() {
		$in = &$this->in;
		$data = array ();
		$_model = D ( 'Module' );

		//查询条件
		$condition = array ();

		//排序条件
		$order = ' `sort` ASC,`moduleid` DESC ';

		//统计模型数量
		$data ['count'] = $_model->where ( $condition )->count ();

		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_model->where ( $condition )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();

		$this->assign ( 'data', $data );
		$this->display ();
	}

	/**
	 * @name注册模块
	 */
	public function add() {
		$in = &$this->in;
		$_module = D ('Module');
		if ($this->ispost()) { //注册模块。不可扩展模块注册成功后自动注册模型
			if ($_module->register($in['info'])) {
				$this->message('<font class="green">模块注册成功！</font>');
			} else {
				$this->message('<font class="red">模块注册失败！'.$_module->getError() . '</font>');
			}
		}
		$this->display();
	}

	/**
	 * @name注册，编辑，开启、禁用模块、系统模块不能禁用
	 */
	public function edit() {
		$in = &$this->in;
		$_module = D ('Module');
		if ($in['ajax']) $this->_edit_ajax();
		if ($in['do']) {
			switch ($in['do']) {
				case 'status':
					$in['moduleid'] = intval($in['moduleid']);
					if (!$in['moduleid']) $this->message('<font class="red">参数错误，没有指定要操作的记录！</font>');
					if (false === $_module->chageStatus($in['moduleid'],$in['status'])) {
						$this->message('<font class="red">状态更新失败！</font>');
					} else {
						redirect(U('fmodule/manage'));
					}
					break;
				default:
					break;
			}
			exit();
		} else {  //编辑模块
			if ($this->ispost()) {
				if ($in['info']['moduleid']) {
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
				else {
					$this->message('<font class="red">参数错误！</font>');
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
			$in['tpl'] = 'edit';
			if (!empty($in['tpl'])) {
				$this->display ( $in ['tpl'] );
			} else {
				$this->display();
			}
		}
	}

	/**
	 * @name编辑操作的ajax请求
	 */
	protected function _edit_ajax() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'sort':
				$in['moduleid'] && $in['moduleid'] = substr($in['moduleid'],9);
				$in['sort'] = intval($in['sort']);
				if ($in['moduleid'] &&  $in['sort'] == '0' || !empty($in['sort'])) {
					$_module = M ('Module');
					$data = $_module->field("`moduleid`,`sort`")->find($in['moduleid']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_module->save($data)) {
							echo $data['sort'];
							exit ();
						}
					}
				}
				echo '';
				break;
			default:
				break;
		}
		exit ();
	}

	/**
	 * @name删除模块、系统模块不能删除，非系统模块删除后会自动删除对应的模型
	 */
	public function delete() {
		$in = &$this->in;
		$in['moduleid'] && $in['moduleid'] = intval($in['moduleid']);
		if ($in['moduleid']) {
			$_module = D ('Module');
			if (false !== $_module->delete($in['moduleid'])) {
				$this->message('<font class="green">模块卸载成功，请注意删除对应的模块菜单！</font>', U ('fmodule/manage'));
			} else {
				$this->message('<font class="red">'.$_module->getError() . '参数错误，模块不存在！</font>');
			}
		} else {
			$this->message('<font class="red">参数错误，模块不存在！</font>');
		}
	}

	/**
	 * @name更新模块缓存信息
	 */
	public function cache() {
		$in = &$this->in;
		$_module = D('Module');
		if ($in['moduleid']) {	//更新单个缓存
			if (!$_module->cacheModule($in['moduleid'])) $this->error('数据写入失败！');
			else $this->success('缓存更新成功');
		} else { //更新所有缓存
			if (!$_module->cacheAllModule()) $this->error('数据写入失败！');
			else $this->success('缓存更新成功');
		}

	}
}

?>