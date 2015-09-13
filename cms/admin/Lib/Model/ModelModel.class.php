<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ModelModel.php
// +----------------------------------------------------------------------
// | Date: 2010-5-7
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 模型管理Model
// +----------------------------------------------------------------------


class ModelModel extends RelationModel {
	
	/**
	 * 关联定义
	 * @var unknown_type
	 */
	public $_link = array (
		'module' => array (
		'mapping_type' => BELONGS_TO, 
		'class_name' => 'Module', 
		'foreign_key' => 'moduleid', 
		'mapping_fields' => 'moduleid,name,controller,ishtml' 
	) 
	);
	
	/**
	 * 验证数据
	 * @param array $_validate
	 * @param boolean 是否是扩展模型
	 */
	public function validate($data = array(), $extend = true, $add = true) {
		$return = true;
		if (! parent::regex ( $data ['exttable'], 'require' ) && $extend) {
			$this->error .= "模型标识必须<br />";
			$return = false;
		}
		if (! empty ( $data ['tablename'] ) && $add) { //检查标识是否重复
			$table = $this->where ( "`exttable`='{$data['exttable']}'" )->find ();
			if (is_array ( $table )) {
				$this->error .= "模型标识重复<br />";
				return false;
			}
		}
		if (! parent::regex ( $data ['name'], 'require' )) {
			$this->error .= "模型名称必须<br />";
			$return = false;
		}
		if (! parent::regex ( $data ['moduleid'], 'number' )) {
			$this->error .= "必须指定所属模块<br />";
			$return = false;
		}
		if (! parent::regex ( $data ['status'], 'number' ) || ! in_array ( $data ['status'], array (
			0, 1 
		) )) {
			$this->error .= "请指定模型状态<br />";
			$return = false;
		}
		return $return;
	}
	
	/**
	 * 新增模型、所有从fmodelAction处添加的都是扩展模型
	 * @param array $data
	 */
	public function add($data = array()) { //事务型操作，需要数据库支持
		if (!$data['moduleid']) return false;
		if(!isset($data['extendable'])){
			$module_data = F ('module_'.$data['moduleid']);
			$data['extendable'] = $module_data['extendable'];
		}
		if ($data ['extendable']) //判断是否扩展模型
			$extend = true;
		else
			$extend = false;
			//进行数据验证
		if (! $this->validate ( $data, $extend ))
			return false;
			//开始事务
		$modelid = '';
//		$this->startTrans ();
		//获取module对应模块的module主记录表主键信息
		$_module = D ( 'Module' );
		$_module->find ( $data ['moduleid'] );
		if (empty($data ['tablename']) && !empty($data['exttable'])) { //组合需要指定完整扩展表名: 模块主记录表+模型标识，以 '_' 分开			
			$data ['tablename'] = $_module->tablename . '_' . $data ['exttable'];
		}
		$modelid = parent::add ( $data );
		if ($modelid) { //模型表记录创建成功
			if ($extend) {  //可扩展模块
				//添加主表字段记录到field表
				$sql_file = ALL_CACHE_PATH . 'model_sql/' . $_module->tablename . '_model.sql';
				if (! file_exists ( $sql_file )) {
					$this->error .= "文件 '$sql_file' 不存在！<br />";
					return false;
				}
				$sql = file_get_contents ( $sql_file );
				//更正表名称，字段名称.替换 {DB_PREFIX} {TABLENAME} {EXTTABLE} {MODELID}
				$sql = str_replace ( '{DB_PREFIX}', C ( 'DB_PREFIX' ), $sql );
				$sql = str_replace ( '{EXTTABLE}', $data ['exttable'], $sql );
				$sql = str_replace ( '{TABLENAME}', $data ['tablename'], $sql );
				$sql = str_replace ( '{MODELID}', $modelid, $sql );                                
				$sqls = explode ( ";", trim ( $sql ) );
				//执行建表以及添加model_field操作
				if (is_array ( $sqls )) {
					$f = true;
					foreach ( $sqls as $s ) {
						$s = trim($s);
						if (!empty($s )) {
							if (false === $this->query ( $s ))
								$f = false;
						}
					}
				}
				if ($f) { //如果所有语句都执行成功
//					$this->commit (); //提交事务
					//更新缓存
					$this->cache ( $modelid );
					//更新模型字段缓存
					D('ModelField')->cacheAll($modelid);
					return $modelid;
				} else { //如果有语句执行失败
					$this->error .= $sql_file . '文件中语句无法完全成功执行！<br />';
//					$this->rollback (); //事务回滚
					return false;
				}
			}
//			$this->commit (); //提交事务
			//更新缓存
			$this->cache ( $modelid );
			return true;
		} else {
			$this->error .= '模型记录添加失败！<br />';
//			$this->rollback (); //事务回滚
			return false;
		}
	}
	
	/**
	 * 新增模型
	 * @param unknown_type $data
	 */
	//	public function add($data = array()) { //非事务型操作
	//		if ($modelid = parent::add ( $data )) { //添加
	//			//获取module对应模块的module主记录表主键信息
	//			$_module = D ( 'Module' );
	//			$_module->find ( $data ['moduleid'] );						
	//			if (! empty ( $_module->tablename )) {
	//				//获取对应模块的主记录表的主键
	//				$pk = M(ucfirst($_module->tablename))->getPk ();
	//				$sql = "CREATE TABLE `" . C ( 'DB_PREFIX' ) . $_module->tablename . '_' . $data ['tablename'] . "` (
	//						`{$pk}` INT UNSIGNED NOT NULL 						
	//						) ENGINE = InnoDB ";
	//				if ($this->query ( $sql )) { //添加数据表
	//					return true;
	//				}
	//			}		
	//		} 
	//		//上面有步骤执行失败、删除模型表记录
	//		$this->where ( "`modelid`={$modelid}" )->delete ();
	//		return false;
	//	}
	

	/**
	 * 更新模型信息、所有从fmodelAction处更新的都是扩展模型
	 * @param array $data
	 */
	public function update($data = array(), $option) { //事务型操作，需要数据库支持
		//判断是否扩展模型
		if ($data ['extend'])
			$extend = true;
		else
			$extend = false;
			//进行数据验证
		if (! $this->validate ( $data, $extend, false ))
			return false;
			//更新模型数据表
		if (false !== parent::save ( $data, $option )) {
			$this->cache ( $data ['modelid'], $data );
			return true;
		} else {
			return false;
		}
	
	}
	
	public function delete($modelid) {
		$where = array (
			"modelid" => "{$modelid}" 
		);
		$model_data = $this->find ( $modelid );
		if (false !== parent::delete ( $modelid )) {
			//删除字段信息，使用虚拟模型类，因为model中的delete方法在ModelField子类中被改写了
			$_field = M ( 'ModelField' );
			$fieldId  = $_field->field("`fieldid`")->where($where)->findAll();
			//清除字段记录
			$_field->where ( $where )->delete ();
			if (empty ( $model_data ['tablename'] ))
				return false;
				//删除物理表
			$drop_sql = "DROP TABLE `" . C ( 'DB_PREFIX' ) . $model_data ['tablename'] . "`";
			$this->execute ( $drop_sql );
			//清除字段缓存
			if (is_array($fieldId)) {
				foreach ($fieldId as $v) {
					F ('fieldModel_'.$v['fieldid'], null);
				}
			}
			//清除模型缓存
			F ( 'model_' . $modelid, null );
			
			return true;
		}
		return false;
	}
	
	/**
	 * 更新模型状态
	 * @param int $modelid
	 * @param int $status  0=禁用  1=启用
	 */
	public function status($modelid, $status) {
		if (! $modelid)
			return false;
		$data = $this->find ( $modelid );
		if (! is_array ( $data )) {
			return false;
		}
		$data ['status'] = $status;
		if ($this->save ( $data )) {
			$this->cache ( $modelid, $data );
			return true;
		} else {
			return false;
		}
	
	}
	
	/**
	 * 根据字段信息，取得模型表单信息
	 * 
	 * @param array $field_data 模型字段信息数组
	 * @param array $value 各字段的值  键值对   例如  array( 'catid'=>1, title=>'test'.....  )
	 * @param array $extra 额外的附加属性，'字段名'=>'附加属性代码' 的 形式，如： array('url'=>'readonly','test'=>'')
	 */
	public function getForm($field_data,$value = array(),$extra = array()) {

		if (!is_array($field_data)) return false;
		//载入Field
		import ( 'Field', INCLUDE_PATH );
		$data = array();
		foreach ($field_data as $k=>$v) {
			if (!empty($value)) {
				if (isset($value[$v['field']])) {
					$v['value'] = $value[$v['field']];
					if($v['field'] === 'classify_select'){
						$v['classify_id'] = $value['catid'];
					}
				}
			}
			//名称
			$data[$v['card']][$k]['name'] = $v['name'];
			//提示
			//$v['tips'] = htmlspecialchars($v['tips']);
			$v['tips'] = htmlspecialchars_decode($v['tips']);
			$data[$v['card']][$k]['tips'] = $v['tips']; //转义特殊html字符
			//元素的额外属性 css pattern minlength maxlength required errortips formattribute(javascript事件)
			$v['errortips'] = htmlspecialchars($v['errortips']);
			$v['formattribute'] = htmlspecialchars_decode($v['formattribute']);
			$class = '';
			!empty($v['css']) && $class .= " {$v['css']} ";
			!empty($v['required']) && $class .= " required ";
			$v['attribute'] .= " class=\"{$class}\" ";
			!empty($v['minlength']) && $v['attribute'] .= " minlength=\"{$v['minlength']}\" ";
			!empty($v['maxlength']) && $v['attribute'] .= " maxlength=\"{$v['maxlength']}\" ";
			!empty($v['errortips']) && $v['attribute'] .= " errortips=\"{$v['errortips']}\" ";
			//!empty($v['tips']) && $v['attribute'] .= " title=\"{$v['tips']}\" ";
			!empty($v['formattribute']) && $v['attribute'] .= " {$v['formattribute']} ";
			!empty($v['pattern']) && $v['attribute'] .= " pattern=\"{$v['pattern']}\" ";
			!empty($extra[$v['field']]) && $v['attribute'] .= " {$extra[$v['field']]} "; 
			$v['setting']['size'] && $v['attribute'] .= " size=\"{$v['setting']['size']}\" ";
			$data[$v['card']][$k]['form'] = Field::form($v['formtype'],$v);
		}
		ksort($data);
		return $data;
	}
	
	/**
	 * 按条件查询获取模型
	 */
	public function selectModel($condition = '') {			
		//查询结果
		$retuslt = $this->where($condition)->findAll();
		if (is_array ( $retuslt )) {
			$data = array();
			foreach ( $retuslt as $k => $v ) {
				$data [$v ['modelid']] = $v ['name'];
				unset ( $retuslt [$k] );
			}
			return $data;
		} else {
			return false;
		}
	}
    
	/**
	 * 按条件查询获取模型内容
	 */
	public function selectonesModel($condition = '') {			
		//查询结果
		$retuslt = $this->where($condition)->find();
		if (!empty ( $retuslt )) {
			return $retuslt;
		} else {
			return false;
		}
	}    
	
	/**
	 * 导出模型
	 * @param unknown_type $modelid
	 */
	public function export($modelid) {
		$modelid = intval ( $modelid );
		if ($modelid < 1)
			return false;
		$model_data = $this->find ( $modelid );
		$result = array ();
		F ( 'model_' . $modelid . '.model.php',$model_data );
		$model_file = ALL_CACHE_PATH . 'cache/model_' . $modelid . '.model.php';
		if (file_exists ( $model_file )) {
			return $model_file;
		}
		return false;
	}
	
	/**
	 * 缓存单个模型信息
	 * @param int $modelid
	 */
	public function cache($modelid, $data = '') {
		if (empty ( $data ) || !is_array ( $data )) {
			$data = $this->find ( $modelid );
		}
		if (is_array ( $data )) {
			if ($data ['modelid'] == $modelid) {
				F ( 'model_' . $modelid, $data );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 缓存所有的模型信息
	 */
	public function cacheAll() {
		$data = $this->findAll ();
		if (is_array ( $data )) {
			$_mField  = D('ModelField','admin');
			foreach ( $data as $v ) {
				F ( 'model_' . $v ['modelid'], $v );
				F ($v['tablename'] , null, ALL_CACHE_PATH.'cache/_fields/');
				//更新对应的字段缓存
				$_mField->cacheAll($v['modelid']);
			}
		}
		return true;
	}

}

?>