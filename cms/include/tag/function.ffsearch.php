<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffsearch.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: 王超 <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 网站搜索标签
// +----------------------------------------------------------------------


/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffsearch($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	//获取搜索引擎
	if (isset ( $engine )) {
		$engine = array_map ( 'trim', explode ( ',', $engine ) );
		$default_engine = array ('站内搜索' => 'site', 'google搜索' => 'google', '百度搜索' => 'baidu', '雅虎搜索' => 'yahoo' );
		$engine = array_intersect ( $default_engine, $engine );
		$data ['engine'] = array_flip ( $engine );
	}
	
	//是否搜索指定栏目
	if ($categories) {
		$data['catid'] = $categories;
		//$Category = D ( 'Category', 'admin' );
		//$data ['category_list'] = $Category->where ( 'catid IN(' . $categories . ')' )->getField ( 'catid,name' );
	}
	
	$data ['travel'] = ($travel ? true : false);
	
	$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
	$smarty->assign ( 'data', $data );
	$html = $smarty->fetch ( ! empty ( $tpl ) ? $tpl : 'system/search/default.html' );
	$smarty->_tpl_vars = $_tpl_vars; //恢复之前的模板变量
	unset ( $_tpl_vars );
	return $html;
}