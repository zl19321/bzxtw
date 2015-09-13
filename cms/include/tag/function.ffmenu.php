<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffmenu.php
// +----------------------------------------------------------------------
// | Date: 2010 09:42:06
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 菜单标签（获取各类菜单）
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffmenu ($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	$types = array('member');  //  会员中心
	$data = array();
	$roles = $roles ? explode(',', $roles) : $_SESSION['fuserdata']['roles']; //如果没有指定角色标识，则使用当前角色的标识
	$type = $type ? $type : 'member';	 //默认获取会员中心菜单
	switch ($type) {
		case 'member':
			$_menu = D ('Menuext','front');
			$data = $_menu->getMenuDataTree(20, $roles);
			
			$html = toHtmlTree($data);
			//TODO 生成HTML树 
			break;
		default:
			break;
	}

	return $html;
}

/**
 * 递归生成HTML代码
 * 
 * @param array $data
 * @param array $sep  第一个元素为大容器的html element 第二个为每个子元素的 html element  第三个为 菜单文字容器的html element
 */
function toHtmlTree($data, $sep = array('ul','li','span')) {
	$html = '';
	$html .= "<{$sep[0]}>\n";
	foreach ($data as $v) {
		$html .= "<{$sep[1]}>";
		if ($v['url'] == '#') {
			$html .= "<{$sep[2]}>".$v['name']."</span>";	
		} else {
			$html .= "<{$sep[2]}><a href=\"__ROOT__/{$v['url']}\">{$v['name']}</a></{$sep[0]}>";
		}
		if (is_array($v['child']) && !empty($v['child'])) {			
			$html .= toHtmlTree($v['child']);
		}
		$html .= "</{$sep[1]}>\n";					
	}
	$html .= "</{$sep[0]}>\n";
	return $html;
}