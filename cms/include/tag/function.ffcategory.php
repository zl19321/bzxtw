<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.fftag.php
// +----------------------------------------------------------------------
// | Date: 2010-7-11
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 自定义的category标签
// +----------------------------------------------------------------------

function smarty_function_ffcategory($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	
	$default_thumb = (empty($default_thumb) ? '' : __ROOT__ . '/public/images/'.$default_thumb);
	//栏目ID
	if (!$catid && is_numeric($parentid)) $catid = $parentid;
	if (!isset($catid)) {
		if (defined(CATID)) $catid = CATID;
		elseif ($smarty->_tpl_vars['cat']) {
    		$cat = $smarty->_tpl_vars['cat'];
    		$catid = $cat['catid'];
    	}
	}
	$_category = D ('Category','admin');
	$_category->auto_format = true;
	if (is_numeric($catid)) {
	    $catid = intval($catid);
	    $data = $_category->getTreeByParentId($catid);
	 
	    if (isset($data['catid'])) {
	    	$data['thumb'] = ($data['thumb'] ? __ROOT__ . '/public/uploads/' . $data['thumb'] : $default_thumb);
	    	map_category($data, $default_thumb);
	    } else {
	    	foreach ($data AS &$d) {
	    		$d['thumb'] = ($d['thumb'] ? __ROOT__ . '/public/uploads/' . $d['thumb'] : $default_thumb);
	    		map_category($d, $default_thumb);
	    	}
	    }
	    
	} else {  //指定栏目
	    $where = array(
	       'catid' => array('IN',$catid),
	    );
	    $data = array();
	    $temp = $_category->field("`catid`")->order('`sort` ASC')->where($where)->findAll();
	    if (is_array($temp)) {
	        foreach ($temp as $c) {
	        	$c_category = F ('category_'.$c['catid']);
	        	$c_category['thumb'] = ($c_category['thumb'] ? __ROOT__ . '/public/uploads/' . $c_category['thumb'] : $default_thumb);
	            $data[] = $c_category;
	        }
	    }
	}
	//print_r($data);
	if (!empty($data)) {
	    //格式化数据  TODO
		if (!empty($to)) {  //不适用其他模板输出，程序上直接循环输出
			$smarty->_tpl_vars[$to] = $data;
		} else { //使用其他模板输出
			$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
			$smarty->assign('selected',$selected ? $selected : 'selected');  //选中状态的css
			$smarty->assign('data',$data);
			$html = $smarty->fetch ( !empty($tpl) ? $tpl : 'system/category/default.html' );
			$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
			unset($_tpl_vars);
		}
	}
	return $html;
}

/**
 * 递归获取指定栏目的所有子栏目
 * @param unknown_type $data
 */
function map_category(&$data, $default_thumb='')
{
	foreach ($data['child'] AS &$d) {
		$d['thumb'] = ($d['thumb'] ? __ROOT__ . '/public/uploads/' . $d['thumb'] : $default_thumb);
		if (isset($d['child']) && is_array($d['child'])) {
			map_category($d);
		}
	}
}