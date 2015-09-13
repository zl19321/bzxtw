<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.fftag.php
// +----------------------------------------------------------------------
// | Date: 2010-9-18
// +----------------------------------------------------------------------
// | Author: fangfa
// +----------------------------------------------------------------------
// | 文件描述: ffscate（网络营销）标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffsalenet($params, &$smarty) {
	@extract($params);
	$html = '';
	$in = array_merge($_GET, $_POST);

	if (!$tpl) $tpl  = 'system/salenet/default_list.html';
	//获取栏目信息
	if ($catid) $cat = F('category_'.$catid);
	elseif (isset($smarty->_tpl_vars['cat']['controller']) 
			&& $smarty->_tpl_vars['cat']['controller'] == 'fsalenet') {
		$cat = $smarty->_tpl_vars['cat'];
	} else return 'catid 参数必须设置';
	
	switch ($act) {
		case 'show':  //营销区域详情
			$in['sid'] = intval($in['sid']);
			if (!$in['sid']) return '缺少sid，请用get或post方式传送！';
			
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			if (!isset($smarty->_tpl_vars['salenet'])) {
				$smarty->assign('salenet', D('Salenet', 'admin')->find($in['sid']));
			}
			$smarty->assign('catid', $cat['catid']);
			$html = $smarty->fetch ( !empty($tpl) ? $tpl : 'system/job/default_show.html' );
			$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
			unset($_tpl_vars);
			break;
		case 'category':  //返回营销的省份
			$_model = D('Scategory', 'admin');

			$field = empty($field) ? '*' : $field;

			$where = array();
			if (!empty($ext_sql)) $where['_string'] = $ext_sql;
			if (!isset($order)) $order = 'sort ASC, scategoryid DESC';

			$data['info'] = $_model->field($field)->where($where)->order($order)->select();			

			if (!empty($to)) {
				$smarty->_tpl_vars[$to] = $data;
			} else {
				$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
				$smarty->assign ( 'data', $data );
				$html = $smarty->fetch (  !empty($tpl) ? $tpl : 'system/job/default_list.html' );
				$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
				unset($_tpl_vars);
			}			
			break;
		case 'list': //返回营销区域列表
			$_model = D('Salenet', 'admin');
			$field = empty($field) ? '*' : $field;
			
			if (!$smarty->_tpl_vars['p']) $p = 1;
			else $p = $smarty->_tpl_vars['p'];
			
			$pagesize = intval($pagesize ? $pagesize : 10);
			$where = array();
			$where['status'] = 1;
			$where['catid'] = $cat['catid'];
			if (!empty($ext_sql)) $where['_string'] = $ext_sql;
			
			if (!isset($order)) $order = 'sort DESC, sid DESC';
			
			$data['info'] = $_model->field($field)->where($where)->order($order)->page($p . ',' . $pagesize)->select();
			if (isset($smarty->_tpl_vars['cat']['catid'])
				&& $cat['catid'] == $smarty->_tpl_vars['cat']['catid']) {
					$count = $_model->where($where)->count(); // 查询满足要求的总记录数
					$pageurl = $cat['url'] . 'index_{page}.html';
					$data['pages'] =  multi($count, $p, $pageurl, $pagesize, isset($goto) ? (boolean)$goto : false); // 分页显示输出
			}
			
			if (! empty ( $data )) {
				if (!empty($to)) {
					$smarty->_tpl_vars[$to] = $data;
				} else {
					$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
					$smarty->assign ( 'data', $data );
					$html = $smarty->fetch (  !empty($tpl) ? $tpl : 'system/job/default_list.html' );
					$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
					unset($_tpl_vars);
				}
			}
			break;
	}	
	return $html;
}