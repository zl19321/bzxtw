<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Act.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-2
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 提供基于角色的权限检查服务，不提供用户管理和角色管理服务。
// +----------------------------------------------------------------------


/**
 * 为了便于开发，预定义了几个角色
 * 
 * RBAC_EVERYONE：表示任何用户（不管该用户是否具有角色信息）
 * RBAC_HAS_ROLE：表示具有任何角色的用户（该用户必须有角色信息）
 * RBAC_NO_ROLE：表示不具有任何角色的用户
 * RBAC_NULL：表示该设置没有值
 * 四个预定义角色并不是字符串，而是常量。
 * 因此必须以 'allow' => RBAC_EVERYONE 这样方式使用。
 * 并且不能和其他角色混用，例如 'allow' => RBAC_EVERYONE . ', POWER_USER' 就是错误的。
 *
 */

/**
 * 定义 RBAC 基本角色常量
 */
// RBAC_EVERYONE 表示任何用户（不管该用户是否具有角色信息）
define ( 'RBAC_EVERYONE', 'RBAC_EVERYONE' );

// RBAC_HAS_ROLE 表示具有任何角色的用户
define ( 'RBAC_HAS_ROLE', 'RBAC_HAS_ROLE' );

// RBAC_NO_ROLE 表示不具有任何角色的用户
define ( 'RBAC_NO_ROLE', 'RBAC_NO_ROLE' );

// RBAC_NULL 表示该设置没有值
define ( 'RBAC_NULL', 'RBAC_NULL' );

// ACTION_ALL 表示控制器中的所有动作
define ( 'ACTION_ALL', 'ACTION_ALL' );


class Act {
	/**
	 * 指示在 session 中用什么名字保存用户的信息
	 *
	 * @var string
	 */
	public $_sessionKey = '';
	
	/**
	 * 指示用户数据中，以什么键保存角色信息，SESSION中也会使用这个名称保存用户角色信息
	 *
	 * @var string
	 */
	public $_rolesKey = '';
	
	/**
	 * 构造函数
	 *
	 * @return FLEA_Rbac
	 */
	public function __construct() {
		$this->_sessionKey = 'userdata';
		$this->_rolesKey = 'roles';
		if ($this->_sessionKey == $this->_rolesKey) {
			throw_exception ( 'Act.class.php中_sessionKey和_sessionKey设置冲突' );
		}
	}
	
	/**
	 * 将用户数据保存到 session 中
	 *
	 * @param array $userData
	 * @param mixed $rolesData
	 */
	public function setUser($userData, $rolesData = null) {
		if ($rolesData) {
			$userData [$this->_rolesKey] = $rolesData;
		}
		//$_SESSION [$this->_sessionKey] = $userData;
		if($userData['isadmin'] == 1){
			$_SESSION [$this->_sessionKey] = $userData;
		}else{
			$_SESSION ["fuserdata"] = $userData;
		}
	}
	
	/**
	 * 获取保存在 session 中的用户数据
	 * 
	 * @return array
	 */
	public function getUser() {
		return isset ( $_SESSION [$this->_sessionKey] ) ? $_SESSION [$this->_sessionKey] : null;
	}
	
	/**
	 * 从 session 中清除用户数据
	 */
	public function clearUser() {
		unset ( $_SESSION [$this->_sessionKey] );
	}
	
	/**
	 * 获取 session 中用户信息包含的角色
	 *
	 * @return mixed
	 */
	public function getRoles() {
		$user = $this->getUser ();
		return isset ( $user [$this->_rolesKey] ) ? $user [$this->_rolesKey] : null;
	}
	
	/**
	 * 以数组形式返回用户的角色信息
	 *
	 * @return array
	 */
	public function getRolesArray() {
		$roles = $this->getRoles ();
		if (is_array ( $roles )) {
			return $roles;
		}
		$tmp = array_map ( 'trim', explode ( ',', $roles ) );
		return array_filter ( $tmp, 'trim' );
	}
	
	/**
	 * 检查访问控制表是否允许指定的角色访问
	 *
	 * @param array $roles
	 * @param array $ACT
	 *
	 * @return boolean
	 */
	public function check(& $roles, & $ACT) {
//		echo $ACT ['allow'];		
		if ($ACT ['allow'] == RBAC_EVERYONE) {
			// 如果 allow 允许所有角色，deny 没有设置，则检查通过
			if ($ACT ['deny'] == RBAC_NULL) {
				return true;
			}
			// 如果 deny 为 RBAC_NO_ROLE，则只要用户具有角色就检查通过
			if ($ACT ['deny'] == RBAC_NO_ROLE) {
				if (empty ( $roles )) {
					return false;
				}
				return true;
			}
			// 如果 deny 为 RBAC_HAS_ROLE，则只有用户没有角色信息时才检查通过
			if ($ACT ['deny'] == RBAC_HAS_ROLE) {
				if (empty ( $roles )) {
					return true;
				}
				return false;
			}
			// 如果 deny 也为 RBAC_EVERYONE，则表示 ACT 出现了冲突
			if ($ACT ['deny'] == RBAC_EVERYONE) {
				throw_exception ( '无效的访问控制表“Access-Control-Table (ACT)”数据' );
				return false;
			}
			
			// 只有 deny 中没有用户的角色信息，则检查通过
			foreach ( $roles as $role ) {
				if (in_array ( $role, $ACT ['deny'], true )) {
					return false;
				}
			}
			return true;
		}
		
		do {
			// 如果 allow 要求用户具有角色，但用户没有角色时直接不通过检查
			if ($ACT ['allow'] == RBAC_HAS_ROLE) {
				if (! empty ( $roles )) {
					break;
				}
				return false;
			}
			
			// 如果 allow 要求用户没有角色，但用户有角色时直接不通过检查
			if ($ACT ['allow'] == RBAC_NO_ROLE) {
				if (empty ( $roles )) {
					break;
				}
				return false;
			}
			
			if ($ACT ['allow'] != RBAC_NULL) {
				// 如果 allow 要求用户具有特定角色，则进行检查
				$passed = false;
				foreach ( $roles as $role ) {
					if (in_array ( $role, $ACT ['allow'], true )) {
						$passed = true;
						break;
					}
				}
				if (! $passed) {
					return false;
				}
			}
		} while ( false );
		
		// 如果 deny 没有设置，则检查通过
		if ($ACT ['deny'] == RBAC_NULL) {
			return true;
		}
		// 如果 deny 为 RBAC_NO_ROLE，则只要用户具有角色就检查通过
		if ($ACT ['deny'] == RBAC_NO_ROLE) {
			if (empty ( $roles )) {
				return false;
			}
			return true;
		}
		// 如果 deny 为 RBAC_HAS_ROLE，则只有用户没有角色信息时才检查通过
		if ($ACT ['deny'] == RBAC_HAS_ROLE) {
			if (empty ( $roles )) {
				return true;
			}
			return false;
		}
		// 如果 deny 为 RBAC_EVERYONE，则检查失败
		if ($ACT ['deny'] == RBAC_EVERYONE) {
			return false;
		}
		
		// 只有 deny 中没有用户的角色信息，则检查通过
		foreach ( $roles as $role ) {
			if (in_array ( $role, $ACT ['deny'], true )) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 对原始 ACT 进行分析和整理，返回整理结果
	 *
	 * @param array $ACT
	 *
	 * @return array
	 */
	public function prepareACT($ACT) {
		$ret = array ();
		$arr = array ('allow', 'deny');
		foreach ($arr as $v) {			
			$value = '';
			if (empty( $ACT [$v] )) {  //没有设置，默认都可以访问					
				$value = RBAC_NULL;
			} else if ( $ACT [$v] == RBAC_EVERYONE || $ACT [$v] == RBAC_HAS_ROLE 
					|| $ACT [$v] == RBAC_NO_ROLE || $ACT [$v] == RBAC_NULL ) {  //预定义角色
				$value = $ACT [$v];
			} else {
				$value = $ACT[$v];   //已经定义了访问权限
			}
//			$value = explode(',', strtoupper($ACT[$key]));				
//			$value = array_filter ( array_map ( 'trim', $value ), 'trim' );
			if (empty ( $value )) {
				$value = RBAC_NULL;
			}
			$ret [$v] = $value;
		}		
		return $ret;
	}

}