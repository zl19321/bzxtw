<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ModelFieldModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-13
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 模型字段Model
// +----------------------------------------------------------------------


class ModelFieldModel extends Model {
	
	protected $tableName = 'model_field';
	
	/**
	 * 系统基础表字段，当数据库中的表结构发生变化的时候，请修改此处
	 * 
	 * @var unknown_type
	 */
	public static $baseFields = array('cid','catid','title','thumb','attr','description','seokeywords','seodescription','seotitle',
    		                   'url','sort','status','user_id','username','template','create_time','update_time');
	
	/**
	 * 
	 * @param string $type
	 */
	public function getSettingByType($class, $data = '') {
		import ( 'Field', INCLUDE_PATH );
		return Field::setting ( $class, $data );
	}
	
	/**
	 * 验证数据有效性
	 * @param array $data
	 */
	public function validate($data = array()) {
		$result = false;
		if ($data ['modelid']) {
			if (preg_match ( "/^[a-z][0-9a-z_]*[0-9a-z]?$/i", $data ['field'] )) {
				$_model = D ( 'Model' );
				$model_data = $_model->field ( 'fieldid' )->find ( ( int ) $data ['modelid'] );
				if (! is_array ( $model_data )) { //字段合法
					if (! empty ( $data ['name'] )) { //别名填写			
						$result = true;
					} else {
						$this->error .= '字段别名必须填写！<br />';
					}					
				} else {
					$this->error .= '字段名已经存在！<br />';
				}				
			} else {
				$this->error .= '字段名填写不规范！<br />';
			}			
		} else {
			$this->error .= '没有指定字段所属模型！<br />';
		}		
		return $result;
	}
	
	/**
	 * 查询的后置方法，会在find()之后自动以引用方式调用
	 */
	protected function _after_find(&$result) {
		if (isset($result['setting'])) {
			$result ['setting'] = eval ( "return {$result ['setting']};" );			
		}
	}
	
	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 * @param unknown_type $resultSet
	 */
	protected function _after_select(&$resultSet) {
		if (is_array ( $resultSet )) {
			foreach ( $resultSet as $k => $v ) {
				if (isset($resultSet [$k] ['setting'])) {
					$resultSet [$k] ['setting'] = eval ( "return {$resultSet [$k] ['setting']};" );						
				}				
			}
		}
	}
	
	/**
	 * add方法insert之前处理方法，会在insert之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_insert(&$data) {		
		//过滤与默认值设置
		$data ['field'] = htmlspecialchars ( $data ['field'] );
		$data ['name'] = htmlspecialchars ( $data ['name'] );
		$data ['pattern'] = htmlspecialchars ( $data ['pattern'] );
		$data ['formattribute'] = htmlspecialchars ( $data ['formattribute'] );
		$data ['card'] = empty ( $data ['card'] ) ? '1' : ( int ) $data ['card'];
		$data ['required'] = empty ( $data ['required'] ) ? '0' : '1';
		import ( 'Field', INCLUDE_PATH );
		@$data ['setting'] = var_export ( $data ['setting'], true );
	}
	
	/**
	 * save方法update数据之前的处理方法，会在update数据之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_update(&$data) {
		$this->_before_insert ( $data );
	}
	
	/**
	 * 添加字段
	 */
	public function add($data = array()) {
		if (! $this->validate ( $data )) {
			return false;
		}
		//字段数据处理
		$data ['modelid'] = intval ( $data ['modelid'] );
		$data ['minlength'] = intval ( $data ['minlength'] );
		$data ['maxlength'] = intval ( $data ['maxlength'] );
		$data ['required'] = $data ['required'] == '1' ? '1' : '0';
		$data ['systype'] = intval ( $data ['systype'] );
		$data ['sort'] = $data ['sort'] > 0 ? intval ( $data ['sort'] ) : 1;
		$data ['status'] = isset ( $data ['status'] ) ? ($data ['status'] == '1' ? '1' : '0') : '1';
		$data ['card'] = $data ['card'] > 0 ? intval ( $data ['card'] ) : 1;
		//开始事务
//		$this->startTrans ();
		$fieldid = parent::add ( $data );
		if (false !== $fieldid) { //保存成功
			import ( 'Field', INCLUDE_PATH );
			$_model = D ( 'Model' );
			//获取要操作字段的数据表表名
			$model_data = $_model->field ( '`tablename`' )->find ( $data ['modelid'] );
			F ($model_data ['tablename'] , null, ALL_CACHE_PATH.'cache/_fields/');
			//添加物理数据表的字段
			if (false !== Field::addField ( $data ['formtype'], $this, $model_data ['tablename'], $data )) {
//				$this->commit ();
				$this->cache($fieldid);
				return true;
			}
		}
		$this->error .= '字段添加事务操作执行失败！<br />';
		
		$this->rollback ();
		return false;
	}
	
	/**
	 * 更新
	 */
	public function save($data = array()) {
		if (! $this->validate ( $data )) {
			return false;
		}		
		//开始事务
		//		$this->startTrans ();
		if (false !== parent::save ( $data )) { //保存成功
			//暂时不允许修改数据信息字段
			//			import('Field',INCLUDE_PATH);
			//			$_model = D('Model');
			//获取要操作字段的数据表表名
			//			$model_data = $_model->field('`tablename`')->find($data['modelid']);
			//修改物理数据表的字段
			//			if (false !== Field::changeField($data['formtype'],$this,$model_data['tablename'],$data)) {
			//				$this->commit();
			//				return true;
			//			}
			//			$this->commit ();
			
			$_model = D ( 'Model' );
			//获取要操作字段的数据表表名
			$model_data = $_model->field ( '`tablename`' )->find ( $data ['modelid'] );
			F ($model_data['tablename'] , null, ALL_CACHE_PATH.'cache/_fields/');
			
			$this->cache($data['fieldid']);
			return true;
		}
		//		$this->error .= '字段添加事务操作执行失败！<br />';
		//		$this->rollback ();
		return false;
	}
	
	
	
	/**
	 * 完全删除删除字段
	 * @param unknown_type $fieldid
	 */
	public function delete($fieldid) {
		$fieldid = intval ( $fieldid );
		if (! $fieldid) {
			return false;
		}			
		$option = array (
			'where' => array (
				'fieldid' => $fieldid 
			) 
		);
		$field_data = $this->find ( $option );
		if (! is_array ( $field_data )) {
			$this->error .= '字段不存在！';
			return false;
		}
		//系统字段不能操作
		if ($field_data ['systype'] > 0) {
			$this->error .= '系统字段，不能删除！<br />';
			return false;
		}
		$_model = D ( 'Model' );
		$model_data = $_model->find ( $field_data ['modelid'] );
		//开始事务
		$this->startTrans ();
		if (false !== parent::delete ( $option )) { //删除字段记录信息			
			$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $model_data ['tablename'] . "` 
					DROP `" . $field_data ['field'] . "`";
			if (false !== $this->execute ( $sql )) { //删除物理字段
				$this->commit ();
				//删除字段缓存，更新模型缓存
				F ( 'modelField_' . $field_data ['fieldid'], null );
				$_model->cache ( $model_data ['modelid'] );
				return true;
			} else {
				$this->rollback ();
				$this->error .= '物理字段删除失败！';
				return false;
			}
		} else {
			$this->rollback ();
			return false;
		}
	}
	
	/**
	 * 缓存数据
	 * @param unknown_type $fieldId
	 * @param unknown_type $data
	 */
	public function cache($fieldId, $data = '') {
		if (empty ( $data ) || !is_array ( $data )) {
			$data = $this->find ( $fieldId );
		}
		if (is_array ( $data )) {
			if ($data ['fieldid'] == $fieldId) {
				F ( 'modelField_' . $fieldId, $data );
				return true;
			}
		}
		return false;
	}
	
	public function cacheAll($modelid) {
		$data = $this->where("`modelid`='{$modelid}'")->findAll ();
		if (is_array ( $data )) {
			foreach ( $data as $v ) {
				F ( 'modelField_' . $v ['fieldid'], $v );
			}
		}
		return true;
	}
    
        /**
     * 
     * @name返回模型现有字段 
     * @articler fangfa
     * @datetime 2013-01-28
     * @update 2013-02-27
     * 
     */ 
    
    public function findlist($modelid,$where = '',$listshow = 0,$field = 'name,field'){
        $where = $where != ''?' AND '.$where : '';
        //增加一个显示判断 用于副表
        if($listshow === 1){
            $listshow_where = ' AND `listshow` = 1 ';
        }
        $data = $this->field($field)->where("`modelid`='{$modelid}' ".$listshow_where.$where)->findAll ();
        
        return $data;
        
    }        

}

?>