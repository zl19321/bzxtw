<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffgetone.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 获取单条内容 (单页或者单条内容信息)
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffgetpage($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	if (empty($type)) return 'type参数是必须！';
	if (empty($id)) return 'id参数是必须！';
	if (empty($to)) return 'to参数是必须！';
	
	$_m = D ( parse_name( $type, 1 ) ); //实例化表模型类
	$_keyid = $_m->getPk ();
	switch ($type) {
		case 'category':
			$category_data = F('category_' . $id);
			if ($category_data['type'] == 'page') {
				$page_model = M('page');
				$page_ext = $page_model->where('catid='.$category_data['catid'])->find();
				$data = array_merge($page_ext, $category_data);
			} else $data = $category_data;
			break;
		case 'content':
			$data = $_m->find($id);
			$category_data = F('category_'.$data['catid']);
			$model_data = F('model_'.$category_data['modelid']);
			if (!empty($model_data['tablename'])) { //扩展表
				$content_model = M(parse_name($model_data['tablename'],1));
				$data_ext = $content_model->find($id);
			} else $data_ext = array();
			
			$data = array_merge($data, $data_ext);
			break;
		default:
			$data = $_m->find($id);
	}
	$smarty->_tpl_vars[$to] = $data;
	return '';
}