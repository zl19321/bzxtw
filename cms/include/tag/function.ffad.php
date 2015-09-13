<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffad.php
// +----------------------------------------------------------------------
// | Date: 2010 09:42:06
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 广告标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffad($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if (!$aid) return '未指定广告ID';
	$data = F ('ad_'.$aid,'',ALL_CACHE_PATH . 'ads/');	
	//生成代码
	if (!empty($data)) {
		$nowtime = date('Y-m-d H:i:s');
		if ($data['starttime']<$nowtime && $data['endtime']>$nowtime) {  //限制时间内，生成广告代码
			import('Ad',INCLUDE_PATH);
			$ad = get_instance_of('Ad');
			$html = $ad->get($data['type'],$data['setting']);
		}
		if (!empty($to)) {
			$smarty->_tpl_vars[$to] = $data;
			$html = '';
		}
	}
	
	return $html;
}