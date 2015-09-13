<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffmagazine.php
// +----------------------------------------------------------------------
// | Date: 2011-9-7
// +----------------------------------------------------------------------
// | Author: Mark  
// +----------------------------------------------------------------------
// | 文件描述: 电子报刊
// +----------------------------------------------------------------------

function smarty_function_ffmagazine($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if (empty ( $data )) {
		$p = intval($p ? $p : $smarty->_tpl_vars['p']);
		$p = ($p ? $p : 1);
		
		//$default_thumb = (empty($default_thumb) ? '' : __ROOT__ . '/public/statics/'.$default_thumb);
		if (!isset($catid) && defined(CATID) ) {
			$catid = CATID;
		}
		//初始化条件
		$catid = intval(isset($catid) ? $catid : $smarty->_tpl_vars['cat']['catid']);
		$data = array();
		if (! isset($catid)) {
			$html .= '缺少catid<br />';
		} else {
			//$parentid 父级杂志编号，parent_child 是否获取自己杂志信息 1=是， $result 存储结果
			$data = getMagazine($params, $smarty);
			//print_r($data);
			if (! empty ( $data )) {
				if (!empty($tpl_id)) $smarty->_tpl_vars['__' . $tpl_id] = $data;

				if ($firstpage && $smarty->_tpl_vars['p']>1) {
					$html = '';
				} elseif (!empty($to)) {
					$smarty->_tpl_vars[$to] = $data;
					$html = '';
				} else {
					$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
					if (!$data['info']) unset($data['info']);
					$smarty->assign ( 'data', $data );
					$html = $smarty->fetch (  !empty($tpl) ? $tpl : 'system/pagelist/default.html' );
					$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
					unset($_tpl_vars);
				}
			}
		}
	}
	return $html;
}
function getMagazine(&$params, &$smarty){
	@extract ( $params );
	$data = array();
	//初始化条件
	$catid = intval(isset($catid) ? $catid : $smarty->_tpl_vars['cat']['catid']);
	$pagesize = intval($pagesize ? $pagesize : $smarty->_tpl_vars['pagesize']);
	$category_data = F ('category_'.$catid);
	$p = intval($p ? $p : $smarty->_tpl_vars['p']);
	//$model_data = F ('model_'.$category_data['modelid']);
	$pageurl = rtrim($category_data['url'],'/') . '/index_{page}.html';
	//默认不获取下级封面
	$m_child = empty($m_child)?  0 : 1;
	//默认不获取杂志文章
	$article = empty($article)?  0 : 1;
	
	if (empty($m_order)) {
		$order= "sort asc";
	}else {
		$order= $m_order;
	}
	$parentid = intval(isset($parentid) ? $parentid : $smarty->_tpl_vars['parentid']) ;
	
	$_magazine = D("Magazine");
	$_c = D ( 'Contentext','front' );
	$data ['count'] = $_magazine ->where("parentid=".$parentid)->count();
	$data ['info'] = $_magazine ->where("parentid=".$parentid)->order($order)->select();
	
	$data ['pages'] = multi($data['count'],$p,$pageurl,$pagesize,isset($goto) ? (boolean)$goto : false); //分页html
			
	foreach ($data ['info'] as $k => $t){
		$data['info'][$k]['images'] = __ROOT__."/".C("UPLOAD_DIR").$t['images'];
		if ($t['parentid'] == 0) {
			$data['info'][$k]['url'] = __ROOT__."/".$smarty->_tpl_vars['cat']['catdir'] ."/t/".$t['id'];
		}else {
			$data['info'][$k]['url'] = __ROOT__."/".$smarty->_tpl_vars['cat']['catdir'] ."/v/".$t['id'];
		}
		$params['m_parentid'] = $t['id'];
		$count = $_magazine ->where("parentid=".$t['id'])->count();
		if ($m_child && $count != 0) {
			$data['info'][$k]['child'] = getMagazine($params, $smarty);
		}
		if ($article) {
			$data['info'][$k]['article'] = $_c -> where("catid=".$catid." and status=9 and cid in (".$t['content_id'].")")->findAll();
			foreach ($data['info'][$k]['article'] as $z=>$v) {
				if ($tlength) $data['info'][$k]['article'][$z]['title'] = msubstr($v['title'],0,$tlength, $charset="utf-8");
				if ($dlength) $data['info'][$k]['article'][$z]['description'] = msubstr($v['description'],0,$dlength);
				if (empty($v['thumb'])) $data['info'][$k]['article'][$z]['thumb'] = $default_thumb; //默认缩略图
				$current_category_data = F ('category_'.$v['catid']);
				$data['info'][$k]['article'][$z]['category_name'] = $current_category_data['name'];
			}
		}
		
		
	}
	return $data;
}