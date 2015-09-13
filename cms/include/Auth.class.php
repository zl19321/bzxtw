<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Auth.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-2
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述:  检查当前用户是否有权限访问指定的控制器和方法
// | *
// | * 验证步骤如下：
// | *
// | * 1、通过 authProiver 获取当前用户的角色信息；
// | * 2、调用 getControllerACT() 获取指定控制器的访问控制表；
// | * 3、根据 ACT 对用户角色进行检查，通过则返回 true，否则返回 false。
// +----------------------------------------------------------------------

class Auth {
	
	public $info = '';
	
	/**
	 * ACT 类对象
	 * @var unknown_type
	 */
	public $_act = '';
	
	function __construct() {
		if (!is_object($this->_act)) {
			import('Act',INCLUDE_PATH);
			$this->_act = get_instance_of('Act');
		}
	}
	
	/**
	 * 检查当前用户是否有权限访问指定的控制器和方法
	 * 
	 * 验证步骤如下：
	 *
	 * 1、通过 authProiver 获取当前用户的角色信息；
	 * 2、调用 getControllerACT() 获取指定控制器的访问控制表；
	 * 3、根据 ACT 对用户角色进行检查，通过则返回 true，否则返回 false。
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param string $controllerClass
	 *
	 * @return boolean
	 */
	public function checkRbac($controllerName = '', $actionName = '') {	    
		//载入Act权限分析验证类
		import ( 'Act', INCLUDE_PATH );
		if (empty( $controllerName )) {
			$controllerName = ucfirst(strtolower(MODULE_NAME));
		} else {
			$controllerName = ucfirst(strtolower($controllerName));
		}
		if (empty( $actionName )) {
			$actionName = strtolower(ACTION_NAME);
		} else {
			$actionName = strtolower($actionName);
		}		
		// 如果控制器没有提供 ACT，或者提供了一个空的 ACT，则返回null
		$rawACT = $this->getControllerACT ( $controllerName );
		if (is_null ( $rawACT ) || empty ( $rawACT )) {
			return defined('IN_ADMIN') ? false : true;
		}
		$this->info['act'] = $rawACT;
		$_act = $this->_act;
		$ACT = $_act->prepareACT ( $rawACT );
		$this->info['act'] = $ACT;
		$ACT ['actions'] = array ();
		if (isset ( $rawACT ['actions'] ) && is_array ( $rawACT ['actions'] )) {
			foreach ( $rawACT ['actions'] as $rawActionName => $rawActionACT ) {
				if ($rawActionName !== ACTION_ALL) {
					$rawActionName = strtolower ( $rawActionName );
				}
				$ACT ['actions'] [$rawActionName] = $_act->prepareACT ( $rawActionACT );
			}
		}
		// 取出用户角色信息
		$roles = $_act->getRolesArray ();
		$this->info['roles'] = $roles;
		// 首先检查用户是否可以访问该控制器、系统后台要进行控制器级别的权限验证
		if (defined('IN_ADMIN')) {
			if (! $_act->check ( $roles, $ACT )) {
				return false;
			}
		}
		// 接下来验证用户是否可以访问指定的控制器方法（这个就需要进行验证了、并且前后台默认访问权限不一样）	
		if (isset ( $ACT ['actions'] [$actionName] )) {
			return $_act->check ( $roles, $ACT ['actions'] [$actionName] );
		}		
		// 如果当前要访问的控制器方法没有在 act 中指定，则检查 act 中是否提供了 ACTION_ALL
		if (! isset ( $ACT ['actions'] [ACTION_ALL] )) {
			return true;
		}
		return $_act->check ( $roles, $ACT ['actions'] [ACTION_ALL] );
	}
	
	/**
	 * 获取指定控制器的访问控制表（ACT）
	 *
	 * @param string $controllerName
	 * @param string $controllerClass
	 *
	 * @return array
	 */
	protected function getControllerACT($controllerName) {		
		$actType = strtolower ( C ( 'AUTH_ACT_TYPE' ) ? C ( 'AUTH_ACT_TYPE' ) : 'File' );
		if ($actType == 'file') { //文件方式载入ACT
			return $this->_loadActFile ($controllerName);
		} else if ($actType == 'db') { //直接用数据库方式载入ACT信息
			return $this->_loadActDb ($controllerName);
		}
	}
	
	/**
	 * 载入 对应的从控制器 ACT 数组 文件
	 *
	 * @param string $actFilename
	 *
	 * @return mixed
	 */
	protected function _loadActFile($controllerName) {		
		static $actArr = array ();
		static $loadCount = 0;			
		if (isset ( $actArr [$controllerName] )) {
			return $actArr [$controllerName];
		}
		$actFilename = DATA_CACHE_PATH . APP_NAME . '_act_cache.php';
		if (! file_exists($actFilename)) {
			$_act = D ('Acts','admin');
			$_act->cacheAct();
			if ($loadCount<3) {
		        $loadCount++;
		        return $this->_loadActFile($controllerName);
		    }
			throw_exception ( "权限列表更新发生异常，请刷新当前页面！" );
		}
		$acts = F (APP_NAME . '_act_cache');
		if (is_array ( $acts )) {
			if (isset($acts[$controllerName])) {
				$actArr[$controllerName] = $acts[$controllerName];
				return $acts[$controllerName];
			}
			else return null;
		}
	}
	
	/**
	 * 查询数据库，返回 ACT 信息
	 *
	 * @param string $actFilename
	 *
	 * @return mixed
	 */
	protected function _loadActDb($controllerName) {
		throw_exception ( 'dbact failed' );
	}
}