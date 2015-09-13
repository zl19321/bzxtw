<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.fftable.php
// +----------------------------------------------------------------------
// | Date: 2011-9-7
// +----------------------------------------------------------------------
// | Author: Mark  
// +----------------------------------------------------------------------
// | 文件描述: 活动报名
// +----------------------------------------------------------------------


function smarty_function_ffactivity($params, &$smarty) {
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if (empty ( $data )) {
		$p = intval($p ? $p : $smarty->_tpl_vars['p']);
		$p = ($p ? $p : 1);
		$pagesize = intval($pagesize ? $pagesize : $smarty->_tpl_vars['pagesize']);
		//默认不显示未开始和已结束的活动
		$start = empty($start) ? 0 : 1;
		$end   = empty($end) ? 0 : 1;
		
		$default_thumb = (empty($default_thumb) ? '' : __ROOT__ . '/public/statics/'.$default_thumb);
		if (!isset($catid) && defined(CATID) ) {
			$catid = CATID;
		}
		//初始化条件
		$catid = intval(isset($catid) ? $catid : $smarty->_tpl_vars['cat']['catid']);
		if (! isset($catid)) {
			$html .= '缺少catid<br />';
		} else {
			$where = array ();

			//content表为 $table
			$db_pre = C('DB_PREFIX');
			$table = "{$db_pre}activity";

			//过滤已有列表数据（其他模板中指定了的，不在其他模板中出现的内容数据，查询的时候需要排除）
			if (isset($except)) {
				$except = explode(',', $except);
				$not_id = '';
				if (is_array($except)) {
					foreach ($except AS $e) {
						if (array_key_exists('__' . $e, $smarty->_tpl_vars) && is_array($smarty->_tpl_vars['__' . $e])) {
							foreach ($smarty->_tpl_vars['__' . $e]['info'] as $s) {
								$not_id .= $s['aid'] . ',';
							}
						}
					}
				}
				$not_id = rtrim($not_id, ',');
				if (!empty($not_id)) 
					$where['`aid`'] = array('not in', $not_id);
				unset($except,$not_id);
			}
			$where['catid'] = array('in',$catid);
			if ($start == 0){
				$where['start_time'] = array("elt",time());
			}
			if ($end == 0){
				$where['end_time'] = array("egt",time());
			}
			if ($isthumb) { //是否要求带有导图
				$where ['thumb'] = array ('neq', "" );
			}
			//初始化  $p 为当前页码
    	    $start = $pagesize*($p-1);
    		$pagesize = isset($pagesize) ? $pagesize : 12;

			//TODO 添加对文档属性筛选的支持 条件有:与、或、非
			// 示例： attr="hot,top"(与) attr="hot|top"(或)  attr="!top"(非)
			if (!empty($attr)) {
				if (false !== strpos($attr, ',' )) {
					$attr = explode(',', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " FIND_IN_SET('{$v}',.attr) ";
					}
					$exp = implode(' AND ', $exp);
					$where['_string']  .= " (" .$exp .") ";
					unset($exp);
				} elseif (false !== strpos($attr, '|' )) {
					$attr = explode('|', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " FIND_IN_SET('{$v}',.attr) ";
					}
					$exp = implode(' OR ', $exp);
					$where['_string']  .= " (" .$exp .") ";
					unset($exp);
				} elseif (false !== strpos($attr, '!' )) {
                     $attr = str_replace("!","",$attr);
					 $where['_string'] .= " AND NOT FIND_IN_SET('{$attr}',attr) ";
				} else {
					 $where['_string'] .= " FIND_IN_SET('{$attr}',attr) ";
				}
				unset($attr);
			}

    		$order = empty($order) ? "sort asc" : $order;

			//判断主栏目数据
			if (is_string($catid)) {
				$catids = explode(',',$catid);
			    $category_data = F ('category_'.$catids[0]);
			} else {
				$category_data = F ('category_'.$catid);
			}
			$_activity = D("Activity");
			
	    	$data ['count'] = $_activity->where ( $where )->count ();
	    	$data ['info'] = $_activity->where ( $where )->order ( $order )->limit ( $start . ',' . $pagesize )->select ();
		}
		    
		//构造内容栏目列表链接  $catid为多个的时候，取第一个			
		$pageurl = rtrim($category_data['url'],'/') . '/index_{page}.html';
		//$p 当前页码
		$data ['pages'] = multi($data['count'],$p,$pageurl,$pagesize,isset($goto) ? (boolean)$goto : false); //分页html
		if (!empty($data['info'])) {
			foreach ($data['info'] as $k=>$v) {
				if ($tlength) $data['info'][$k]['title'] = msubstr($v['title'],0,$tlength, $charset="utf-8");
				if ($dlength) $data['info'][$k]['description'] = msubstr($v['description'],0,$dlength);
				if (empty($v['thumb'])) $data['info'][$k]['thumb'] = $default_thumb; //默认缩略图
				$current_category_data = F ('category_'.$v['catid']);
				$data['info'][$k]['category_name'] = $current_category_data['name'];
			}
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