<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fsurvey.class.php
// +----------------------------------------------------------------------
// | Date: 2010-07-26
// +----------------------------------------------------------------------
// | Author: fangfa <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 模块 问卷调查
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 问卷调查模块
 *
 */
class FsurveyAction extends FbaseAction
{

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
	 * @name 问卷调查
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'survey';

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
	 * @name问卷列表
	 */
	public function manage()
	{
		$in = &$this->in;
		parent::manage();	
	}

	/**
	 * @name添加问卷
	 *
	 */
	public function add()
	{
		$in =&$this->in;

		if ($this->ispost()) {
			$_model = M('Survey');
			$in['_tablename'] = 'survey';
			$in['info']['create_time'] = $in['info']['update_time'] = time();
			$in['info']['userid'] = $_SESSION['userdata']['user_id'];
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['catid'] = $in['catid'];

			$in['info']['allowedgroup'] = serialize($in['info']['groupidsvote']);
			$in['info']['showgroup'] = serialize($in['info']['groupidsview']);
			

			$surid = $_model->add($in['info']);
			if(is_numeric($surid))
			{
				$this->message('<font class="green">添加成功！</font>');
			} else
			{
				$this->message('<font class="red">添加失败！</font>');
			}

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
	public function edit()
	{
		$in = &$this->in;

		if ($in['ajax']) {
			$this->_ajax_edit();
			exit;
		}
		$_model = M('Survey');

		if ($this->ispost() && $in['surid']) {
			$in['info']['allowedgroup'] = serialize($in['info']['allowedgroup']);
			$in['info']['showgroup'] = serialize($in['info']['showgroup']);
			$in['info']['surid'] = $in['surid'];
			$in['info']['update_time'] = time();
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];

			print_r($in['info']);

			if ( $_model->where('surid=' . $in['surid'])->save ($in['info']) ) {

				$this->message('<font class="green">编辑成功！</font>');

			} else
			{
				$this->message('<font class="red">编辑失败！</font>', '', 3000);
			}
			
		}
		$data = $_model->find($in['surid']);
		$data['allowedgroup'] = unserialize($data['allowedgroup']);
		$data['showgroup'] = unserialize($data['showgroup']);

		$this->assign('editer_html', Html::editor('info[description]', $data['description'], C('EDITOR_TYPE') , array('toolbar' => 'basic', 'width' => 500, 'height' => 250)));
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
	 * @name管理问题
	 */
	public function manage_question()
	{
		$in = &$this->in;
		$in['tpl'] = 'manage_question.html';
		$in['_tablename'] = 'survey_option';
		$this->assign("surid",$in['surid']);
		parent::manage();		
	}

	/**
	 * @name编辑问题
	 */
	public function edit_question()
	{
		$in = &$this->in;
	}

	/**
	 * @name添加问题
	 */
	public function add_question()
	{
		$in = &$this->in;
		$this->assign("surid",$in['surid']);
		$in['tpl'] = 'add_question.html';
		$in['_tablename'] = 'survey_option';
		$_option = D ('survey_option');
		$_keyid = $_option->getPk ();
		$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
		$in['info']['create_time'] = $in['info']['update_time'] = time();

		switch($in['info']['type'])
		{
			case '0':
				foreach($in['info']['option'] as $k=>$v)
				{
					$arr[]=array('text'=>$v,'img'=>$in['info']['option'][$k]);
				}
				$in['info']['options'] = serialize($arr);
				$in['info']['option_one'] = $in['option']['minval'];
				$in['info']['option_two'] = $in['option']['maxval'];
				$in['info']['option_three'] = $in['option']['fujia'];
				break;
			case '1':
				$in['info']['options'] = serialize($arr);
				$in['info']['option_one'] = $in['option']['minval'];
				$in['info']['option_two'] = $in['option']['maxval'];
				$in['info']['option_three'] = $in['option']['fujia'];		
				break;
			case '2':
				$in['info']['options'] = serialize($array());
				$in['info']['option_one'] = $in['option']['rows'];
				$in['info']['option_two'] = $in['option']['cols'];
				$in['info']['option_three'] = $in['option']['fujia'];		
				break;
		}
		if ( $_option->create ( $in ['info'] ) ) 
		{
			if ($_option->add ()) 
			{  
				$this->message('<font class="green">添加成功！</font>');
			} else
			{
				$this->message('<font class="red">添加失败！</font>');
			}
		} else $this->message('<font class="red">请不要重复提交！</font>');
	}

	/**
	 * @name删除问题
	 */
	public function delete_question()
	{
		$in = $this->in;
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
	 * @name删除问卷
	 */
	public function delete()
	{
		$in = &$this->in;

		$_model = D('Survey');

		if (is_array($in['surid'])) {
			$ids = implode(',', $in['surid']);
		} elseif (is_numeric($in['surid'])) {
			$ids = $in['surid'];
		}
		$_model->delete($ids);

		//删除选项
		//$option_model = M('vote_option');
		//$option_model->where('subjectid IN('.$ids.')')->delete();

		redirect($this->forward);
	}

	/**
	 * @name AJAX请求
	 */
	protected function _ajax_edit()
	{
		$in = &$this->in;

		$_model = M ('Survey');
		$data = array();

		switch ($in['ajax']) {
			case 'status':
				$data['status'] = $in['status'];
				$_model->where('surid=' . $in['surid'])->save($data);
				break;
		}

		redirect($this->forward);
	}

	/**
	 * @name重置数据
	 */
	public function reset()
	{
		$in = &$this->in;

		$vote_date_model = M('Survey');
		$vote_date_model->where('surid='.$in['surid'])->delete();
		$this->message('<font class="green">重置成功！</font>');
	}
}