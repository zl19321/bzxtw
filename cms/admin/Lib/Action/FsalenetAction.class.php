<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FtagAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 网络营销
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 网络营销
 *
 */
class FsalenetAction extends FbaseAction {
	/**
	 * 网络营销分类对象
	 * 
	 * @var object
	 */	
	protected $_salenet_cat = null;
	/**
	 * 栏目表模型对象
	 * 
	 * @var object
	 */
	protected $_category = null;

	/**
	 * 所在栏目数据
	 * 
	 * @var array
	 */
	protected $category_data = array();


	/**
	 * @name网络营销管理初始化，主要用户检查操作权限
	 * 
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		$this->_salenet_cat = D ('Scategory');
		$this->assign('cate_data',$this->_salenet_cat->select());

		if (strtolower(MODULE_NAME) == 'fsalenet') {
			if ($in['catid']) { //检查栏目操作权限
				$this->_category = D ('Category');
				$this->category_data = $this->_category->find((int)$in['catid']);
				$this->assign('cat',$this->category_data);
				$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
			} else {
				$this->message('<font class="red">没有选择要操作的栏目~！</font>');
			}
		}
		
		$_province = D("Province")->findAll();
		$this->assign("province",$_province);
	}

	/**
	 * @name检查权限
	 * 
	 * @param $userData
	 * @param $permissions
	 */
	private function checkPermissions($userData,$permissions) {
		if ($userData['username'] == 'developer') {
			return true;
		}
		$has = false;
		if (is_array($userData['roles'])) {
			foreach ($userData['roles'] as $v) {  //如果有一个角色有权限   那就有权限
				if (in_array($v,$permissions[ACTION_NAME])) $has = true;
			}
		}
		if (!$has) {
			$this->message('<font class="red">无权访问！</font>');
		}
		return true;
	}

	/**
	 * @name栏目管理
	 */
	public function category_manage() {

		
		$in = &$this->in;
		$data = array ();
		$_aslenet = D ('Salenet');
		$db_pre = c('DB_PREFIX');
		
		$where = array ();

		$where["{$db_pre}salenet.catid"] = $in['catid'];


	}

	/**
	 * @name营销内容管理
	 * 
	 */
	public function manage() {
		$in = &$this->in;
		$data = array ();
		$_aslenet = D ( 'Salenet' );
		//表前缀
		$db_pre = C('DB_PREFIX');

		
		//查询条件
		$where = array ();
		$where["{$db_pre}salenet.catid"] = $in['catid'];		
		if ($in['q'] && $in['q'] != '请输入关键字') {
			$in['q'] = urldecode($in['q']);
			$where["{$db_pre}salenet.{$in['field']}"] = array('like',"%{$in['q']}%");
		}
		//排序条件
		$order = " {$db_pre}salenet.`sort` ASC,{$db_pre}salenet.`sid` DESC ";
		//联合查询
		$join_role_cate = "`{$db_pre}scategory`
					 		  ON ({$db_pre}salenet.scatid={$db_pre}scategory.scategoryid)";

		//统计模型数量
		$data ['count'] = $_aslenet->join($join_role_cate)->where ( $where )->count();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据
		$data ['info'] = $_aslenet->field("{$db_pre}salenet.`sid`,{$db_pre}salenet.`title`,{$db_pre}salenet.`province`,{$db_pre}salenet.`catid`,{$db_pre}salenet.`content`,{$db_pre}salenet.`create_at`,{$db_pre}salenet.`status`,{$db_pre}salenet.`url`,{$db_pre}salenet.`sort`,{$db_pre}salenet.`name`,{$db_pre}scategory.`scategoryid`")->join($join_role_cate)->where ( $where )->order ( $order )->limit ( $Page->firstRow . ',' . $Page->listRows )->select();
		$_province = D("Province");
        foreach ($data['info'] as $k => $v){
			$data['info'][$k]['province'] = $_province->where("id=".$v['province'])->getField("name");
		}
		
        
		$this->assign ( 'data', $data );
		$this->assign('in', $in);
		$this->display();
	}

	/**
	 * @name审核内容
	 * 
	 */
	 public function check() {
		$in = &$this->in;
		if (!$in['sid']) $this->message('<font class="red">参数不完整！</font>');
		$data = array(
			'sid'	=>	$in['sid'],
			'status'	=>	$in['status']
		);
		M('Salenet')->save($data);
		redirect($this->forward);				

	 }

	/**
	 * @name录入要显示的内容
	 * 
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_add();
		}
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			$_salenet = D ('Salenet');
			//令牌验证			

			if (!$_salenet->autoCheckToken($in)) $this->message('<font class="red">请不要非法提交或者重复刷新页面！</font>');
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			$in['info']['url'] = 'null';
			$in['info']['catid'] = $in['catid'];
			$in['info']['create_at'] = time();
			$in['info']['update_at'] = time();
			
			$return = $_salenet->add($in['info']);

			if (false !== $return) {

				$in['info']=null;
				$in['info']['sid'] = $return;
				$in['info']['url'] = "{$return}". C ('URL_HTML_SUFFIX');
				$_salenet->save($in['info']);
				
				$this->message('<font class="green">内容保存成功！</font>',U('fsalenet/manage?catid='.$in['catid']),3,false);				
				exit;
			} else {
				$this->message('<font class="red">' . $_salenet->getError() . '内容保存失败！</font>');
			}
		}
		$this->display();
	}

	

	/**
	 * @name更新内容
	 * 
	 */
	public function edit() {
		$in = &$this->in;
		if ($in['ajax']) $this->_ajax_edit();
		if ($in['do']) $this->_do_edit();
		if (!$in['sid']) $this->message('<font class="red">参数错误，无法继续该操作！</font>');
		$_salenet = D ('Salenet');
		$cat_data = $this->category_data;
		if ($this->ispost()) {  //录入内容
			//令牌验证
//			if (!$_salenet->autoCheckToken($in)) 
//				$this->message('请不要非法提交或者重复刷新页面！'); //由于tags参数不能传数组，请将数组参数格式化

			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			$in['info']['update_at'] = time();

			if(!strstr($in['info']['url'],'.html'))$in['info']['url']=$in['info']['url']. C ('URL_HTML_SUFFIX');

			if (false !== $_salenet->save($in['info'])) {				
				$this->message('<font class="green">内容更新成功！</font>',U('fsalenet/manage?catid='.$in['catid']));				
			} else {
				$this->message('<font class="red">' . $_salenet->getError() . '内容更新失败！</font>');
			}
		}
		$this->assign('data',$_salenet->find($in['sid']));
		$this->display();
	}


	/**
	 * @name更新内容的AJAX请求
	 */
	private function _ajax_edit() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'sort':  //更新排序
				$in['sid'] && $in['sid'] = substr($in['sid'],5);
				$in['sort'] = intval($in['sort']);
				if ($in['sort'] == '0' || !empty($in['sort'])) {
					$_salenet = M ('Salenet');
					$data = $_salenet->find($in['sid']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_salenet->save($data)) {
							echo $data['sort'];
							exit ();
						}
					}
				}
				echo '';
				break;
			case 'savetitle':  //更新标题
				$in['sid'] && $in['sid'] = substr($in['sid'],6);
				if (!empty($in['title'])) {
					$_salenet = M ('salenet');
					$data = $_salenet->find($in['aid']);
					if (is_array($data)) {
						$data['title'] = $in['title'];
						if (false !== $_salenet->save($data)) {
							echo $data['title'];
							exit ();
						}
					}
				}
				echo '';
				break;					
			default:
				break;
		}
		exit ();
	}


	/**
	 * @name删除营销信息
	 */
	public function delete() {
		$in = &$this->in;
		if (!$in['sid']) $this->message('<font class="red">没有选择操作项！</font>');
		$_salenet = D('Salenet');
		if (is_numeric($in['sid'])) {
			$_salenet->delete($in['sid']);
			$this->message('<font class="green">删除成功！</font>');
		}
	}
	
}