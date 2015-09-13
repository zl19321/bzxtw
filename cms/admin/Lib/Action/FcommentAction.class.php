<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Ffriendlink.class.php
// +----------------------------------------------------------------------
// | Date: 2010-07-26
// +----------------------------------------------------------------------
// | Author: fangfa <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 模块 评论
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 模块评论
 *
 */
class FcommentAction extends FbaseAction {
	/**
	 * 栏目表模型对象
	 * @var unknown_type
	 */
	protected $_category = '';

	/**
	 * 栏目数据
	 * @var unknown_type
	 */
	protected $category_data = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();

		$in = &$this->in;
		$in['_tablename'] = 'comment';
		if ($in['id']) {
			$comment = M('Comment')->find($in['id']);
			if ($comment) $in['catid'] = $comment['catid'];
		}
		//if ($in['catid']) {
			$this->_category = D ('Category');
			$this->category_data = $this->_category->find((int)$in['catid']);
			$this->assign('cat',$this->category_data);
			$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
		//} else $this->message('<font class="red">没有选择要操作的栏目</font>');
		
		$this->assign('in', $this->in);
		$this->assign('status', array('0' => '待审', '1' => '审核'));
		$this->assign('reply', array('0' => '未回复', '1' => '已回复'));
		$this->assign('q_fields', array('title' => '标题', 'username' => '留言者', 'replyer' => '回复者'));
	}
	
	/**
	 * @name检查权限
	 * @param $userData
	 * @param $permissions
	 */
	private function checkPermissions($userData,$permissions) {
		if ($userData['username'] == 'developer') return true;
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
	 * @name列表
	 */
	public function manage()
	{
		$in = &$this->in;
		$where = array();
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`=' . $in['status'];
		}
		if (isset($in['reply']) && $in['reply'] != 'all') {
			$where[] = ($in['reply'] == 0 ? ' `reply` IS NULL' : ' `reply` IS NOT NULL');
		}
		if(!empty($in['newsid']))
		{
			$where[] = ' `newsid`=' . $in['newsid'];
			$this->assign('newsid',$in['newsid']);
			$this->assign('flag',$in['flag']);
		}
		if (!empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();

		//操作条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		} else {
			$option['order'] = "`{$_keyid}` DESC ";
		}
		if ( $in [$_keyid] ) { //主键筛选
			$option ['where'] = array ($_keyid => $in [$_keyid] );
		}
		if ($in ['where']) {
			$option['where'] = &$in['where'];
		}

		//获取数据
		//初始化分页类
		$data = array ();

		//统计记录数
		$data ['count'] = $_m->where ( $option['where'] )->count ();

		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name编辑
	 */
	public function edit()
	{
		$in = &$this->in;
		
		if ($in['dosubmit']) {
			$Comment = M('Comment');
			if(!empty($in['info']['reply']))
			{
				if($in['replyed'] == 0)
				{
					$_count = M ('ContentCount');
					$_contentCount = $_count->field("`{$in['type']}`")->where("`cid`='{$in['newsid']}'")->find();			
					if (!$_contentCount) {
						$_count->add(array('cid'=>$in['newsid']));						
					}
					$_count->execute("update __TABLE__ set `comments_checked`=`comments_checked`+1 where `cid`='{$in['newsid']}' limit 1");
				}

				$data['replytime'] = time();
				$data['replyer'] = $_SESSION['userdata']['username'];
				//过滤敏感词
			$cat = D ( 'Category' );
			$cat_data = $cat->field('setting')->where(" `catid`=".$in['catid']."")->find();
			if(isset($cat_data['setting']['isfilter']) && $cat_data['setting']['isfilter']){//如果开启过滤
				$in['info'] = $this->filter();
			}
				$data['reply'] =  $in['info']['reply'];
			}else
			{
				$this->message(L("回复不能为空！"));	
			}
			$data['status'] = $in['info']['status'];
			$Comment->where('id=' . $in['info']['id'])->save($data);
			if($in['flag'] == 111)
			redirect($this->forward."&newsid=".$in['newsid']."&flag=111");
			else
			redirect($this->forward);
		} else {
			$this->assign('catid',$in['catid']);
			$this->assign('flag',$in['flag']);
			
			if ($this->ispost()) {
				if (! $in ['_tablename'])
					$this->message ( '没有指定操作表！' );
				$name = $in ['_tablename']; //数据表名
				//		die($this->getInTableName($name));
				$_m = D ( parse_name($name,1) ); //实例化表模型类
				$_keyid = $_m->getPk ();
				//过滤敏感词
				$in['info'] = $this->filter();
				//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
				$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
				if ( $_m->create ( $in ['info'] ) ) {
					if (! empty ( $in ['info'] [$_keyid] )) { //更新
						$keyid = $_m->save ();
					} else { //添加
						$keyid = $_m->add ();
						if ($keyid) $in['info'][$_keyid] = $keyid;
					}
					if (false !== $keyid) { //添加数据
						if (method_exists ( $_m, 'cache' )) { //调用缓存处理;
							$_m->cache ( ($in['info'][$_keyid] ? $in['info'][$_keyid] : $keyid), $in ['info'] );
						}
						//返回处理信息
						if ($in ['ajax'])
							$this->ajaxReturn ( $in ['info'], '记录保存成功！', 1, 'json' );
						else if($in ['_tablename'] == 'menu')
							$this->message ( '记录保存成功！', '', 0, false);
						else
							$this->message ( '记录保存成功！' );
					} else {
						//返回处理信息
						if ($in ['ajax'])
							$this->ajaxReturn ( '', $_m->getError () . '<br />数据保存失败！', 1, 'json' );
						else
							$this->message ( $_m->getError () . '<br />数据保存失败！' );
					}
				} else {
					if ($in ['ajax'])
						$this->ajaxReturn ( '', $_m->getError (), 1, 'json' );
					else
						$this->message ( $_m->getError ().'记录保存失败！' );
				}
			}
			//获取数据
			if (!empty($in['_tablename'])) {
				$name = $in ['_tablename']; //数据表名
				$_m = D ( parse_name($name,1) ); //实例化表模型类
				$_keyid = $_m->getPk ();
				if ( $in [$_keyid] ) { //编辑
					$keyid = $in [$_keyid] ;
					$data = $_m->find ( $keyid );
					if (isset($data['parentid']) && $data['parentid']>0) {
						$this->assign('parent_data',$_m->find($data['parentid']));
					}
					$this->assign ( 'data', $data );
				}
			}
			if (!empty($in['tpl'])) {
				$this->display ( $in ['tpl'] );
			} else {
				$this->display();
			}
		}
	}
	
	/**
	 * @name审核
	 */
	public function check()
	{
		
		$in = &$this->in;
		if ($in['id'] && isset($in['status'])) {
			$data['id'] = $in['id'];
			$data['status'] = $in['status'];
			$_model = M('Comment');
			$_model->save($data);
		}
		redirect($this->forward);
	}
	
	/**
	* @name过滤敏感词
	*/
	public function filter(){
		$in = &$this->in;
		$filterwords = explode('|',C ('FILTER_WORD'));//敏感词组
		foreach($filterwords as $k => $v){
			$filterwords[$k] = '/'.$v.'/';
		}
		$in['info']['reply'] = preg_replace($filterwords,'***',$in['info']['reply']);
		$in['info']['comment'] = preg_replace($filterwords,'***',$in['info']['comment']);
		return $in['info'];
	}
	
	/**
	 * @name删除留言
	 */
	public function delete()
	{
		$in = &$this->in;
		if(empty($this->in['info']['id']))		
		$this->in['id']=$in['id'];
		else
		$this->in['id'] = $this->in['info']['id'];
		
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名
		//		die($this->getInTableName($name));
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
		$_model = D ( $name );
		//安全起见，必须包含删除的记录的主键，或者删除条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		}
		if ($in [$_keyid] ) { //主键筛选
			if (is_array($in[$_keyid])) {
				if (!empty($in [$_keyid])) {
					$option ['where'] = " `{$_keyid}` IN (".implode(',', $in[$_keyid]) .")";
				}
			} else {
				$option ['where'] = array ($_keyid => $in [$_keyid] );
			}
		}
		if ($in ['where']) {
			if (!empty($option ['where'])) {
				@$option['where'] = array_merge($in['where'],$option ['where']);
			} else {
				$option['where'] = &$in['where'];
			}
		}
		if (! empty ( $option )) {
			if (false !== $_m->delete($option)) {
				if (method_exists ( $_m, 'cache' )) { //删除缓存
					if (is_array($in[$_keyid])) {
						if (!empty($in [$_keyid])) {
							foreach ($_keyid as $k) {
								$_m->cache ( $k , null );
							}
						}
					} else if (is_numeric($in [$_keyid])) {
						$_m->cache ( $_keyid , null );
					}
				}
				$this->message('删除成功！');
			} else {
				$this->message($_m->getError() . '删除失败！');
			}
		} else {
			$this->message ( '参数错误，没有指定删除条件！' );
		}
	}
}