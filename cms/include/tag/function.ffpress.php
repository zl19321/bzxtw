<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffpress.php
// +----------------------------------------------------------------------
// | Date: 2011-9-7
// +----------------------------------------------------------------------
// | Author: Mark  
// +----------------------------------------------------------------------
// | 文件描述: 电子杂志
// +----------------------------------------------------------------------

function smarty_function_ffpress($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if ( empty ( $data )) {
		
		$p = intval($p ? $p : $smarty->_tpl_vars['p']);
		$p = ($p ? $p : 1);
		//初始化条件
		$catid = isset($catid) ? $catid : $smarty->_tpl_vars['cat']['catid'];
		$default_thumb = (empty($default_thumb) ? '' : __ROOT__ . '/public/statics/'.$default_thumb);
		$getField = "pid,sort,title,attr,thumb,description,create_time,update_time,url,catid";
		if (!$catid && defined(CATID)) {
			$catid = CATID;
		}
		if (! isset($catid)) {
			$html .= '缺少catid<br />';
		} else {
			$where = array ();
			//content表为 $table t1
			$db_pre = C('DB_PREFIX');
			$table = "{$db_pre}press";

			//TODO 基础表已经发生改变
			//查询结果，取出数据，根据field查询是否有扩展表中的数据
			import('admin.Model.ModelFieldModel');
			$base_field = ModelFieldModel::$baseFields;

			
			$where['`catid`'] = $catid;
			//发布的内容才显示
			$where['status'] = '9';
			
    		$pagesize = isset($pagesize) ? $pagesize : 12;
    	    $start = $pagesize*($p-1);
			
			//TODO 添加对文档属性筛选的支持 条件有:与、或、非
			// 示例： attr="hot,top"(与) attr="hot|top"(或)  attr="!top"(非)
			if (!empty($attr)) {
				if (false !== strpos($attr, ',' )) {
					$attr = explode(',', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " FIND_IN_SET('{$v}',attr) ";
					}
					$exp = implode(' AND ', $exp);
					$where['_string']  .= " (" .$exp .") ";
					unset($exp);
				} elseif (false !== strpos($attr, '|' )) {
					$attr = explode('|', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " FIND_IN_SET('{$v}',attr) ";
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

			if ($field) { //添加扩展字段
    		    $getField = $field . "," . $getField;
    		}

			$_p = D ( 'Press');
			$pageurl = rtrim($category_data['url'],'/') . 'index_{page}.html';
			$data['count'] = $_p->table($table)->where ( $where )->field ( $getField )->count ();
			$data['info'] = $_p->table($table)->where ( $where )->field ( $getField )->order ( $order )->limit ( $start . ',' . $pagesize)->findAll ();
			$data ['pages'] = multi($data['count'], $p, $pageurl, $pagesize, isset($goto) ? (boolean)$goto : false); //分页html
		
			
			if (! empty ( $data )) {
				//数据格式化
				foreach ($data['info'] as $k=>$v) {
					if ($tlength) $data['info'][$k]['title'] = msubstr($v['title'],0,$tlength, $charset="utf-8");
					if ($dlength) $data['info'][$k]['description'] = msubstr($v['description'],0,$dlength);
					if (empty($v['thumb'])) $data['info'][$k]['thumb'] = $default_thumb; //默认缩略图
					else  $data['info'][$k]['thumb'] = __ROOT__ . "/" . C("UPLOAD_DIR") . $data['info'][$k]['thumb'];
					$current_category_data = F ('category_'.$v['catid']);
					$data['info'][$k]['category_name'] = $current_category_data['name'];
				}
				//将已经使用过的数据保存在指定的key中，以便在下一次调用中不重复出现该数据
				if (!empty($tpl_id)) $smarty->_tpl_vars['__' . $tpl_id] = $data;

				if ($firstpage && $smarty->_tpl_vars['p']>1) {
					$html = '';
				} elseif (!empty($to)) {  //不适用其他模板输出，程序上直接循环输出
					$smarty->_tpl_vars[$to] = $data;
					$html = '';
				} else { //使用其他模板输出
					$_tpl_vars = $smarty->_tpl_vars; //将已经存在模板变量保存起来
					if (!$data['info']) unset($data['info']);
					$smarty->assign ( 'data', $data );
					$html = $smarty->fetch (  !empty($tpl) ? $tpl : 'system/list/default.html' );
					$smarty->_tpl_vars = $_tpl_vars;  //恢复之前的模板变量
					unset($_tpl_vars);
				}
			}
		}
	}
	return $html;
}