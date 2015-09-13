<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffask.php
// +----------------------------------------------------------------------
// | Date: 2010-10-26
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 问答标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffask($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	////根据tpl参数获取要执行的操作
	if (!isset($act)) {
		if (strpos(array_pop(explode('/',$tpl)), 'form')) $act = 'form';
		else if (strpos(array_pop(explode('/',$tpl), 'category'))) $act = 'category';
		else $act = 'list';
	}
	
	//获取栏目信息
	if ($catid) $cat = F('category_'.$catid);
	elseif (isset($smarty->_tpl_vars['cat']['controller']) 
			&& $smarty->_tpl_vars['cat']['controller'] == 'fask') {
			$cat = $smarty->_tpl_vars['cat'];
	} else return 'catid 参数必须设置';

	$ask_model = M('ask');
	$ask_category_model = M('ask_category');
	$where = array();
	switch ($act) {
		case 'form':  //提交问题
			if (empty($tpl)) $tpl = "system/ask/default_form.html";
			$smarty->assign('actionto', $cat['url'].'add');  //表单提交地址
			
			//同类型分类下拉列表json
			import ( 'Tree', INCLUDE_PATH );
			$tree = get_instance_of ( 'Tree' );
			$ask_category_model = M('ask_category');
			$ask_category_where = array();
			$ask_category_where['status'] = 1;
			$ask_category_where['catid'] = $cat['catid'];
			$ask_categorys = $ask_category_model->where($ask_category_where)->field("`ask_category_id` AS `id`,`name`,`parentid`")->order('`sort` ASC')->findAll();
			$tree->init ( $ask_categorys );
			$str = "<option value='\$id' \$selected>\$spacer\$name</option>\n";
			$ask_categorys_option = $tree->get_tree ( 0, $str, 0);
			$smarty->assign ( 'ask_categorys_option',$ask_categorys_option );	//已有分类
			$data = true;
			break;
		case 'list':  //问答列表
			if (empty($tpl)) $tpl = "system/ask/default_list.html";
			
			//获取该栏目下所有栏目
			$ask_categorys = $ask_category_model->where('catid='. $cat['catid'])->getField('ask_category_id,name');
			
			$where['status'] = 1;
			$where['parentid'] = 0;
			$where['catid'] = $cat['catid'];
			if (isset($ask_category_id)) $where['ask_category_id'] = array('IN', $ask_category_id);
			
			//分页
			$p = ($smarty->_tpl_vars['p'] ? $smarty->_tpl_vars['p'] : 1);
			$pagesize = intval($pagesize ? $pagesize : 12);
			$count = $ask_model->where($where)->count();
			$pageurl = $cat['url'] . 'index_{page}.html';
			if (isset($ask_category_id)) $pageurl .= '?ask_category_id='.$ask_category_id;
			$data['pages'] =  multi($count, $p, $pageurl, $pagesize, isset($goto) ? (boolean)$goto : false); // 分页显示输出
			
			$data['info'] = $ask_model->where($where)->order('ask_id DESC')->page($p . ',' . $pagesize)->findAll();
			
			foreach($data['info'] AS &$d) {
				$d['category_name'] = $ask_categorys[$d['ask_category_id']];
				$d['url'] = $cat['url'].'show?ask_id='.$d['ask_id'];
				$d['category_url'] = $cat['url'].'index?ask_category_id='.$d['ask_category_id'];
			}
			
			break;
		case 'category':  //问答分类
			if (empty($tpl)) $tpl = "system/ask/default_category.html";
			$where['status'] = 1;
			if (isset($parentid)) $where['parentid'] = $parentid;
			$where['catid'] = $cat['catid'];
			$ask_category_data = $ask_category_model->where($where)->order('`sort` DESC')->findAll();
			foreach ($ask_category_data AS &$a) {
				$a['url'] = $cat['url'] . 'index?ask_category_id='.$a['ask_category_id'];
			}
			$data = array_to_tree($ask_category_data,'ask_category_id','parentid', 'child', false, ($parentid ? $parentid : 0));
			break;
	}
	
	if (! empty ( $data )) {
		if (!empty($to)) {  //不适用其他模板输出，程序上直接循环输出
			$smarty->_tpl_vars[$to] = $data;
			$html = '';
		} else { //使用其他模板输出
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			$smarty->assign('cat', $cat);
			$smarty->assign('data', $data);
			$html = $smarty->fetch ($tpl);
			$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
			unset($_tpl_vars);
		}
	}
	return $html;
}