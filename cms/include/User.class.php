<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: User.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-18
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 用户管理工厂类
// +----------------------------------------------------------------------
import('admin.Model.UserModel');
class User extends UserModel {

	/**
	 * 验证登录
	 */
	public function checkLogin($data,$isAdmin = false) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::checkLogin($data,$isAdmin);
		}
	}
	
	/**
	 * 用户注册
	 */
	public function register($data, $role_id = '', $isAdmin = false) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::register($data, $role_id, $isAdmin);
		}
	}
	
	/**
	 * 新增用户
	 */
	public function insert($data,$isAdmin = false) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {			
			return parent::insert($data,$isAdmin);
		}
	}
	
		
	/**
	 * 更新用户信息
	 */
	public function update($data) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::update($data);
		}
	}
	
	/**
	 * 删除用户信息
	 */
	public function delete($user_id) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::delete($user_id);
		}
	}
	
	/**
	 * 验证激活用户
	 */
	public function active($userid,$key) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::active($userid,$key);
		}
	}
	
	/**
	 * 获取用户信息
	 * @param int $user_id
	 * @param boolean $extend 是否取得扩展表的信息
	 */
	public function getUserData($user_id, $extend = false) {
		if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::getUserData($user_id, $extend);
		}
	}
	
	/**
	 * 更新用户名修改密码 
	 * @see UserModel::updatePassword()
	 */
	public function updatePassword($username, $newPassword) {
	if (true === C('USER_PASSPORT_ON')) { //处理uc同步
			
		} else {
			return parent::updatePassword($username, $newPassword);
		}
	}
	
}

?>