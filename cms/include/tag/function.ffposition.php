<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffposition.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 当前位置标签
// +----------------------------------------------------------------------


/**
 * smarty模板对象
 * @var
 *
 */
function smarty_function_ffposition($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );

	$catid = isset ( $catid ) ? $catid : $smarty->_tpl_vars ['cat'] ['catid'];
	if (! $catid && defined ( CATID ))
		$catid = CATID;
	if (empty ( $catid ))
		return "缺少必需参数catid";

	$category_data = F ( 'category_' . $catid );

	$pre_tag = empty ( $tag ) ? '' : trim ( $tag );
	if (preg_match ( '/^<[a-zA-Z]*>$/', $pre_tag )) {
		$ext_tag = '</' . substr ( $pre_tag, 1 );
	} elseif (strpos ( $pre_tag, ' ' ) !== false) {
		$ext_tag = '</' . substr ( $pre_tag, 1, strpos ( $pre_tag, ' ' ) ) . '>';
	} else
		$ext_tag = '&nbsp;&gt;&nbsp;';

	$separtor = isset ( $separtor ) ? $separtor : '';
	$a_class = isset ( $a_class ) ? $a_class : '';
	//$data = array (0 => "{$pre_tag}<a href='" . __ROOT__ . "/' class='{$a_class}'>首页</a>{$ext_tag}" );
	if (! empty ( $category_data ['parentidarr_self'] )) {
		$count = count($category_data ['parentidarr_self']);
		foreach ( $category_data ['parentidarr_self'] as $k=>$p ) {
			if($count-1<=0)$ext_tag='';
			if($k > 0) {
			$c_category = F ( 'category_' . $p );
			}
			
			if (! empty ( $c_category )) {
			     //2012-10-08 新功能 给最后一个加标签
                 if(!empty($label)){
    			     if($count-1<=0){
                         $data [] = "<{$label}>{$c_category['name']}</{$label}>{$ext_tag}";
    			     }else{
    			         $data [] = "{$pre_tag}<a href='{$c_category['url']}' class='{$a_class}'>{$c_category['name']}</a>{$ext_tag}";
    			     }
                 }else{
                    
                     $data [] = "{$pre_tag}<a href='{$c_category['url']}' class='{$a_class}'>{$c_category['name']}</a>{$ext_tag}";
                    
                 }

            }
			$count--;
		}
	}

	return implode ( $separtor, $data );
}