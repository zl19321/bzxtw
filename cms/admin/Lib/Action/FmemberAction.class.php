<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FmemberAction.class.php
// +----------------------------------------------------------------------
// | Date: 2011-01-18
// +----------------------------------------------------------------------
// | Author: 孙斌 <sunyichi@163.com>
// +----------------------------------------------------------------------
// | 文件描述:  会员模型管理
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 会员模型管理
 *
 */
class FmemberAction extends FbaseAction {
	
	/**
	 * @name会员模型列表
	 */
	public function manage_model() {
		$in = &$this->in;
		$data = array ();
		$_model = D ( 'Model' );
		//查询条件
		$condition = array ('moduleid'=>$in['moduleid']);
		//排序条件
		$order = ' `modelid` ASC ';
		//统计模型数量
		$data ['count'] = $_model->where ( $condition )->count ();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] = $_model->relation ( 'module' )->where ( $condition )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		
		$this->assign ( 'moduleid', $in['moduleid'] );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	
	/**
	 * @name新增模型
	 */
	public function add_model() {
		$in = &$this->in;
		//处理新增模型的时候的ajax请求
		if ($in ['ajax'])
			$this->_ajax_add_model ();
			//处理普通请求
		$_model = D ( 'Model' );
		$_module = D ( 'Module' );
		if ($this->ispost()) { //处理修改
			if (! $_model->autoCheckToken ( $in ))
				$this->message ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );			
			$modelid = $_model->add ( $in ['info'] );
			if ($modelid === true) {
				$this->message ( '<font class="green">模型创建成功！</font>', U("Fmember/manage_model?moduleid={$in['moduleid']}") );
			} else {
				$this->message ( '<font class="red">' . $_model->getError () . '<br />模型创建失败！</font>' );
			}
		}
		//查询可用的模块
		$modules = $_module->selectModule ( array (
			'`status`' => '1',
		) );		
		if (! $modules)
			$this->message ( '<font class="red">没有可用模块！</font>' );
		if (is_array ( $modules )) {
			foreach ( $modules as $k => $v ) {
				$modules [$k] = array (
					'title' => $v, 'value' => $k 
				);
			}
		}
		$this->assign ( 'moduleid', $in['moduleid'] );
		$this->assign ( 'modules', $modules );
		$this->display ();
	}
	
	/**
	 * @name添加模型的AJAX请求
	 */
	protected function _ajax_add_model() {
		$in = &$this->in;
		$_model = D ( 'Model' );
		if ($in ['ajax'] == 'check_tablename') { //检查表名
			$in['info']['exttable'] = trim( $in['info']['exttable'] );
			if (!empty($in['info']['exttable'])) {
				$table = $_model->where("`exttable`='{$in['info']['exttable']}'" )->find ( );
				if (is_array ( $table ))
					die ( 'false' ); //有重复
				else
					die ( 'true' ); //未重复，可以使用
			}
			die ( 'false' );
		} else if ($in ['ajax'] == 'check_name') { //检查模型名称
			$table = $_model->where( "`name`='{$in['name']}'" )->find ( );
			if (is_array ( $table ))
				die ( 'false' ); //有重复
			else
				die ( 'true' ); //未重复，可以使用
		}
		exit ();
	}
	
	/**
	 * @name字段列表
	 */
	public function list_field() {
		$in = &$this->in;
		if (! $in ['modelid'])
			$this->message ( '<font class="red">参数错误！</font>' );
		$_model = D ( 'Model' );
		$_module = D ( 'Module' );
		$_field = D ( 'ModelField' );
		$model_data = $_model->find ( array (
			'where' => array (
			'modelid' => $in ['modelid'] 
		) 
		) );
		if (! is_array ( $model_data )) {
			$this->message ( '<font class="red">该模型不存在！</font>' );
		}
		//检查模型是否是可扩展的		
		if (! $model_data ['extendable'] > 0)
			$this->message ( '<font class="red">该模型是系统模型，不能管理其字段！</font>' );
			//模块字段、扩展模型字段
		$module_where = array (
			'moduleid' => "{$model_data['moduleid']}" 
		);
		$module_data = $_module->field ( 'extendable' )->where ( $module_where )->find ();
		if (! $module_data ['extendable'] > 0)
			$this->message ( '<font class="red">该模型所属模块是系统模块，不管理其字段！</font>' );
		//分页查询
		//排序条件		
		$order = ' `card` ASC ,`sort` ASC ';
		//统计所属模型的字段记录数
		$data ['count'] = $_field->where ( "`modelid`='{$in['modelid']}'" )->count ();
		//初始化分页类
		$in ['p'] = intval ( $in ['p'] );
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据、后台的数据操作都直接操作数据库
		$data ['info'] = $_field->where ( "`modelid`=" . $in ['modelid'] )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		
		$this->assign ( 'data', $data );
		$this->assign ( 'modelid', $in ['modelid'] );
		$this->assign ( 'moduleid', $model_data ['moduleid'] );
		//载入相应的字段类，模板会调用其静态方法
		import ( 'Field', INCLUDE_PATH );
		$this->display ();
	}
	
	/**
	 * @name添加、编辑、复制字段
	 */
	public function add_field() {
		$in = &$this->in;
		if (! $in ['modelid'] && ! $in ['fieldid'] && ! $in ['copy_fieldid']) {
			$this->message ( '<font class="red">参数错误！</font>' );
		}
		//处理添加字段时候的ajax请求
		if ($in ['ajax']) {
			$this->_ajax_add_field ();
		}
		$_model = D ( 'Model' );
		$_mfield = D ( 'ModelField' );
		$data = '';
		if ($this->ispost()) { //处理提交数据
			//检查数据来源
			if (! $_mfield->autoCheckToken ( $in ))
				$this->message ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			$in ['info'] ['modelid'] = $in ['modelid'];
			if ($in ['info'] ['fieldid'] > 0) { //更新数据库				
				$field_data = $_mfield->find ( $in ['info'] ['fieldid'] );
				//可修改部分信息
				$field_data ['name'] = $in ['info'] ['name'];
				$field_data ['tips'] = $in ['info'] ['tips'];
				$field_data ['parent_css'] = $in ['info'] ['parent_css'];
				$field_data ['css'] = $in ['info'] ['css'];
				$field_data ['minlength'] = $in ['info'] ['minlength'];
				$field_data ['maxlength'] = $in ['info'] ['maxlength'];
				$field_data ['required'] = $in ['info'] ['required'];
				$field_data ['pattern'] = $in ['info'] ['pattern'];
				$field_data ['errortips'] = $in ['info'] ['errortips'];
				$field_data ['setting'] = $in ['info'] ['setting'];
				$field_data ['formattribute'] = $in ['info'] ['formattribute'];
				$field_data ['card'] = $in ['info'] ['card'];				
				if (false === $_mfield->save ( $field_data )) {
					$this->message ( '<font class="red">' . $_mfield->getError () . '<br />字段更新失败！</font>' );
				} else {
					$this->message ( '<font class="green">字段更新成功！</font>', $this->forward );
				}
			} else { //插入新纪录
				if (false === $_mfield->add ( $in ['info'] )) {
					$this->message ( '<font class="red">' . $_mfield->getError () . '<br />字段添加失败！</font>' );
				} else {
					$this->message ( '<font class="green">字段添加成功！</font>', $this->forward );
				}
			}
		} else {
			//复制
			if ($in ['copy_fieldid']) {
				$data = $_mfield->find ( $in ['copy_fieldid'] );
				$in ['modelid'] = $data ['modelid'];
				unset ( $data ['fieldid'] );
				unset ( $data ['field'] );
				unset ( $data ['name'] );
				$this->assign ( 'data', $data );
			}
			if ($in ['fieldid']) { //编辑
				$data = $_mfield->find ( $in ['fieldid'] );
				$in ['modelid'] = $data ['modelid'];
				$this->assign ( 'data', $data );
			}
		}
		$model_data = $_model->find ( array ('where' => array ('modelid' => $in ['modelid'] ) ) );
		$this->assign ( 'modelid', $in ['modelid'] );
		$this->assign ( 'moduleid', $model_data ['moduleid'] );
		$this->assign ( 'forward', $this->forward );
		$this->display ();
	}
	
	/**
	 * @name处理添加字段时候的ajax请求
	 */
	protected function _ajax_add_field() {
		$in = &$this->in;
		$_mField = D ( 'ModelField' );
		if ($in ['ajax'] == 'setting') {  //设置
			if (! $in ['type'])
				exit ();
			$fieldid = $in ['copy_fieldid'] ? $in ['copy_fieldid'] : $in ['fieldid'];
			if ($fieldid) {
				$data = $_mField->field ( 'setting' )->find ( $fieldid );
				$html = $_mField->getSettingByType ( $in ['type'], $data ['setting'] );
			} else {
				$html = $_mField->getSettingByType ( $in ['type'] );
			}
			if ($html) {
				die ( $html );
			} else {
				die ( '<font class="red">参数不正确！</font>' );
			}
			exit ();
		}
		if ($in ['ajax'] == 'check') {  //字段名称检查
			if ($in ['modelid'] && empty ( $in ['field'] )) {
				$in ['info']['field'] = trim ( $in ['info']['field'] );
				if (!empty($in ['info']['field'])) {
					$option = array (
						'where' => array (
							'modelid' => $in ['modelid'], 'field' => $in ['info']['field'] 
						) 
					);
					$field_data = $_mField->find ( $option );				
					if (is_array ( $field_data )) {
						die('false');	//重复，不可用						
					} else {
						die('true');	//未重复，可以使用
					}
				}
				die('false');
			} else {
				die('false');	//参数错误
			}
			exit ();
		}
		if ($in ['ajax'] == 'sort') {  //快速排序			
			$in['fieldid'] && $in['fieldid'] = substr($in['fieldid'],6);
			$in['sort'] = intval($in['sort']);
			if ($in['fieldid'] &&  $in['sort'] == '0' || !empty($in['sort'])) {
				$_mField = M ('ModelField');
				$data = $_mField->field("`fieldid`,`sort`")->find($in['fieldid']);
				if (is_array($data)) {
					$data['sort'] = $in['sort'];
					if (false !== $_mField->save($data)) {
						echo $data['sort'];
						exit ();
					}
				}
			}
			echo '';
			exit();
		}
		if ($in ['ajax'] == 'savename') {  //快速编辑字段别名
			$in['fieldid'] && $in['fieldid'] = substr($in['fieldid'],5);
			if ($in['fieldid'] && !empty($in['name'])) {
				$_mField = M ('ModelField');
				$data = $_mField->field("`fieldid`,`name`")->find($in['fieldid']);				
				if (is_array($data)) {
					$data['name'] = $in['name'];
					if (false !== $_mField->save($data)) {
						echo $data['name'];
						exit ();
					}
				}
			}
			echo '';
			exit();
		}
		exit ();
	}
	
	/**
	 * @name编辑模型，不可扩展的模型不能编辑，模型的编辑总只能更新模型的名称和状态
	 * 
	 */
	public function update_model() {
		$in = &$this->in;
		//处理编辑模型的时候的ajax请求
		if ($in ['ajax'])
			$this->_ajax_update_model ();
			//处理普通请求
		$_model = D ( 'Model' );
		$_module = D ( 'Module' );
		$data = $_model->find ( $in ['modelid'] );
		if (! is_array ( $data )) {
			$this->message ( '<font class="red">要修改的模型不存在！</font>' );
		}
		//查询当前模型所属模块
		if ($this->ispost()) { //处理修改
			if (! $_model->autoCheckToken ( $in )) {
				$this->error ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			}
			//更新条件
			$option = array (
				'where' => array (
				'modelid' => $in ['modelid']
			) 
			);
			//name,status,description 几个数据才能更新，其余数据都是不可修改的
			$data ['name'] = $in ['info'] ['name'];
			$data ['status'] = $in ['info'] ['status'];
			$data ['description'] = $in ['info'] ['description'];
			if ($_model->update ( $data, $option )) {
				$this->message ( '<font class="green">模型编辑成功！</font>', $this->forward );
			} else {
				$this->message ( '<font class="red">' . $_model->getError () . '模型编辑失败！</font>' );
			}
		}
		$this->assign ( 'modules', $_module->find ( $data ['moduleid'] ) );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	
	/**
	 * @name处理模型更新时候的ajax请求
	 */
	protected function _ajax_update_model() {
		$in = &$this->in;
		$_model = D ( 'Model' );
		if (! $in ['modelid'])
			$this->ajaxReturn ( 'e', '异常错误' ); //异常错误
		if ($in ['ajax'] == 'check_name') { //检查模型名称
			$table = $_model->find ( "`name`='{$in['name']}' AND `modelid`<>'{$in['modelid']}'" );
			if (is_array ( $table ))
				$this->ajaxReturn ( 'n', '重复，不可使用' ); //有重复
			else
				$this->ajaxReturn ( 'y', '未重复，可以使用' ); //未重复，可以使用
		}
		$this->ajaxReturn ( 'e', '异常错误' ); //异常错误
	}
	
	/**
	 * @name删除模型
	 */
	public function del_model() {
		$in = &$this->in;
		if (! $in ['modelid'])
			$this->message ( '<font class="red">参数错误！</font>' );
		$_model = D ( 'Model' );
		$model_data = $_model->find ( array ('where' => array ('modelid' => $in ['modelid'] ) ) );
		if ($_model->delete ( $in ['modelid'] )) {
			//删除模型字段信息			
			$this->message ( '<font class="green">成功删除模型！</font>', U ( "Fmember/manage_model?moduleid={$model_data ['moduleid']}" ) );
		} else {
			$this->message ( '<font class="red">' . $_model->getError () . '操作失败！</font>' );
		}
	}
	
	/**
	 * @name删除字段
	 */
	public function del_field() {
		$in = &$this->in;
		if (! $in ['fieldid'])
			$this->message ( '<font class="red">非法参数！</font>' );
		$_mField = D ( 'ModelField' );
		if (! $_mField->delete ( $in ['fieldid'] )) {
			$this->message ( '<font class="red">' . $_mField->getError () . '删除失败！</font>' );
		} else {
			$this->message ( '<font class="green">字段删除成功！</font>' );
		}
	}
	
	
	/**
	 * @name启用、禁用模型
	 */
	public function status_model() {
		$in = &$this->in;
		if (! $in ['modelid'] || ! in_array ( $in ['status'], array (
			0, 1 
		) ))
			$this->message ( '<font class="red">参数错误！</font>' );
		$_model = D ( 'Model' );
		$model_data = $_model->find ( array (
			'modelid' => "{$in['modelid']}" 
		) );
		if (! is_array ( $model_data ))
			$this->message ( '<font class="red">发生异常，要操作的模型不存在！</font>' );
			//如果是开启，则先检查关联的模块是否开启
		if ($in ['status'] == 1) {
			$_module = D ( 'Module' );
			$module_data = $_module->find ( array (
				'modelid' => "{$in['modelid']}" 
			) );
			if (is_array ( $module_data )) {
				if ($module_data ['status'] == 0)
					$this->message ( '<font class="red">模型所属模块已经禁用，如果需要开启模型，请先将模块启用。</font>' );
			} else {
				$this->message ( '<font class="red">发生异常，模型所属模块不存在！</font>' );
			}
		}
		//启用、禁用模型、并更新缓存
		$_model->status ( $in ['modelid'], $in ['status'] );
		redirect ( $this->forward );
	}
	
	/**
	 * @name启用、禁用字段
	 */
	public function status_field() {
		$in = &$this->in;
		if (! $in ['fieldid'])
			$this->message ( '<font class="red">非法参数</font>' );
		$_mField = D ( 'ModelField' );
		$field_data = $_mField->find ( intval ( $in ['fieldid'] ) );
		if (! is_array ( $field_data )) {
			$this->message ( '<font class="red">字段不存在！</font>' );
		} else {
			if ($field_data ['systype'] == '2') {
				$this->message ( '<font class="red">系统字段，不能操作！</font>' );
			} else {
				$in ['status'] = intval ( $in ['status'] ) > 0 ? '1' : '0';
				$field_data ['status'] = $in ['status'];
				if (false !== $_mField->save ( $field_data )) {
					redirect ( $this->forward );
				} else {
					$this->message ( '<font class="red">操作失败！</font>' );
				}
			}
		}
	}
	
	/**
	 * @name预览模型、只有扩展模型才能预览
	 */
	public function preview_model() {
		$in = &$this->in;
		if (! $in ['modelid'])
			$this->message ( '<font class="red">参数错误！</font>' );
			//检查模型是否可扩展	
		$_model = D ( 'Model' );
		$model_data = $_model->find ( $in ['modelid'] );
		if (! is_array ( $model_data ))
			$this->message ( '<font class="red">该模型不存在或者已经被删除！</font>' );
		$_mField = D ( 'ModelField' );
		//字段系统类型   systype   0=自定义字段  1=主表默认字段  2=系统字段(主键字段)
		$field_data = $_mField->where ( "`modelid`='{$in['modelid']}' AND `status`='1' AND `systype`<>'2'" )->order ( "`sort` ASC,`fieldid` ASC " )->findAll ();
		//print_r($field_data);exit;
		$form_data = $_model->getForm($field_data);
		$this->assign ( 'data', $model_data );
		$this->assign ( 'form_data', $form_data );
		$this->display ();
	}
	
	/**
	 * @name导出模型
	 */
	public function export_model() {
		$in = &$this->in;
		$this->message ( '<font class="red">建设中...</font>' );exit;
		if (! $in ['modelid'])
			$this->message ( '<font class="red">参数错误！</font>' );
		$_model = D ( 'Model' );
		$filename = $_model->export ( $in ['modelid'] );
		if (false !== $filename) {
			import ( 'ORG.Net.Http' );
			Http::download ( $filename );
		} else {
			$this->message ( '<font class="red">模型不存在！</font>' );
		}
	}
	
	
	/**
	 * @name会员角色管理
	 */
	public function manage_group() {
		$in = &$this->in;
		$in ['_tablename'] = 'role';
		$in['where'] = array("isadmin"=>0);  //"status"=>1
		
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
		//$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$data ['info'] = $_m->relation ( 'model' )->where ( $option['where'] )->order ( $option['order'] )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		$this->assign ( 'data', $data );
		$this->assign ( 'moduleid', $in['moduleid'] );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name添加会员角色
	 */
	public function add_group() {
		$in = &$this->in;
		if ($in ['ajax'])
			$this->_ajax_add_group ();
		$in ['_tablename'] = 'role';
		$this->assign('forward',U("fmember/manage_group?moduleid={$in['moduleid']}"));
		
		if ($this->ispost()) {
			if (! $in ['_tablename']){
				$this->message ( '没有指定操作表！' );
			}
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
		//查询可用的会员模型
		$_model = D ( 'Model' );
		$where = "`status`='1' AND `moduleid`='{$in['moduleid']}' AND `exttable`<>'manager'";
		$models = $_model->selectModel($where);
		//print_r($models);exit;
		if (!$models)
			$this->message ( '<font class="red">没有可用的用户模型！</font>' );
		if (is_array ( $models )) {
			foreach ( $models as $k => $v ) {
				$models [$k] = array (
					'title' => $v, 'value' => $k 
				);
			}
		}
		$this->assign ( 'models', $models );
		$this->assign ( 'moduleid', $in['moduleid'] );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name处理添加时候的ajax请求
	 */
	protected function _ajax_add_group() {
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
	 * @name编辑会员角色
	 */
	public function update_group() {
		$in = &$this->in;
		if ($in ['do'] == 'status') $this->status_group();
		$in ['_tablename'] = 'role';
		$in ['tpl'] = 'update_group';
		$this->assign('forward',U("fmember/manage_group?moduleid={$in['moduleid']}"));
		
		if ($this->ispost()) {
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
		$_model = D ( 'Model' );
		$this->assign ( 'models', $_model->find ( $data ['modelid'] ) );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name删除会员角色
	 */
	public function delete_group() {
		$in = &$this->in;		
		$in ['_tablename'] = 'role';
		$this->assign('forward',U("fmember/manage_group?moduleid={$in['moduleid']}"));
		
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
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
	 * @name启用、禁用会员角色
	 */
	public function status_group() {
		$in = &$this->in;
		if (! $in ['role_id'])
			$this->error ( '非法参数' );
		$_role = D ( 'Role' );
		$data = $_role->find ( intval ( $in ['role_id'] ) );
		if (! is_array ( $data )) {
			$this->error ( '会员角色不存在！' );
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
	
	/**
	 * @name会员模块配置
	 */
	public function setting_module (){
		$in = &$this->in;
		$key = 'user';
		$_setting = D ('Setting');
		if ($this->ispost()) {
			if (is_array($in['info'])) {
				foreach ($in['info'] as $k=>$v) {
					$_setting->set($k,$v,$key);
				}
				//更新缓存和配置
				$_setting->cacheAll();
				R('Fcache','app');
				$this->message('<font class="green">会员模块配置保存成功！</font>',U('fmember/setting_module'));
			} else {
				$this->message('<font class="red">' . "参数错误！</font>");
			}
		}
		$value_data = $_setting->where("`key`='{$key}'")->findAll();
		if (is_array($value_data)) {
			foreach ($value_data as $k=>$v) {
				$value_data[$v['var']] = $v['value'];
				unset($value_data[$k]);	
			}
		}
		$this->assign ('value_data', $value_data);
		$this->display ();
	}
}
?>