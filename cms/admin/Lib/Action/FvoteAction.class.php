<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FvoteAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-1-2
// +----------------------------------------------------------------------
// | Author: 成俊<cgjp123@163.com>
// +----------------------------------------------------------------------
// | 文件描述:  投票
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 投票管理
 *
 */
class FvoteAction extends FbaseAction
{
	protected $_category = '';
	protected $category_data = '';

	/**
	 * @name初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'vote_subject';

		if ($in['catid']) {
			$this->_category = D ('Category');
			$this->category_data = $this->_category->find((int)$in['catid']);
			$this->assign('cat',$this->category_data);
			$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
		} else $this->message('<font class="red">没有选择要操作的栏目</font>');
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
	 * @name投票列表
	 */
	public function manage()
	{
		$in = &$this->in;
		$in ['where']['ismultiple'] = 0;
		$in ['where']['parentid'] = 0;
		$in ['where']['catid'] = $in['catid'];
		$this->manageData();
	}

	/**
	 * @name添加投票
	 */
	public function add()
	{
		$in =&$this->in;

		if ($this->ispost()) {
			$_model = M('vote_subject');
			$in['_tablename'] = 'vote_subject';
			$in['info']['create_time'] = $in['info']['update_time'] = time();
			$in['info']['userid'] = $_SESSION['userdata']['user_id'];
			$in['info']['optionnumber'] = count($in['info']['option']);
			$in['info']['groupidsvote'] = serialize($in['info']['groupidsvote']);
			$in['info']['groupidsview'] = serialize($in['info']['groupidsview']);
			$in['info']['catid'] = $in['catid'];

			$_m = D ('vote_subject'); //实例化表模型类
			$_keyid = $_m->getPk ();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				$subjectid = $_m->add ();
				if ($subjectid) {  //插入选项值
					$option_model = M('vote_option');
					foreach ($in['info']['option'] AS $key=>$value) {
						$option_model->subjectid = $subjectid;
						$option_model->option = $value;
						$option_model->image = $in['info']['image'][$key];
						$option_model->add();
					}

					$this->message('<font class="green">添加成功！</font>');
				} else $this->message('<font class="red">添加失败！</font>');
			} else $this->message('<font class="red">请不要重复提交！</font>');
		} else {
			$this->assign('editer_html', Html::editor('info[description]', '', C('EDITOR_TYPE'), array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
			//获取用户组
			$_role = D ( 'Role' );
			$role_data = $_role->where ( "`status`='1' " )->getField('name,nickname');
			$this->assign('role_data', $role_data);
		}

		$this->display();
	}

	/**
	 * @name编辑投票
	 */
	public function edit()
	{
		$in = &$this->in;

		if ($in['ajax']) {
			$this->_ajax_edit();
			exit;
		}
		$_model = D('vote_subject');
		$option_model = M('vote_option');

		if ($this->ispost() && $in['subjectid']) {
			$in['info']['optionnumber'] = count($in['info']['option']);
			$in['info']['groupidsvote'] = serialize($in['info']['groupidsvote']);
			$in['info']['groupidsview'] = serialize($in['info']['groupidsview']);
			$in['info']['subjectid'] = $in['subjectid'];
			$in['info']['update_time'] = time();

			$_m = D ('vote_subject'); //实例化表模型类
			$_keyid = $_m->getPk ();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				if ($_m->save ()) {  //更新选项值
					//旧选项值
					$old_option = $option_model->where('subjectid='.$in['subjectid'])->getField('optionid,`option`');
					foreach ($in['info']['option'] AS $key=>$value) {
						$option_model->subjectid = $in['subjectid'];
						$option_model->option = $value;
						$option_model->image = $in['info']['image'][$key];
						if (!empty($in['info']['option_ids'][$key])) {
							$option_model->optionid = $in['info']['option_ids'][$key];
							$option_model->save();
							unset($old_option[$in['info']['option_ids'][$key]]);
						} else {
							$option_model->optionid = '';
							$option_model->add();
						}
					}
					if (!empty($old_option)) $option_model->delete(implode(',', array_keys($old_option)));

					$this->message('<font class="green">编辑成功！</font>');
				} else {
                    $this->message('<font class="red">编辑失败！</font>');
                }
			} else $this->message('<font class="red">请不要重复提交！</font>');
		}
		$data = $_model->find($in['subjectid']);
		$data['groupidsvote'] = unserialize($data['groupidsvote']);
		$data['groupidsview'] = unserialize($data['groupidsview']);
		$data['option'] = $option_model->where('subjectid='.$in['subjectid'])->findAll();

		$this->assign('editer_html', Html::editor('info[description]', $data['description'], C('EDITOR_TYPE') , array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
		//获取用户组
		$_role = D ( 'Role' );
		$role_data = $_role->where ( "`status`='1' " )->getField('name,nickname');
		$this->assign('role_data', $role_data);
		$this->assign('data', $data);
		$this->display();
	}
	
	/**
	 *@name 处理投票AJAX请求 
	 */
	protected function _ajax_edit()
	{
		$in = &$this->in;

		$_model = D('vote_subject');
		$data = array();
		$data['subjectid'] = $in['subjectid'];
		switch ($in['ajax']) {
			case 'status':
				$data['status'] = $in['status'];
				$_model->save($data);
				break;
		}

		redirect($this->forward);
	}

	/**
	 * @name删除问卷
	 */
	public function delete()
	{
		$in = &$this->in;

		$_model = D('vote_subject');

		if (is_array($in['subjectid'])) {
			$ids = implode(',', $in['subjectid']);
		} elseif (is_numeric($in['subjectid'])) {
			$ids = $in['subjectid'];
		}
		$_model->delete($ids);

		//删除选项
		$option_model = M('vote_option');
		$option_model->where('subjectid IN('.$ids.')')->delete();

		redirect($this->forward);
	}

	/**
	 * @name查看问卷统计
	 */
	public function voteshow()
	{
		$in = &$this->in;

		$_model = D('vote_subject');
		$_vote_data_model = M('vote_data');
		$_option_model = M('vote_option');

		$subject = $_model->find($in['subjectid']);
		$subject['total_vote_num'] = 0;
		if ($subject['ismultiple'] == 1) { //问卷
			$subject_child = $_model->where('parentid='.$subject['subjectid'])->order('`sort` ASC')->findAll();
		} else { //投票
			$subject_child = array($subject);
		}

		$vote_data = $_vote_data_model->where('subjectid='.$subject['subjectid'])->findAll();
		foreach ($subject_child AS &$sc) {
			$sc['total_vote_num'] = 0;  //总投票数
			$vote_option = $_option_model->where('subjectid='.$sc['subjectid'])->findAll();
			foreach ($vote_option AS $vo) {
				$vo['vote_num'] = 0;
				$sc['options'][$vo['optionid']] = $vo;
			}

			if (is_array($vote_data)) {
				foreach ($vote_data AS $vd) {
					$vd['data'] = unserialize($vd['data']);
					if (is_string($vd['data'][$sc['subjectid']])) $vd['data'][$sc['subjectid']] = array($vd['data'][$sc['subjectid']]);
					foreach ($vd['data'][$sc['subjectid']] AS $o_id) {
						if(array_key_exists($o_id, $sc['options'])) {
							$sc['options'][$o_id]['vote_num']++;
							$sc['total_vote_num']++;
							$subject['total_vote_num']++;
						}
					}
				}
			}
		}
		$subject['child'] = $subject_child;

		$this->assign('subject', $subject);
		$this->display();
	}
	/**
	 * @name问卷列表
	 */
	public function survey_manage()
	{
		$in = &$this->in;
		$in['where']['ismultiple'] = 1;
		$in['where']['parentid'] = 0;
		$in['where']['catid'] = $in['catid'];
		$this->manageData();
	}

	/**
	 * @name添加问卷
	 *
	 */
	public function survey_add()
	{
		$in =&$this->in;

		if ($this->ispost()) {
			$_model = M('vote_subject');
			$in['_tablename'] = 'vote_subject';
			$in['info']['create_time'] = $in['info']['update_time'] = time();
			$in['info']['userid'] = $_SESSION['userdata']['user_id'];
			$in['info']['catid'] = $in['catid'];

			if (isset($in['info']['parentid'])) {
				$in['info']['ismultiple'] = 0;
				$in['info']['optionnumber'] = count($in['info']['option']);
				$in['info']['groupidsvote'] = '';
				$in['info']['groupidsview'] = '';
			} else {
				$in['info']['ismultiple'] = 1;
				$in['info']['groupidsvote'] = serialize($in['info']['groupidsvote']);
				$in['info']['groupidsview'] = serialize($in['info']['groupidsview']);
			}


			$_m = D ('vote_subject'); //实例化表模型类
			$_keyid = $_m->getPk ();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				$subjectid = $_m->add ();
				if ($subjectid) {  //插入选项值
					if (is_array($in['info']['option'])) {
						$option_model = M('vote_option');
						foreach ($in['info']['option'] AS $key=>$value) {
							$option_model->subjectid = $subjectid;
							$option_model->option = $value;
							$option_model->image = $in['info']['image'][$key];
							$option_model->add();
						}
					}
					$this->message('<font class="green">添加成功！</font>');
				} else $this->message('<font class="red">添加失败！</font>');
			} else $this->message('<font class="red">请不要重复提交！</font>');
		} else {
			$this->assign('editer_html', Html::editor('info[description]', '', C('EDITOR_TYPE'), array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
			//获取用户组
			$_role = D ( 'Role' );
			$role_data = $_role->where ( "`status`='1' " )->getField('name,nickname');
			$this->assign('role_data', $role_data);
		}
		$this->display();
	}

	/**
	 * @name编辑问卷
	 */
	public function survey_edit()
	{
		$in = &$this->in;

		if ($in['ajax']) {
			$this->_ajax_edit();
			exit;
		}
		$_model = D('vote_subject');

		if ($this->ispost() && $in['subjectid']) {
			$in['info']['optionnumber'] = count($in['info']['option']);
			$in['info']['groupidsvote'] = serialize($in['info']['groupidsvote']);
			$in['info']['groupidsview'] = serialize($in['info']['groupidsview']);
			$in['info']['subjectid'] = $in['subjectid'];
			$in['info']['update_time'] = time();

			$_m = D ('vote_subject'); //实例化表模型类
			$_keyid = $_m->getPk ();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				if ($_m->save ()) {  //更新选项值

					$this->message('<font class="green">编辑成功！</font>');
				} else $this->message('<font class="red">编辑失败！</font>', '', 3000);
			} else $this->message('<font class="red">请不要重复提交！</font>');
		}
		$data = $_model->find($in['subjectid']);
		$data['groupidsvote'] = unserialize($data['groupidsvote']);
		$data['groupidsview'] = unserialize($data['groupidsview']);

		$this->assign('editer_html', Html::editor('info[description]', $data['description'], C('EDITOR_TYPE'), array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
		//获取用户组
		$_role = D ( 'Role' );
		$role_data = $_role->where ( "`status`='1' " )->getField('name,nickname');
		$this->assign('role_data', $role_data);
		$this->assign('data', $data);
		$this->display();
	}

	/**
	 * @name问卷主题列表
	 */
	public function survey_manage_subject()
	{
		$in = &$this->in;

		$_model = D('vote_subject');
		$subject = $_model->find($in['subjectid']);

		if ($in['ajax']) {
			$data['subjectid'] = $in['subjectid'];
			switch ($in['ajax']) {
				case 'status':
					$data['status'] = $in['status'];
					$_model->save($data);
					break;
			}

			redirect($this->forward);
			exit;
		}
		//获取问卷主题列表
		$subject['data'] = $_model->where('parentid='.$subject['subjectid'])->order('`sort` ASC')->findAll();

		$this->assign('subject', $subject);
		$this->display();
	}

	/**
	 * @name问卷主题编辑
	 */
	public function survey_edit_subject()
	{
		$in = &$this->in;

		$_model = D('vote_subject');
		$subject = $_model->find($in['subjectid']);

		$option_model = M('vote_option');
		$subject['option'] = $option_model->where('subjectid='.$in['subjectid'])->findAll();

		if ($this->ispost()) {
			$in['info']['subjectid'] = $in['subjectid'];
			$in['info']['update_time'] = time();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_model->create ( $in ['info'] ) ) {
				if ($_model->save ()) {  //更新选项值
					//旧选项值
					$old_option = $option_model->where('subjectid='.$in['subjectid'])->getField('optionid,`option`');
					foreach ($in['info']['option'] AS $key=>$value) {
						$option_model->subjectid = $in['subjectid'];
						$option_model->option = $value;
						$option_model->image = $in['info']['image'][$key];
						if (!empty($in['info']['option_ids'][$key])) {
							$option_model->optionid = $in['info']['option_ids'][$key];
							$option_model->save();
							unset($old_option[$in['info']['option_ids'][$key]]);
						} else {
							$option_model->optionid = '';
							$option_model->add();
						}
					}
					if (!empty($old_option)) $option_model->delete(implode(',', array_keys($old_option)));

					$this->message('<font class="green">编辑成功！</font>');
				} else $this->message('<font class="red">编辑失败！</font>', '', 3000);
			} else $this->message('<font class="red">请不要重复提交！</font>');
		}

		$this->assign('subject', $subject);
		$this->display();
	}

	/**
	 * @name重置数据
	 */
	public function reset()
	{
		$in = &$this->in;

		$vote_date_model = M('vote_data');
		$vote_date_model->where('subjectid='.$in['subjectid'])->delete();
		$this->message('<font class="green">重置成功！</font>');
	}
	
	/**
	 * @name数据列表
	 */
	public function manageData() {
		$in = &$this->in;
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
}
?>