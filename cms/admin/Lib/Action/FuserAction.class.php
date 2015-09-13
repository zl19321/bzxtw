<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FuserAction.class.php
// +----------------------------------------------------------------------
// | Date: 2011-1-30
// +----------------------------------------------------------------------
// | Author: 孙斌 <sunyichi@163.com>
// +----------------------------------------------------------------------
// | 文件描述:  会员管理
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name RBAC访问控制设置
 *
 */
class FuserAction extends FbaseAction {
	
	/**
	 * User类对象
	 * @var object
	 */
	protected $_user = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		import('User',INCLUDE_PATH);//导入User类
		//所有会员角色
		$this->_user = get_instance_of('User');
		$roles = D('Role')->field("`role_id`,`nickname`")->where("`status`='1' and `isadmin`='0'")->order("`role_id` ASC ")->findAll();
		$this->assign('roles',$roles);
	}
	
	/**
	 * @name 会员管理
	 *
	 */
	public function manage() {
		$in = &$this->in;
		$where = array();
		if (true !== C('USER_PASSPORT_ON')) {
			$_user = M ('User');
			//表前缀
			$db_pre = C('DB_PREFIX');
			$data = array ();
			//查询条件
			$where = array ();
			$where["{$db_pre}user.isadmin"] = 0;
			if ($in['role_id']) {
				$where["{$db_pre}role_user.role_id"] = $in['role_id'];
			}
			if ($in['q'] && $in['q'] != '请输入关键字') {
				if($in['type']=='rolename')$where["{$db_pre}role.name"] = $in['q'];
				else if($in['type']=='rolenickname')$where["{$db_pre}role.nickname"] = $in['q'];
				else $where["{$db_pre}user." . $in['type']] = $in['q'];
			}
			//排序条件
			if ($in['order']) {
				$order = "{$db_pre}user.`user_id` DESC";
			} else {
				$order = "{$db_pre}user.`user_id` DESC";
			}
			//left join 表 role_user 再 left join role 后 left join model
			$join_role_user = "`{$db_pre}role_user`
					 		  ON ({$db_pre}role_user.user_id={$db_pre}user.user_id)";
			$join_role = "`{$db_pre}role`
					 	 ON ({$db_pre}role_user.role_id={$db_pre}role.role_id)";
			$join_model = "`{$db_pre}model`
					 	 ON ({$db_pre}model.modelid={$db_pre}role.modelid)";
			//查找字段
			$field = "{$db_pre}user.*,
					  {$db_pre}role.nickname AS `rolenickname`,
					  {$db_pre}role.name AS `rolename`,
					  {$db_pre}model.name AS `modelname`";
			//统计会员数量
			$data ['count'] = $_user->join($join_role_user)->join($join_role)->join($join_model)->where ( $where )->count ();			
			//初始化分页类
			$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
			$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );			
			//分页代码
			$data ['pages'] = $Page->show ();			
			//当前页数据
			$data ['info'] = $_user->field($field)->join($join_role_user)->join($join_role)->join($join_model)->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		} else { //处理通行证模式
			//TODO
		}
		//IP->地址
		if (is_array($data['info'])) {
			import('ORG.Net.IpArea');
			$_ipArea = new IpArea(INCLUDE_PATH . '/ipdata/QQWry.Dat');
			foreach ($data['info'] as $k=>$v) {
				$data['info'][$k]['last_login_location'] = $_ipArea->get($v['last_login_ip']);
			}
		}
		$this->assign ('data', $data);
		$this->assign('in', $this->in);
		$this->display();
	}
	
	/**
	 * @name添加会员
	 * 
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) $this->_add_ajax();
		if(!isset($in['step'])){
			/* 需要先选择用户模型，在选择角色时使用以下代码
			$models = array('' => '--选择--');
			$_model = D ( 'Model' );
			$where = "`status`='1' AND `moduleid`='{$in['moduleid']}' AND `exttable`<>'manager'";
			$models += $_model->selectModel($where);
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
			*/
			$this->assign ( 'step', 1 );
		}
		if ($this->ispost()) { //添加进数据库
			$_user = &$this->_user;
			if (!$_user->autoCheckToken($in)) { //令牌验证
				$this->error ( '请不要非法提交或者重复提交页面！</font>' );
			}
			if(isset($in['step']) && $in['step']=='1'){
				//获取要显示的字段，及其相关属性
				$role_data = D('Role')->find($in['role_id']);
				$field_data = D('ModelField')->where("`modelid`='{$role_data['modelid']}' AND `systype`<>'2' AND `status`='1'")->order('`sort` ASC')->findAll();
				$form = D('Model')->getForm($field_data,array(),array("repassword"=>'equalTo="#info_password"', "username"=>'remote="'.U('fuser/add?ajax=checkusername').'"', "email"=>'remote="'.U('fuser/add?ajax=checkemail').'"'));
				$this->assign( 'form_data', $form );
				$this->assign ( 'step', 2 );
				$this->assign ( 'role_id', $in['role_id'] );
			}
			elseif(isset($in['step']) && $in['step']=='2'){
				if (false === $_user->insert($in['info'],(int)$in['info']['isadmin'])) {
					$this->message('<font class="red">' . $_user->getError() . '会员添加失败！</font>');
				} else {
					$this->message('<font class="green">会员添加成功！</font>', U("fuser/manage") );
				}
			}
		}
		$this->assign ( 'moduleid', $in['moduleid'] );
		//TODO
		$this->display();
	}

	/**
	 * @name处理添加用户时候的ajax请求
	 * 
	 */
	protected function _add_ajax() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'checkusername':
				$_user = D ('User');
				if (true === $_user->validate($in['info'],'username')) {
					die('true');
				} else {
					die('false');
				}
				break;
			case 'checkemail':
				$_user = D ('User');
				if (true === $_user->validate($in['info'],'email')) {
					die('true');
				} else {
					die('false');
				}
				break;
			case 'getrole':
				$roles = D('Role')->field("`role_id`,`nickname`")->where("`modelid`='{$in['modelid']}' and `status`='1' and `isadmin`='0'")->order("`role_id` ASC ")->findAll();
				$selectrole = array();
				if (is_array ( $roles )) {
					foreach ( $roles as $v ) {
						$selectrole [$v['role_id']] = array (
							'title' => $v['nickname'], 'value' => $v['role_id']
						);
					}
					echo Html::select('role_id', $selectrole,'','title="【所属会员角色】必须选择" class="required"');
				}
				break;
			default:
				;
		}
		exit();
	}
	
	/**
	 * @name编辑会员
	 * 
	 */
	public function edit() {
		$in = &$this->in;
		$_user = &$this->_user;
		if ($in['do'] == 'status') {
			$this->status();
		}
		
		if ($this->ispost()) {
			if (!$in['info']['user_id']) {
				$this->message('<font class="red">参数错误，没有选择要修改的会员！</font>');
			}
			if ($in['info']['user_id'] == '1') $this->message('<font class="red">操作终止，不能更改【会员】状态！</font>');
			if (!$_user->autoCheckToken($in)) { //令牌验证
				$this->error ( '<font class="red">请不要非法提交或者重复提交页面！</font>' );
			}
			if (false === $_user->update($in['info'],(int)$in['info']['isadmin'])) {
				$this->message('<font class="red">' . $_user->getError() . '会员信息修改失败！</font>');
			} else {
				$this->message('<font class="greeen">会员信息修改成功！</font>', U('fuser/manage') );
			}
		}
		if (!$in['user_id']) {
			$this->message('<font class="red">参数错误，没有选择要修改的会员！</font>');
		}
		if ($in['user_id'] == 1) {
			$this->message('<font class="red">操作终止，不能更改【会员】状态！</font>');
		}
		//获取会员详情
		$data = $_user->getUserData($in['user_id'], true);
		$data['password'] = '';
		//获取要显示的字段，及其相关属性
		$role_user_data = D('RoleUser')->where("`user_id`='{$in['user_id']}'")->find();
		$role_data = D('Role')->find($role_user_data['role_id']);
		$field_data = D('ModelField')->where("`modelid`='{$role_data['modelid']}' AND `systype`<>'2' AND `status`='1'")->order('`sort` ASC')->findAll();
		$form = D('Model')->getForm($field_data,$data,array("repassword"=>'equalTo="#info_password"', "username"=>'readonly="readonly"', "email"=>'readonly="readonly"'));
		$this->assign( 'form_data', $form );
		//获取当前角色的所有同模型角色
		$roles = D('Role')->field("`role_id`,`nickname`")->where("`status`='1' and `isadmin`='0' and `modelid`='{$role_data['modelid']}'")->order("`role_id` ASC ")->findAll();
		$this->assign( 'roles',$roles );
		$this->assign( 'data',$data );
		$this->display();
	}
	
	/**
	 * @name修改会员状态
	 * 
	 */
	protected function status() {
	$in = &$this->in;
		$_user = &$this->_user;
		if (!$in['user_id']) {
			 $this->message('<font class="red">参数错误，没有指定删除项！</font>');
		}
		if (is_numeric($in['user_id'])) {
			if ($in['user_id'] == '1') $this->message('<font class="red">操作终止，不能更改【会员】状态！</font>');
		}
		if (is_array($in['user_id'])) {
			if (in_array('1',$in['user_id']))  $this->message('<font class="red">操作终止，不能更改【会员】状态！</font>');
		}
		if (false === $_user->status($in['user_id'],$in['status'])) {
			$this->message('<font class="red">' . $_user->getError() . '操作发生错误！</font>',U('fuser/manage'));
		} else {
			redirect(U('fuser/manage'));
		}
		exit ();
	}
	
	/**
	 * @name删除会员
	 * 
	 */
	public function delete() {
		$in = &$this->in;
		$_user = &$this->_user;
		if (!$in['user_id']) {
			 $this->message('<font class="red">参数错误，没有指定删除项！</font>');
		}
		if (is_numeric($in['user_id'])) {
			if ($in['user_id'] == '1') $this->message('<font class="red">操作终止，【会员】无法删除！</font>');
		}
		if (is_array($in['user_id'])) {
			if (in_array('1',$in['user_id']))  $this->message('<font class="red">操作终止，【会员】无法删除！</font>');
		}
		if (true === $_user->delete($in['user_id'])) {
			$this->message('<font class="red">操作成功！</font>',U('fuser/manage'));
		} else {
			$this->message('<font class="red">' . $_user->getError() . '操作发生错误！</font>',U('fuser/manage'));
		}
		//TODO
		$this->display();
	}

	/**
	* 递归转化$array数组为字符串
	*
	* @param $array 任何类型(主要是用于数组)
	*/
	protected function loop($array){
		if(!is_array($array)){
		   return $array;
		}
		//声明一条字符串，用来组合解析的所有值
		$w_string_array = "Array (";
		if(isset($array)){
			//获得所有的key
			$keys = array_keys($array);
			//遍历所有的key
			for($i=0;$i<count($keys);$i++){
				//获得一个key
				$key = $keys[$i];
				//获得一个值
				$value = $array[$key];
				//递归追加
				$w_string_array = $w_string_array." [$key] => ".$this->loop($value);
			}
		}
		$w_string_array .= " )";
		return $w_string_array;
	}
}
?>