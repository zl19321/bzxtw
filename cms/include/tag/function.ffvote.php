<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffvote.php
// +----------------------------------------------------------------------
// | Date: 2010-10-26
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 投票，问卷标签
// +----------------------------------------------------------------------


/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffvote($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	if (! is_numeric ( $subjectid ))
		return 'subjectid 是必须参数';
	$_model = D ( 'vote_subject' );
	$data = $_model->find ( $subjectid );
	
	$option_model = M ( 'vote_option' );
	//获取选项
	$where = array ();
	if ($data ['ismultiple'] == 1) { //问卷
		$where ['parentid'] = $subjectid;
		$where ['status'] = 1;
		$data ['child'] = $_model->where ( $where )->order ( '`sort` ASC' )->findAll ();
		foreach ( $data ['child'] as &$v ) {
			$v ['option'] = $option_model->where ( 'subjectid=' . $v ['subjectid'] )->findAll ();
			foreach ( $v ['option'] as &$option ) {
				$option ['image'] = __ROOT__ . '/' . 'public/uploads/' . $option ['image'];
			}
		}
	} else { //投票
		$data ['option'] = $option_model->where ( 'subjectid=' . $subjectid )->findAll ();
		foreach ( $data ['option'] as &$option ) {
			$option ['image'] = __ROOT__ . '/' . 'public/uploads/' . $option ['image'];
		}
	}
	
	if (! empty ( $data )) {
		if (! empty ( $to )) { //不适用其他模板输出，程序上直接循环输出
			$smarty->_tpl_vars [$to] = $data;
			$html = '';
		} else { //使用其他模板输出
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			$smarty->assign ( 'type', $type );
			$smarty->assign ( 'data', $data );
			$html = ($data ['ismultiple'] == 1 ? $smarty->fetch ( ! empty ( $tpl ) ? $tpl : 'system/vote/default_survey.html' ) : $smarty->fetch ( ! empty ( $tpl ) ? $tpl : 'system/vote/default_vote.html' ));
			$smarty->_tpl_vars = $_tpl_vars; //恢复之前的模板变量
			unset ( $_tpl_vars );
		}
	}
	return $html;
}