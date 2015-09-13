<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-7
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: acts 数据表模型
// +----------------------------------------------------------------------

class ActsModel extends Model {

	/**
	 * 添加入库之前自动调用
	 * @param unknown_type $data
	 * @param unknown_type $options
	 */
	protected function _before_insert(&$data,$options) {
		parent::_before_insert($data,$options);
		if (is_array($data['allow'])){
			$data['allow'] = implode(',',$data['allow']);
		}
		if (is_array($data['deny'])){
			$data['deny'] = implode(',',$data['deny']);
		}

	}
	/**
	 * 更新入库之前自动调用
	 * @param unknown_type $data
	 * @param unknown_type $options
	 */
	protected function _before_update(&$data,$options) {
		$this->_before_insert($data,$options);
	}
	/**
	 * find之后自动调用
	 * @param unknown_type $data
	 * @param unknown_type $options
	 */
	protected function _after_find(&$result,$options) {
		parent::_after_find($result,$options);
		if (empty($result['allow'])) $result['allow'] = array();
		else $result['allow'] = explode(',',$result['allow']);

		if (empty($result['deny'])) $result['deny'] = array();
		else $result['allow'] = explode(',',$result['allow']);
	}
	/**
	 * findAll or select 之后自动调用
	 * @param unknown_type $resultSet
	 * @param unknown_type $options
	 */
	protected function _after_select(&$resultSet,$options) {
		parent::_after_select($resultSet,$options);
		if (is_array ( $resultSet )) {
			foreach ( $resultSet as $k => $v ) {
				if (isset($resultSet [$k] ['allow'])) {
					if (empty($resultSet [$k] ['allow'])) $resultSet [$k] ['allow'] = array();
					else $resultSet [$k] ['allow'] = explode(',',$resultSet [$k] ['allow']);
				}
				if (isset($resultSet [$k] ['deny'])) {
					if (empty($resultSet [$k] ['deny'])) $resultSet [$k] ['deny'] = array();
					else $resultSet [$k] ['deny'] = explode(',',$resultSet [$k] ['deny']);
				}
			}
		}
	}

	/**
	 * ajax方式保存授权，单个
	 */
	public function ajaxSave($data) {
		if (empty($data['controller']) || empty($data['action'])
			|| empty($data['do']) || !$data['role_id'] || empty($data['appname'])) return null;
		//获取角色信息
		$role_data = D('Role')->find((int)$data['role_id']);
		if(is_array($role_data)) {
			$where = array(
				"appname" => $data['appname'],
				"controller" => $data['controller'],
				"action" => $data['action'],
			);
			$act_data = $this->where($where)->find();
			if (is_array($act_data)) {
				if ($data['do'] == 'add') {
					if (!in_array($role_data['name'],$act_data['allow'])) {
						array_push($act_data['allow'],$role_data['name']); //添加角色访问权限
					}
				} else if ($data['do'] == 'remove') {
					if (in_array($role_data['name'],$act_data['allow'])) {
						$key = array_search($role_data['name'],$act_data['allow']);
						unset($act_data['allow'][$key]);
					}
				}
				//print_r($act_data);
				return $this->save($act_data);
			}			
		}
		return false;
	}
	/**
	 * 更改菜单操作的访问权限
	 * 来源用户权限修改页面
	 */
	public function ajaxCategory($data) {
		if (empty($data['cat_id']) || empty($data['action']) || empty($data['do']) || !$data['role_id'] || empty($data['field'])) return null;

		if ($data['member'] == "administrator") { return false; }

		//分析URL
		preg_match_all('/\[\w+\]/',$data['field'],$match);
		//dosubmit=2&role_id=1&cat_id=rule2&field=info[permissions][admin][add][]&action=member&do=add
		$permissions = substr($match[0][0], 1, strlen($match[0][0])-2);//字段
		$rule        = substr($match[0][1], 1, strlen($match[0][1])-2);//admin
		$action      = substr($match[0][2], 1, strlen($match[0][2])-2);//操作add
		
		//获取栏目信息
		$catid = substr($data['cat_id'],4);
		$_cate = D('Category');
		$category = $_cate->find((int)$catid);
		if(is_array($category[$permissions])){
			$permi = $category[$permissions];
		}else {
			$permi = eval("return {$category[$permissions]};");
		}
		$rule_array = $permi[$rule];
		if(is_array($rule_array)) {
			if (in_array($data['action'], $rule_array[$action]) && $data['do'] == "remove") {
				$key = array_search($data['action'],$rule_array[$action]);
				unset($rule_array[$action][$key]);
				foreach ($rule_array[$action] as $t){
					$new[] = $t;
				}
				$rule_array[$action] = $new;
			}elseif (!in_array($data['action'], $rule_array[$action]) && $data['do'] == "add") {
				array_push($rule_array[$action],$data['action']); 
			}else {
				return true;
			}
			$category[$permissions][$rule] = $rule_array;
			$result = var_export ( $category[$permissions], true );
			return $_cate->query('update '.C("DB_PREFIX").'category set '.$permissions.'="'.$result.'" where catid='.$catid);

		}
		return false;
	} 

	/**
	 * 检查控制器和其动作是否已经被注册
	 * @param array $data
	 */
	public function isActsExists($data) {
		if (!is_array($data) || empty($data['appname']) || empty($data['controller']) || empty($data['action'])) return false;
		$where = array(
			"appname" => $data['appname'],
			"controller" => $data['controller'],
			"action" => $data['action']
		);
		$act_data = $this->where($where)->find();
		if (is_array($act_data)) return true;
		else return false;
	}


	/**
	 * 缓存ACT文件
	 */
	public function cacheAct() {
		$group = $this->field(array("DISTINCT appname AS `appname`"))->findAll();
		foreach ($group as $g) {
			$where = array("appname"=>$g['appname']);
			$controller = $this->where($where)->group("controller")->findAll();
			if (is_array($controller)) {
				$data = array();
				foreach ($controller as $v) {	//控制器的所有已经注册动作
					$w = array(
						'controller' => $v['controller'],
						'appname' => $g['appname'],
					);
					$actions = $this->where($w)->findAll();
					$module = ucfirst(strtolower($v['controller']));
					$data[$module]['allow'] = 'RBAC_HAS_ROLE';
					$data[$module]['actions'] = array();					
					if (is_array($actions)) {
						foreach ($actions as $a) {
							$data[$module]['actions'][$a['action']]['allow'] = $a['allow'];
							$data[$module]['actions'][$a['action']]['deny'] = $a['deny'];
						}
					}
				}
			}			
			F ( $g['appname'] . '_act_cache', $data );
		}
		return true;
	}
}

?>