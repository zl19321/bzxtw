<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Ffriendlink.class.php
// +----------------------------------------------------------------------
// | Date: 2010-07-26
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 模块 留言板
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 留言板模块
 *
 */
class FguestbookAction extends FbaseAction {
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
		if ($in['id']) {
			$guestbook = M('Guestbook')->find($in['id']);
			if ($guestbook) $in['catid'] = $guestbook['catid'];
		}
		if ($in['catid']) {
			$this->_category = D ('Category');
			$this->category_data = $this->_category->find((int)$in['catid']);
			$this->assign('cat',$this->category_data);
			$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
		} else $this->message('<font class="red">没有选择要操作的栏目</font>');

		$this->assign('in', $this->in);
		$this->assign('status', array('0' => '待审', '1' => '审核'));
		$this->assign('reply', array('0' => '未回复', '1' => '已回复'));
		$this->assign('q_fields', array('title' => '标题', 'username' => '留言者', 'replyer' => '回复者'));
	}

	/**
	 * 检查权限
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
		if ($in['catid']) {
			$where[] = " `catid`='{$in['catid']}' ";
		}
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`=' . $in['status'];
		}
		if (isset($in['reply']) && $in['reply'] != 'all') {
			$where[] = ($in['reply'] == 0 ? ' `reply` IS NULL' : ' `reply` IS NOT NULL');
		}
		if (!empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		$_mGuestbook = D ( 'Guestbook' ); //实例化表模型类

		//操作条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		} else {
			$option['order'] = "`id` DESC ";
		}
		if ($in ['where']) {
			$option['where'] = &$in['where'];
		}

		//获取数据
		//初始化分页类
		$data = array ();

		//统计记录数
		$data ['count'] = $_mGuestbook->where ( $option['where'] )->count ();

		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_mGuestbook->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		$this->display();
	}

	/**
	 * @name编辑
	 */
	public function edit()
	{
		$in = &$this->in;
		$_mGuestbook = D ( 'Guestbook' ); //实例化表模型类
		if ($in['do']) {
			$_model = M('guestbook');
			$data[$in['do']] = $in[$in['do']];
			$_model->where('id=' . $in['id'])->save($data);
			redirect($this->forward);
		} else {
			if ($this->isPost()) {				
				//过滤敏感词语
				$cat = D ( 'Category' );
				$cat_data = $cat->field('setting')->where(" `catid`=".$in['catid']."")->find();
				if(isset($cat_data['setting']['isfilter']) && $cat_data['setting']['isfilter']){//如果开启过滤
					$in['info'] = $this->filter();
				}
				//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
				$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
				if ( $_mGuestbook->create ( $in ['info'] ) ) {
					if (! empty ( $in ['info'] ['id'] )) { //更新
						$keyid = $_mGuestbook->save ();
					} else { //添加
						$keyid = $_mGuestbook->add ();						
					}
					if (false !== $keyid) { //添加数据
						
						//返回处理信息
						if ($this->isAjax())
							$this->ajaxReturn ( $in ['info'], '记录保存成功！', 1, 'json' );
						else
							$this->message ( '记录保存成功！' );
					} else {
						//返回处理信息
						if ($this->isAjax())
							$this->ajaxReturn ( '', $_mGuestbook->getError () . '<br />数据保存失败！', 1, 'json' );
						else
							$this->message ( $_mGuestbook->getError () . '<br />数据保存失败！' );
					}
				} else {
					if ($this->isAjax())
						$this->ajaxReturn ( '', $_mGuestbook->getError (), 1, 'json' );
					else
						$this->message ( $_mGuestbook->getError ().'记录保存失败！' );
				}
			}
			//获取数据		
			$this->assign ( 'data', $_mGuestbook->find ( $in['id'] ) );
			$this->display();
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
			$_model = M('Guestbook');
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
		$in['info']['title'] = preg_replace($filterwords,'***',$in['info']['title']);
		$in['info']['content'] = preg_replace($filterwords,'***',$in['info']['content']);
		$in['info']['reply'] = preg_replace($filterwords,'***',$in['info']['reply']);
		return $in['info'];
	}

	/**
	 * @name删除留言
	 */
	public function delete()
	{
		$in = &$this->in;
		$_mGuestbook = D ( 'Guestbook' ); //实例化表模型类
		
		//安全起见，必须包含删除的记录的主键，或者删除条件
		$option = array ();
		
        
        //lwh update
		if ($in ['id'] ) { 
			
				$option ['where'] = array ('id' => $in ['id'] );

		}elseif($in['info']['id']){

				$option ['where'] = " `id` IN (".implode(',', $in['info']['id']) .")";
          
		}		
		if (! empty ( $option )) {
			if (false !== $_mGuestbook->delete($option)) {				
				$this->message('删除成功');
			} else {
				$this->message($_mGuestbook->getError() . '删除失败');
			}
		} else {
			$this->message ( '参数错误，没有指定删除条件' );
		}
	}
}
?>