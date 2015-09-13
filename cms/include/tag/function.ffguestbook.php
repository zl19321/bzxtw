<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.fftag.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: ffguestbook（留言板）标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffguestbook($params, &$smarty) {
	@extract($params);
	$html = '';
	if (!$tpl) $tpl  = 'system/guestbook/default_list.html';
	
	////根据tpl参数获取要执行的操作
	if (strpos(array_pop(explode('/',$tpl)), 'form')) $act = 'form';
	else $act = 'list';
	
	//获取栏目信息
	if ($catid) $cat = F('category_'.$catid);
	elseif (isset($smarty->_tpl_vars['cat']['controller']) 
			&& $smarty->_tpl_vars['cat']['controller'] == 'fguestbook') {
		$cat = $smarty->_tpl_vars['cat'];
	} else return 'catid 参数必须设置';

	switch ($act) {
		case 'form':  //返回留言板表单	
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			$smarty->assign('catid', $cat['catid']);
			$smarty->assign('fuserdata', (isset($_SESSION['fuserdata'])?$_SESSION['fuserdata']:''));
			$html = $smarty->fetch ( !empty($tpl) ? $tpl : 'system/guestbook/default_form.html' );
			$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
			unset($_tpl_vars);
			break;
		case 'list':  //返回留言板列表，包括分页
			$Guestbook = M('guestbook');
			$field = empty($field) ? '*' : $field;
			
			$p = ($smarty->_tpl_vars['p'] ? $smarty->_tpl_vars['p'] : 1);
			$pagesize = intval($pagesize ? $pagesize : 12);
			$order = (!empty($order) ? $order : 'id DESC');
			
			$data['info'] = $Guestbook->field($field)->where('status=1 AND catid='.$cat['catid'])->order($order)->page($p . ',' . $pagesize)->select();
			
			//对于带分页的，栏目id必须是当前栏目ID
			if (isset($smarty->_tpl_vars['cat']['catid'])
				&& $cat['catid'] == $smarty->_tpl_vars['cat']['catid']) {
					$count = $Guestbook->where('status=1 AND catid='.$cat['catid'].$where)->count(); // 查询满足要求的总记录数
					$pageurl = $cat['url'] . 'index_{page}.html';
					$data['pages'] =  multi($count, $p, $pageurl, $pagesize, isset($goto) ? (boolean)$goto : false); // 分页显示输出
			}
			
			if (! empty ( $data )) {
				if (!empty($to)) {
					$smarty->_tpl_vars[$to] = $data;
				} else {
					$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
					$smarty->assign ( 'data', $data );
					$html = $smarty->fetch (  !empty($tpl) ? $tpl : 'system/guestbook/default_list.html' );
					$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
					unset($_tpl_vars);
				}
			}
			break;
	}	
	return $html;
}