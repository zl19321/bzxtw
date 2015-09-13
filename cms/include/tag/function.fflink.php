<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.fftag.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 自定义的fflink（友情链接）标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_fflink($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	$order = !empty($order) ? $order : '`sort` ASC';
	$limit = $pagesize ? intval($pagesize) : '';
	
	$_friendlink = M('Friendlink');
	$where = array();
	$where['status'] = 1;
	if (!empty($type_id)) $where['type_id'] = $type_id;
	else if (!empty($type_name)) $where['type_name'] = $type_name;
	
	$data = $_friendlink->field('*')->where ( $where )->order ( $order )->limit ( $limit )->findAll ();
	
	if(is_array($data)) {
		foreach($data AS &$d) {
			$d['created'] = date('Y-m-d H:i', $d['created']);
			if (strpos($d['logo'], 'http') !== 0) {
				$d['logo'] = WEB_PUBLIC_PATH . '/uploads/' . $d['logo'];
			}
		}
	}
	if (! empty ( $data )) {
		if (!empty($to)) {  //不适用其他模板输出，程序上直接循环输出
			$smarty->_tpl_vars[$to] = $data;
			$html = '';
		} else { //使用其他模板输出
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			$smarty->assign('type', $type);
			$smarty->assign('data', $data);
			$html = $smarty->fetch ( !empty($tpl) ? $tpl : 'system/fflink/default.html' );
			$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
			unset($_tpl_vars);
		}				
	}
	return $html;
}