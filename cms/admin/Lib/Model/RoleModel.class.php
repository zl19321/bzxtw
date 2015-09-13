<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: RoleModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-6
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 数据库维护Model
// +----------------------------------------------------------------------

class RoleModel extends RelationModel {
	
	/**
	 * 关联定义
	 * @var unknown_type
	 */
	public $_link = array (
		'model' => array (
		'mapping_type' => BELONGS_TO, 
		'class_name' => 'Model', 
		'foreign_key' => 'modelid', 
		'mapping_fields' => 'modelid,name,exttable,tablename' 
	) 
	);
	
	/**
	 * 验证 name（角色标识，如admin,manager） 是否可用
	 */
	public function isNameExists($name = '') {
		$name = trim($name);
		if (!empty($name)) {
			$data = $this->getByName($name);
			if (is_array($data)) return true;
		}
		return false;
	}	
	
	/**
	 * 缓存角色信息
	 * @param int $moduleid
	 * @param array $data
	 */
	public function cache($role_id,$data = null) {
		if (empty($data) || !is_array($data)) {
			$data = $this->find($role_id);
		}		
		if (is_array($data)) {
			if ($data['role_id'] == $role_id) {
				F ( 'role_' . $role_id, $data );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 缓存所有角色信息
	 */
	public function cacheAll() {
		$data = $this->findAll();
		if (is_array($data)) {
			foreach ($data as $v) {
				F('role_'.$v['role_id'],$v);
			}
			return true;
		}
		return false;
	}
}

?>