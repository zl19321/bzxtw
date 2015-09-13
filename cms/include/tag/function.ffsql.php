<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffsql.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 直接使用sql进行数据调用
// +----------------------------------------------------------------------


function smarty_function_ffsql($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if (empty ( $data )) {
		$_model = M();
		$sql = str_replace ( '%DB_PREFIX%', C ( 'DB_PREFIX' ), $sql );
		$data = $_model->query ( $sql );
		if (! empty ( $data )) {
			if (! empty ( $to )) { //不适用其他模板输出，程序上直接循环输出
				$smarty->_tpl_vars [$to] = $data;
				$html = '';
			} else { //使用其他模板输出
				$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
				$smarty->assign ( 'data', $data );
				! empty ( $tpl ) && $html = $smarty->fetch ( $tpl );
				$smarty->_tpl_vars = $_tpl_vars; //恢复之前的模板变量
				unset ( $_tpl_vars );
			}
		}
	}
	return $html;
}