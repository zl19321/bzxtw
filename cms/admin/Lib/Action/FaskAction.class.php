<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fask.class.php
// +----------------------------------------------------------------------
// | Date: 2010-10-26
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 辅助插件 - 问答模块
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 辅助插件 - 问答模块
 *
 */
class FaskAction extends FbaseAction {
	protected $_category = '';
	protected $category_data = '';
	/**
	 * 初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'ask';
		
		if ($in['catid']) {
			$this->_category = D ('Category');
			$this->category_data = $this->_category->find((int)$in['catid']);
			$this->assign('cat',$this->category_data);
			$this->checkPermissions($_SESSION['userdata'],$this->category_data['permissions']['admin']);
			
			//同类型分类下拉列表json
			$categorys = $this->_category->getSameCategorys($this->category_data['catid']);
			$arr = array();
			if (is_array($categorys)) {
				foreach ($categorys as $k=>$v) {
					$arr[$v['catid']] = $v['name'];
					$categorys[$k]['id'] = $v['catid'];
				}
				$this->assign('cat_json',json_encode($arr));
				//树形栏目下拉框
				import('Tree',INCLUDE_PATH);
				$_tree = get_instance_of('Tree');
				$_tree->init ( $categorys );
				$str = "<option value='\$catid'>\$spacer\$name</option>";
				$this->assign('category_tree',$_tree->get_tree ( 0, $str ));
			}
		} else $this->message('<font class="red">没有选择要操作的栏目</font>');
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
	 * @name分类管理
	 */
	public function category_manage()
	{
		$in = &$this->in;
		$_model = M('ask_category');
		$msg = '';
		$parent_id = 0;
		
		//生成树
		import ( 'Tree', INCLUDE_PATH );
		$tree = get_instance_of ( 'Tree' );
		$categorys = $_model->field("`ask_category_id` AS `id`,`name`,`parentid`,`catid`,`sort`,`status`,`create_time`")->order('`sort` ASC')->findAll();
		foreach ($categorys AS &$c) {
			$c['create_time'] = date('Y-m-d H:i', $c['create_time']);
			$c['access'] = '<a href="'."{$this->category_data['url']}index?ask_category_id={$c['id']}".'">访问</a>';
			$c['edit'] = '<a href="'.U("fask/category_manage?act=edit&catid={$this->category_data['catid']}&ask_category_id={$c['id']}").'">编辑</a>';
			$c['delete'] = '<a href="'.U("fask/category_manage?act=delete&catid={$this->category_data['catid']}&ask_category_id={$c['id']}").'">删除</a>';
			if ($in['ask_category_id'] && $in['ask_category_id'] == $c['id']) {
				$parent_id = $c['parentid'];
			}
		}
		$tree->init ( $categorys );
		$str = "<option value='\$id' \$selected>\$spacer\$name</option>\n";
		$categorys_option = $tree->get_tree ( 0, $str, $parent_id);
		$this->assign ( 'categorys_option',$categorys_option );	//已有分类
		
		$str = "<tr>
				  <td>\$id</td>
				  <td>\$spacer\$name</td>
				  <td>\$sort&nbsp;</td>
				  <td>\$create_time&nbsp;</td>
				  <td>\$access | \$edit | \$delete</td>
				</tr>";
		$tree->ret = '';
		
		$html = $tree->get_tree ( 0, $str );
		$this->assign('html', $html);
		
		switch ($in['act']) {
			case 'add':
				$in['info']['create_time'] = $in['info']['update_time'] = strtotime($in['info']['create_time']);
				if ($_model->add($in['info'])) $this->message('<font class="green">添加成功！</font>');
				else $this->message('<font class="red">添加失败！</font>');
				break;
			case 'edit':
				$this->category_edit();
				exit;
				break;
			case 'delete':
				$ask_model = M('ask');
				$_model->delete($in['ask_category_id']); //删除分类
				$ask_model->where('ask_category_id='. $in['ask_category_id'])->delete(); //删除分类下的内容
				
				//删除子分类及内容
				$where = array();
				$where['parentid'] = array('IN', $in['ask_category_id']);
				while ($delete_data = $_model->where($where)->findAll()) {
					$parent_ids = array();
					if (empty($delete_data)) break;
					foreach ($delete_data AS $d) {
						$_model->delete($d['ask_category_id']);
						$ask_model->where('ask_category_id='. $d['ask_category_id'])->delete();
						$parent_ids[] = $d['ask_category_id'];
					}
					$where['parentid'] = implode(',', $parent_ids);
				}
				
				redirect(U('fask/category_manage?catid=' . $this->category_data['catid']));
				break;
		}
		$this->assign('msg', $msg);
		$this->display();
	}
	
	/**
	 * @name编辑栏目
	 */
	protected function category_edit()
	{
		$in = &$this->in;
		
		$_model = M('ask_category');
		if ($this->ispost()) {
			$in['info']['update_time'] = time();
			$in['info']['create_time'] = strtotime($in['info']['create_time']);
			$_model->save($in['info']);
			$this->message('<font class="green">编辑成功！</font>', $this->forward, 3);
		}
		$data = $_model->find($in['ask_category_id']);
		$this->assign('data', $data);
		$this->display('category_edit');
	}
	
	/**
	 * @name列表
	 */
	public function manage() {
		$in = &$this->in;
		
		$where = array();
		$where[] = 'parentid=0';
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`=' . $in['status'];
		}
		if (!empty($in['q']) && $in['q'] != '请输入关键字') {
			$where[] = ' `' . $in['field'] . '` LIKE "%' . $in['q'] . '%"';
		}
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		$in['order'] = '`ask_id` DESC';
		
		$this->assign('status', array('0' => '隐藏', '1' => '显示'));
		$this->assign('q_fields', array('title' => '标题', 'username' => '提问人'));
		$this->assign('in', $in);

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
		$category = $this->category_data;
		
		$_model = D('ask');
		
		if ($in['act'] == 'delAnswer') {
			if ($_model->delete($in['ask_id'])) {
				$_model->updateAnswerNum($in['parentid']);
				die(json_encode('ok'));
			} else die(json_encode('error'));
		}
		if ($this->ispost()) {
			$in['info']['create_time'] = strtotime($in['info']['create_time']);
			$in['info']['update_time'] = time();
			if (!$in['info']['good_answer']) {
				$in['info']['good_answer'] = 0;
			}
			if ($_model->save($in['info'])) {
				$this->message('<font class="green">编辑成功！</font>');
			} else $this->message('<font class="green">编辑失败！</font>');
		}
		$data = $_model->find($in['ask_id']);
		
		//同类型分类下拉列表json
		import ( 'Tree', INCLUDE_PATH );
		$tree = get_instance_of ( 'Tree' );
		$ask_category_model = M('ask_category');
		$ask_category_where = array();
		$ask_category_where['status'] = 1;
		$ask_category_where['catid'] = $category['catid'];
		$ask_categorys = $ask_category_model->where($ask_category_where)->field("`ask_category_id` AS `id`,`name`,`parentid`")->order('`sort` ASC')->findAll();
		$tree->init ( $ask_categorys );
		$str = "<option value='\$id' \$selected>\$spacer\$name</option>\n";
		$ask_categorys_option = $tree->get_tree ( 0, $str, $data['ask_category_id']);
		$this->assign ( 'ask_categorys_option',$ask_categorys_option );	//已有分类
		
		//回答列表
		$data['answer_list'] = $_model->where('parentid='.$data['ask_id'])->findAll();
		$this->assign('data', $data);
		$this->display();
	}
	
	/**
	 * @name删除
	 */
	public function delete()
	{
		$in = &$this->in;
		
		$_model = M('ask');
		if (is_array($in['info']['id'])) {
			$ids = implode(',', $in['info']['id']);
		} else $ids = intval($in['info']['id']);
		
		$_model->delete($ids);
		
		$_model->where('parentid IN (' . $ids . ')')->delete();  //删除回答
		redirect($this->forward);
	}
}