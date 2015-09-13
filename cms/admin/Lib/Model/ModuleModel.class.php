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
// | 文件描述: 模块管理Model
// +----------------------------------------------------------------------


class ModuleModel extends Model {
	
	/**
	 * 数据验证
	 * @param array $data
	 */
	public function validate($data, $field = '') {
	if (empty($field) && is_array($data)) { 	//检查所有字段的有效性			
			$t = true;
			foreach ($data as $k=>$v) {//进行递归验证
				if (false === $this->validate($data,$k)) $t = false;
			}
			return $t;
		} else if ($field == 'name') { //检查模块名称
			$data['name'] = trim($data['name']);
			return empty($data['name']) ? false : true;
		} else if ($field == 'controller') { //检查控制器名称
			if ($this->field("`controller`")->getByController($data['controller'])) {
				$this->error .= '此控制器已经被其他模块注册！<br />';
				return false; 
			} else {
				return true;
			}
		} else if ($field == 'tablename') { //检查主记录表
			if ($this->field("`tablename`")->getByTablename($data['tablename'])) {
				$this->error .= '已经有模块注册此主记录表！<br />';
				return false; 
			} else {
				return true;
			}
			return true;
		}
	}
	
	
	public function chageStatus($moduleid,$status = 1) {
		$data = $this->find($moduleid);
		if (is_array($data)) {
			$data['status'] = $status > 0 ? '1' : '0';
			if (false !== parent::save($data)) {
				$this->cache($moduleid,$data);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 注册模块
	 * @param $data 模块数据信息
	 */
	public function register($data) {
		$data['name'] = trim($data['name']);
		$data['controller'] = trim($data['controller']);
		$data['tablename'] = trim($data['tablename']);
		if (false == $this->validate($data)) {
			return false;
		}
		$moduleid = parent::add($data);
		if (fasle !== $moduleid) {
			$_model = D ('Model');
			if (!$data['extendable']) {  //可扩展模型需要手动建立
				//注册模型
				$model_data = array(
					'name' => $data['name'],  //模型名称
					'description' => $data['description'], //描述
					'moduleid' => $moduleid, //模型ID
					'extendable' => '0',  //是否可扩展
					'status' => $data['status'],  //状态
					'exttable' => $data['tablename'],  //非可扩展模型无扩展表，使用主记录表作为标识
					'tablename' => $data['tablename'],  //非可扩展模型无扩展表，使用主记录表作为标识
				);
				$modelid = $_model->add($model_data);
				if (false === $modelid) {
					$this->error .= '模块注册成功，对应模型创建失败！';
				}
			}
			//更新缓存
			$data['moduleid'] = $moduleid;
			$this->cache($moduleid,$data);
			$result = true;
		} else {
			$result = false;
		}
		return $result;
	}
	
	
	
	
	/**
	 * 按状态查询获取模块
	 * @param string $status
	 * @param string $extentable
	 */
	public function selectModule($condition = '') {			
		//查询结果
		$retuslt = $this->where ( $condition )->select ();		
		if (is_array ( $retuslt )) {
			$data = array();
			foreach ( $retuslt as $k => $v ) {
				$data [$v ['moduleid']] = $v ['name'];
				unset ( $retuslt [$k] );
			}
			return $data;
		} else {
			return false;
		}
	}
	
	public function delete($moduleid) {
		$moduleid = intval($moduleid);
		$return = false;
		if ($moduleid) {			
			$options = array(
				'where' => array(
					'moduleid' => $moduleid,
				),
			);
			$data = F ('module_'.$moduleid);
			if (!empty($data)) {
				if (!$data['issystem']) {  //判断是否系统模块
					if (false !== parent::delete($options)) {						
						//清除缓存
						F ('module_'.$moduleid,null);
						$return = true;
					}
					$this->error .= '模块卸载失败！<br />';
				}
				$this->error .= '系统模块，无法卸载！<br />';
			}
			$this->error .= '模块不存在！';
		}
		$this->error .= '无效参数！<br />';
		return $return;
	}
	
	
	/**
	 * 缓存模块信息
	 * @param int $moduleid
	 * @param array $data
	 */
	public function cache($moduleid,$data = null) {
		if (empty($data) || !is_array($data)) {
			$data = $this->find($moduleid);
		}		
		if (is_array($data)) {
			if ($data['moduleid'] == $moduleid) {
				F ( 'module_' . $moduleid, $data );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 缓存所有模块的信息
	 */
	public function cacheAll() {
		$data = $this->findAll();
		if (is_array($data)) {
			foreach ($data as $v) {
				F('module_'.$v['moduleid'],$v);
			}
			return true;
		}
		return false;
	}

}

?>