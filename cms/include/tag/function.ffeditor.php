<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffeditor.php
// +----------------------------------------------------------------------
// | Date: 2011.1.17 15:15:06
// +----------------------------------------------------------------------
// | Author: 孙斌 <sunyichi@163.com>
// +----------------------------------------------------------------------
// | 文件描述: 编辑器标签
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffeditor($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	if(empty($name)) return '未指定文本域的name属性';
	if(empty($value)) $value = '';
	if(empty($width)) $width = 600;
	if(empty($height)) $height = 200;
	if(empty($toolbar)) $toolbar = 'basic';
	if(empty($extra)) $extra = '';
	if(empty($type)) $type = C('EDITOR_TYPE');
	$option = array(
		'width' => $width,
		'height' => $height,
		'toolbar' => $toolbar
	);
	$html = Html::editor($name, $value, $type, $option, $extra);
	return $html;
}
?>