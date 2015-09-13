<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: UserModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-13
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 用户Model
// +----------------------------------------------------------------------
/**
 * 密码的加密方式
 */
define ( 'PWD_MD5', 1 );  //md5
define ( 'PWD_CRYPT', 2 ); //crypt
define ( 'PWD_CLEARTEXT', 3 ); //明文
define ( 'PWD_SHA1', 4 );  //sha1
define ( 'PWD_SHA2', 5 ); //hash('sha512')
//

class UserModel extends RelationModel {
	
	/**
	 * 关联定义
	 * @var unknown_type
	 */
	public $_link = array(
		'UserPerson'	=>	array(
			'mapping_type'		=>	HAS_ONE,
			'Class_name'		=>	'UserPerson',
			'foreign_key'		=>	'user_id',
			//'mapping_fields'	=>	'username,password,email,nickname,verify,remark,status,isadmin',
		)
	);
	
	/**
	 * 密码加密方式，默认为md5
	 * @var unknown_type
	 */
	protected $encodeMethod = PWD_MD5;
	
	/**
	 * 数据表名
	 * @var unknown_type
	 */
	protected $tableName = 'user';
	
	/**
	 * 手动指定数据表模型名称
	 * @var string
	 */
	protected $name = 'User';
	
	/**
	 * 登录验证主字段
	 * 如果是数组，则数组中任意值都可以用来进行登录判断（前提是数据库中字段值是唯一的）
	 * 常见的 用户名/邮箱  都可以验证登录   这里默认也是，用户名，邮箱都可以登录
	 * @var array
	 */
	public static $checkBy = array('username','email');
	
	/**
	 * 用户信息
	 * @var unknown_type
	 */
	public $userData = array();
	
	/**
	 * 设置登录名验证字段
	 * 
	 * 
	 */
	public function setCheckBy($name) {
		if (is_string($name)) {
			$this->checkBy = array($name);
		} elseif (is_array($name)) {
			$this->checkBy = $name;
		}
	}
	
	/**
	 * 添加用户的时候进行数据验证
	 * @param array $data
	 * @param string $field
	 */
	public function validate($data,$field = '') {
		if (empty($field) && is_array($data)) { 	//检查所有字段的有效性			
			$t = true;
			foreach ($data as $k=>$v) {//进行递归验证
				$v!='' && $data[$k] = trim($v);
				if (true !== $this->validate($data,$k)) $t = false;
			}
			return $t;
		} else if ($field == 'username') { //检查用户名是否可用
			if (strlen(trim($data['username']))<4 || strlen(trim($data['username']))>32
				|| !parent::regex($data['username'],'englist_num')) {
				$this->error .= '用户名不规范！<br />';
				return false;
			}
			if (is_array($this->getByUsername($data['username']))) {
				$this->error .= '该用户名已经存在！<br />';
				return false;
			}
			return true;
		} else if ($field == 'password') { //检查密码有效性
			if (isset($data['repassword']) && $data['password'] !== $data['repassword']) {
				$this->error .= '两次填写的密码不一致！<br />';
				return false;
			}
			if (strlen(trim($data['password']))<4 || strlen(trim($data['password']))>32) {
				$this->error .= '密码填写不符合要求！<br />';
				return false;
			}
			return true;
		}else if ($field == 'email') { //检查电子邮件是否可用	
			if (!parent::regex($data['email'],'email')) {
				$this->error .= '电子邮件不合法！<br />';
				return false;
			}	
			if (is_array($this->getByEmail($data['email']))) {
				$this->error .= '该电子邮件已经存在！<br />';
				return false;
			}
			return true;
		} else if ($field == 'role_id') { //检查角色是否存在
			$data['role_id'] = intval($data['role_id']);
			if (null === D('Role')->field("`role_id`")->find($data['role_id'])) {
				$this->error .= '用户角色不存在！<br />';
				return false;
			}
			return true;
		} else {
			return true;
		}
	}
	
	/**
	 * 验证用户登录信息
	 * @param array $data	//验证信息
	 * @param boolean $isadmin	是否管理员登录
	 */
	public function checkLogin($data = array(), $isadmin = false) {
		if (empty($data['username'])) {
			$this->error .= '请填写用户名！';
			return false;
		}
		if (empty($data['password'])) {
			$this->error .= '请填写密码！';
			return false;
		}
		if (!$this->existsUsername($data['username'])) {
			$this->error .= '用户名不存在！';
			return false;
		}
		$userData = $this->userData;
		if($userData['isadmin'] != $isadmin){
			$this->error .= '登陆错误！';
			return false;
		}
		if (!is_array($userData) || empty($userData)) {
			$userData = $this->getByUsername($data['username']);
		}
		if ($userData['status']<=0) {
			$this->error .= '该用户已经被锁定！';
			return false;
		}
		if (!$this->checkPassword($data['password'], $userData['password'])) {
			$this->error .= '密码错误！';
			return false;
		}
		//登录成功，更新登录信息
		$userData['last_login_ip'] = get_client_ip();
		$userData['last_login_time'] = time();
		$userData['login_count']++;
		if (false === parent::save($userData)) {
			return false;
		}
//		将用户ID、用户名和角色信息保存到SESSION
		import('Act',INCLUDE_PATH);
		$_act = get_instance_of('Act');
		$userinfo = array(
			'user_id'  => &$userData['user_id'],
			'username' => &$userData['username'],
			'isadmin'  => &$userData['isadmin']
		);
		$userRoles = $this->fetchRoles($userinfo['user_id']);
		$_act->setUser($userinfo,$userRoles);
		return true;
	}
		
	/**
	 * 验证用户找回密码信息
	 *
	 * @param array $data  //验证信息
	 * @return boolean
	 */
	public function checkGetPwd($data = array()) {
		if (empty($data['username'])) {
			$this->error .= '请填写用户名！';
			return false;
		}
		if (!$this->existsUsername($data['username'])) {
			$this->error .= '用户名不存在！';
			return false;
		}
		$userData = $this->userData;
		if (!is_array($userData) || empty($userData)) {
			$userData = $this->getByUsername($data['username']);
		}
		if ($userData['status']<=0) {
			$this->error .= '该用户已经被锁定！';
			return false;
		}
		return true;
	}
	
	/**
	 * 注册新用户
	 * 
	 * @param array $data  用户基本信息
	 * @param int $role_id 角色ID
	 */
	public function register($data = array(), $role_id = '', $isAdmin = 0) {
		if (!$role_id) {
			$this->error .= L('系统未设定角色信息！');
			return false;
		}
		if (!$this->validate($data)) {
			return false;
		}
		if ($isAdmin) {  //是否为管理员
			$data['isadmin'] = 1;
		} else {
			$data['isadmin'] = 0;
		}
		$data['password'] = $this->encodePassword($data['password']);
		//生成32位激活码
		$data['active'] = $this->createActiveCode($data);
		$data['create_time'] = time();
		$data['update_time'] = time();
		$user_id = parent::add($data);
		if (false !== $user_id) {
			//添加角色关联信息
			$role_user_data = array(
				'role_id' => $role_id,
				'user_id' => $user_id,
			);
			D('RoleUser')->add($role_user_data);
			//初始化扩展信息表数据
			$data['user_id'] = $user_id;
			$role_data = D('Role')->find($role_id);
			$modelData = F ('model_'.$role_data['modelid']);
			$_extContentModel = D (parse_name( $modelData['tablename'], 1 ));
			$_extContentModel->add($data);
			return true;
		} else {
			$this->error .= L ('用户信息无法保存！');
		}
		return false;
	}
	
	/**
	 * 添加用户
	 */
	public function insert($data = array(),$isAdmin = false) {
		if (!$this->validate($data)) {
			return false;
		}
		if ($isAdmin) {  //是否为管理员
			$data['isadmin'] = 1;
		}
		$data['password'] = $this->encodePassword($data['password']);
		$data['create_time'] = time();
		$data['update_time'] = time();
		$user_id = parent::add($data);
		if (false !== $user_id) {
			//添加角色关联信息
			$role_user_data = array(
				'role_id' => $data['role_id'],
				'user_id' => $user_id,
			);
			D('RoleUser')->add($role_user_data);
			//初始化扩展信息表数据
			$data['user_id'] = $user_id;
			$role_data = D('Role')->find($data['role_id']);
			$modelData = F ('model_'.$role_data['modelid']);
			$_extContentModel = D (parse_name( $modelData['tablename'], 1 ));
			$_extContentModel->add($data);
			return true;
		}
		return false;
	}
	
	/**
	 * 生成32位激活码
	 * 
	 */
	public function createActiveCode($data) {
		$data = serialize($data) . uniqid();
		return md5($data);
	}
	
	/**
	 * 更新用户信息
	 */
	public function update($data = array(), $isAdmin) {
		if ($isAdmin) {  //是否为管理员
			$data['isadmin'] = 1;
		}
		if (empty($data['password'])) {
			unset($data['password']);
		} else {
			if (!$this->validate($data,'password')) {
				return false;
			}
			$data['password'] = $this->encodePassword($data['password']);
		}
		//验证电子邮件是否更改以及是否能用(邮件地址可以更改，但是不能与别人的重复)
		$email = $this->getByEmail($data['email']);
		if ($email) {
			if ($email['user_id'] != $data['user_id']) {
				$this->error .= L('邮件地址被占用！');
				return false;
			}
		}
		$data['update_time'] = time();
		if (false !== parent::save($data)) {
			if ($data['role_id']) {  //  更新角色关联信息				
				$role_user_data = array(
					'role_id' => $data['role_id'],
					'user_id' => $data['user_id'],
				);
				D('RoleUser')->where("`user_id`='{$data['user_id']}'")->save($role_user_data);
			}
			//更新扩展表信息
			$role_data = D('Role')->find($data['role_id']);
			$modelData = F ('model_'.$role_data['modelid']);
			$_mUserextend = D (parse_name( $modelData['tablename'], 1 ));
			$tmp = $_mUserextend->field('user_id')->where("`user_id`='{$data['user_id']}'")->find();
			if ($tmp) {  //保存
				$_mUserextend->where("`user_id`='{$data['user_id']}'")->save($data);
			} else {  //新增
				$_mUserextend->where("`user_id`='{$data['user_id']}'")->add($data);
			}
			return  true;
		}
		return false;
	}
	
	/**
	 * 更改用户状态，锁定/解锁
	 * @param int $user_id
	 * @param int $status
	 */
	public function status($user_id,$status = 1) {
		$user_id = intval($user_id);
		$status = intval($status);
		if (!in_array($status,array(0,1))) return false;
		$data  = array(
			'user_id' => $user_id,
			'status' => $status,
		);
		return parent::save($data);
	}
	
	/**
	 * 删除用户
	 * 
	 * @param mixed $user_id 用户ID，用户ID数组，用户IDS
	 */
	public function delete($user_id) {
		if (!$user_id) return false;
		if (is_numeric($user_id)) { //要删除的用户ID
			$where= array(
				'user_id' => $user_id,
			);
		} else if (is_array($user_id)) {  //要删除的用户ID数组
			$ids = implode(',',$user_id);
			$where= " `user_id` IN ({$ids})";
		} else if (is_string($user_id)) { //要删除的用户ids
			$where= " `user_id` IN ({$user_id})";
		}
		$option['where'] = $where;
		//删除用户信息
		if (false !== parent::delete($option)) {
			//删除用户的角色关联信息
			D('RoleUser')->delete($option);
			return true;
		}
		return false;
	}
	
	/**
	 * 激活用户
	 * 
	 * @param int $user_id 用户ID
	 * @param string $key 激活码
	 */
	public function active($user_id,$key) {
		
	}
	
	/**
	 * 检查指定的用户名是否已经存在
	 *
	 * @param string $username
	 *
	 * @return boolean
	 */
	public function existsUsername($username) {
		$result = $this->getByUsername ($username);		
		if (is_array($result) && !empty($result)) {
			$this->userData = $result;
			return true;
		}
		return false;
	}
	
	/**
	 * 更新指定用户的密码
	 *
	 * @param string $username 用户名
	 * @param string $oldPassword 现在使用的密码
	 * @param string $newPassword 新密码
	 *
	 * @return boolean
	 *
	 * @access public
	 */
	public function changePassword($username, $oldPassword, $newPassword) {
		$userData = $this->getByUsername ( $username);
		if ( empty($userData) ) {
			return false;
		}
		if (! $this->checkPassword ( $oldPassword, $userData ['password'] )) {
			return false;
		}
		
		$userData ['password'] = $this->encodePassword($newPassword);
		return parent::save ( $userData );
	}
	
	/**
	 * 根据用户名直接更新密码
	 *
	 * @param string $username
	 * @param string $newPassword
	 *
	 * @return boolean
	 */
	public function updatePassword($username, $newPassword) {
		$userData = $this->getByUsername ( $username );
		if ( empty($userData) ) {
			return false;
		}
		
		$userData ['password'] = $this->encodePassword($newPassword);
		return parent::save ( $userData );
	}
	
	
	/**
	 * 依据用户ID直接更新密码
	 *
	 * @param mixed $userId
	 * @param string $newPassword
	 *
	 * @return boolean
	 */
	public function updatePasswordById($user_id, $newPassword) {
		$userData = $this->getByUser_id ( $user_id );
		if ( empty($userData) ) {
			return false;
		}
		$userData ['password'] = $this->encodePassword($newPassword);
		return parent::save ( $userData );
	}
	
	
	/**
	 * 依据用户名直接更新验证串
	 *
	 * @param string $username
	 * @param string $newPassword
	 *
	 * @return boolean
	 */
	public function updateVerify($username, $code) {
		$userData = $this->getByUsername ( $username );
		if ( empty($userData) ) {
			return false;
		}
		$userData ['verify'] = $code;
		return parent::save ( $userData );
	}
	
	/**
	 * 检查密码的明文和密文是否符合
	 *
	 * @param string $cleartext 密码的明文
	 * @param string $cryptograph 密文
	 *
	 * @return boolean
	 *
	 * @access public
	 */
	public function checkPassword($cleartext, $cryptograph) {
		switch ($this->encodeMethod) {
			case PWD_MD5 :
				return (md5 ( $cleartext ) == rtrim ( $cryptograph ));
			case PWD_CRYPT :
				return (crypt ( $cleartext, $cryptograph ) == rtrim ( $cryptograph ));
			case PWD_CLEARTEXT :
				return ($cleartext == rtrim ( $cryptograph ));
			case PWD_SHA1 :
				return (sha1 ( $cleartext ) == rtrim ( $cryptograph ));
			case PWD_SHA2 :
				return (hash ( 'sha512', $cleartext ) == rtrim ( $cryptograph ));			
			default :				
				return false;
		}
	}
	
	/**
	 * 将密码明文转换为密文
	 *
	 * @param string $cleartext 要加密的明文
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function encodePassword($cleartext) {
		switch ($this->encodeMethod) {
			case PWD_MD5 :
				return md5 ( $cleartext );
			case PWD_CRYPT :
				return crypt ( $cleartext );
			case PWD_CLEARTEXT :
				return $cleartext;
			case PWD_SHA1 :
				return sha1 ( $cleartext );
			case PWD_SHA2 :
				return hash ( 'sha512', $cleartext );
			default :
				return false;
		}
	}
	
	 /**
     * 返回指定用户的角色名数组
     *
     * @param array $user
     *
     * @return array
     */
	public function fetchRoles($user_id) {
		$user_id = intval($user_id);
		if (!$user_id) {
			return false;
		}
		$userRoles = D ( 'RoleUser' )->join("`".C('DB_PREFIX')."role` ON ".C('DB_PREFIX')."role_user.role_id=".C('DB_PREFIX')."role.role_id")
									 ->field(C('DB_PREFIX')."role.name")
									 ->where(C('DB_PREFIX')."role_user.user_id='{$user_id}'")
									 ->findAll();
		$result = array();
		if (is_array($userRoles)) {
			foreach ($userRoles as $v) {
				$result[] = $v['name'];
			}
		}
		return $result;
	}
	
	/**
	 * 对日期进行加密
	 *
	 * @return string
	 */
	public function strEncode(){
		$str = md5(rand(0,9999));
		$date = date('YmdHis');
		$str_arr = array();
		$date_arr = array();
		$arr = array(0,2,4,6,9,11,14,16,19,21,24,26,29,31);
		for($i=0;$i<strlen($str);$i++){
			$str_arr[] = substr($str, $i, 1);
		}
		for($i=0;$i<strlen($date);$i++){
			$date_arr[] = substr($date, $i, 1);
		}
		for($i=0;$i<count($arr);$i++){
			$str_arr[$arr[$i]] = $date_arr[$i];
		}
		return strrev(implode($str_arr));
	}
	
	/**
	 * 对字符串进行日期型解密
	 *
	 * @param string $str
	 * @return string
	 */
	public function strDecode($str){
		$str = strrev($str);
		$date_arr = array();
		$arr = array(0,2,4,6,9,11,14,16,19,21,24,26,29,31);
		for($i=0;$i<count($arr);$i++){
			$date_arr[] = substr($str, $arr[$i], 1);
		}
		return implode($date_arr);
	}
	
	/**
	 * 取得用户信息数组
	 * 
	 * @param int $user_id 用户ID
	 * @param boolean $extend 是否取得扩展表的信息
	 */
	public function getUserData($user_id, $extend = false) {
		if (!$user_id) return null;
		//表前缀
		$db_pre = C('DB_PREFIX');
		//left join 表 role_user 后 left join role
		$join_role_user = "`{$db_pre}role_user`
				 		  ON ({$db_pre}role_user.user_id={$db_pre}user.user_id)";
		$join_role = "`{$db_pre}role`
				 	 ON ({$db_pre}role_user.role_id={$db_pre}role.role_id)";
		$field = "{$db_pre}user.*,
				  {$db_pre}role.role_id AS `role_id`,
				  {$db_pre}role.nickname AS `rolenickname`,
				  {$db_pre}role.name AS `rolename`";
		$where = "{$db_pre}user.user_id='{$user_id}'";
		if ($extend) {
			$role_user_data = D('RoleUser')->where("`user_id`='{$user_id}'")->find();
			$role_data = D('Role')->find($role_user_data['role_id']);
			$modelData = F ('model_'.$role_data['modelid']);
			$join_extend = "`{$db_pre}{$modelData['tablename']}`
				 	 		ON ({$db_pre}user.user_id={$db_pre}{$modelData['tablename']}.user_id)";
			$field .= ",{$db_pre}{$modelData['tablename']}.*";
			$data = $this->field($field)->join($join_role_user)->join($join_role)->join($join_extend)->where($where)->find ();
		} else {
			$data = $this->field($field)->join($join_role_user)->join($join_role)->where($where)->find ();	
		}		
		return $data;
	}
}
?>