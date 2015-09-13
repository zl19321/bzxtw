<?php

function smarty_function_fftag($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	 
	$_tag = D("Tag");
	$_tc  = D("ContentTag");
	$db_pre = C('DB_PREFIX');

	if (!isset($tag)) { 
		$tag = $_tag ->field($db_pre."tag.name")-> join($db_pre."content_tag  on ".$db_pre."tag.tagid= ".$db_pre."content_tag.tagid")->where(" ".$db_pre."content_tag.keyid= concat('c-',".$cid.")")->findAll();
		foreach ($tag as $t){
			$tag_array[] = $t['name']; 
		}
	}
	else {
		if(!is_utf8($tag))
			$tag = iconv( "gb2312", "UTF-8//IGNORE" , $tag);
		$tag_array = explode(";",$tag);

	}
	$tag_id =  array();
	$c_id = array();
	foreach ($tag_array as $item){
		if(empty($item)) continue;
		$tag_id[] = $_tag->where("name like '%".$item."%'")->findAll();
	}
	if(!empty($tag_id)){
		
		foreach ($tag_id as $item){
			foreach ($item as $cell){
				if ( !empty($cell['tagid']) ){
					$tagid = $_tc -> where( "tagid=".$cell['tagid'] )->findAll();
					if (!empty($tagid)) {
						$c_id[] = $tagid;
					}
				}
			}
		}
	}else {
		//取最新的同栏目的信息
	}
	$result = array();
	if (!empty($c_id)) {
		$where = "";
		foreach ($c_id as $item){
			foreach ($item as $cell){
				$result[] = $cell['keyid'];
			}
		}
		if (!empty($attr)) {
			if (false !== strpos($attr, ',' )) {
				$attr = explode(',', $attr);
				$exp = array();
				foreach ($attr as $v) {
					$exp[] = " FIND_IN_SET('{$v}',attr) ";
				}
				$exp = implode(' AND ', $exp);
				$where .= " and (" .$exp .") ";
				unset($exp);
			} elseif (false !== strpos($attr, '|' )) {
				$attr = explode('|', $attr);
				$exp = array();
				foreach ($attr as $v) {
					$exp[] = " FIND_IN_SET('{$v}',attr) ";
				}
				$exp = implode(' OR ', $exp);
				$where .= " and (" .$exp .") ";
				unset($exp);
			}  elseif (false !== strpos($attr, '!' )) {
                 $attr = str_replace("!","",$attr);
				 $where  .= " and NOT FIND_IN_SET('{$attr}',attr) ";
			} else {
				 $where .= " and FIND_IN_SET('{$attr}',attr) ";
			}
			unset($attr);
		}
		
		$result = array_unique($result);
		$query = implode(",",$result);
		$query = str_replace("c-","",$query);
		if(!empty($cid)){
			$where .= " and cid !=". $cid ;
		}
		if ($isthumb) {
			$where .= " and thumb != ''";
		}
		if(!empty($find_catid)){
			$where .= " and catid in ($find_catid)";
		}
		$pagesize = isset($pagesize) ? $pagesize : 5;
		$sql = "select * from ".$db_pre."content where  cid in ($query)  ".$where ." order by cid DESC limit 0,".$pagesize;

		$_model = get_instance_of('Model');
		$c = D("Category");
		$data = $_model->query($sql);
		foreach ($data as $key =>$item) {
			$dir = $c -> where("catid=".$item['catid']) -> getField("catdir");
			if (empty($dir)) continue; 
			if ($tlength) $data[$key]['title'] = msubstr($item['title'],0,$tlength);
			
			$url = __ROOT__."/".$dir."/".$item['url'];
			$data[$key]['url'] = $url;
			$data[$key]['thumb'] = __ROOT__."/public/uploads/".$item['thumb'];
		}
		if (! empty ( $data )) {
			if (!empty($to)) {  //不适用其他模板输出，程序上直接循环输出
				$smarty->_tpl_vars[$to] = $data;				
				$html = '';
			} else { //使用其他模板输出
				$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
				$smarty->assign ( 'data', $data );
				!empty($tpl) && $html = $smarty->fetch ( $tpl );
				$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
				unset($_tpl_vars);
			}				
		}
	}
	return $html;
}