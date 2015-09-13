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
// | 文件描述: 内容列表标签，不带分页，基础表别名t1，扩展表别名t2，统计表t3
// +----------------------------------------------------------------------

function smarty_function_fflist($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if ( empty ( $data )) {
		//初始化条件
		$catid = isset($catid) ? $catid : $smarty->_tpl_vars['cat']['catid'];
		$isthumb = intval($isthumb);
		$default_thumb = (empty($default_thumb) ? '' : __ROOT__ . '/public/statics/'.$default_thumb);

		if (!$catid && defined(CATID)) {
			$catid = CATID;
		}
		if (! isset($catid)) {
			$html .= '缺少catid<br />';
		} else { 
			$where = ' 1 ';
			//content表为 $table t1
			$db_pre = C('DB_PREFIX');
			$table = "{$db_pre}content AS t1";

			//TODO 基础表已经发生改变
			//查询结果，取出数据，根据field查询是否有扩展表中的数据
			import('admin.Model.ModelFieldModel');
			$base_field = ModelFieldModel::$baseFields;

			//过滤已有列表数据（其他模板中指定了的，不在其他模板中出现的内容数据，查询的时候需要排除）
			if (isset($except)) {
				$except = explode(',', $except);
				$not_id = '';
				if (is_array($except)) {
					foreach ($except as $e) {
						if (array_key_exists('__' . $e, $smarty->_tpl_vars) && is_array($smarty->_tpl_vars['__' . $e])) {
							foreach ($smarty->_tpl_vars['__' . $e] as $s) {
								$not_id .= $s['cid'] . ',';
							}
						}
					}
				}
				$not_id = rtrim($not_id, ',');
				if (!empty($not_id)) {
					$where .= ' AND t1.`cid` NOT IN ('.$not_id.') ';
				}
				unset($except,$not_id);
			}
			if (isset($magazine) && !empty($magazine)) {
				$where .= ' AND t1.`cid` IN ('.$magazine.') ';
				unset($magazine);
			}

			if (is_numeric($catid)) {  //数据获取条件，当前栏目及其子栏目的的数据
				if (!isset($child) || $child) {
			    	$category_data = F ('category_'.$catid);
			    	//判断是否取出包含子栏目，同模型栏目ID
			    	//过滤掉与父栏目不同模型的栏目
			    	foreach ($category_data['childrenidarr_self'] as $k=>$v) {
			    		if ($v != $catid) {
			    			$tmp_data = F('category_'.$v);
			    			if ($tmp_data['modelid'] != $category_data['modelid']) {
			    				unset($category_data['childrenidarr_self'][$k]);
			    			}
			    		}
			    	}
			    	$catid = implode(',', $category_data['childrenidarr_self']);
			    	unset($category_data);
			    }
			} elseif (is_string($catid)) {  //catid  TODO 多栏目选择
                $catids = explode(',',$catid);
                $catid_t = array();
                foreach($catids as $v) {
                    $t = F ('category_'.$v);
                    if (!isset($child) || $child) 
                    	$catid_t[] = $t['childrenids_self'];
                }
                if(!empty($catid_t)) {
                    $catid = implode(',',$catid_t);                    
                }
                unset($catid_t,$catids,$t);
            }
            
            
            if(!empty($notattr)){
                $_co = M( 'Content' );
                $_pa = explode('|',$notattr);
                $id = array();
                $id_str = '';
                foreach($_pa as $k=>$v){
                    
                    $_para = explode(',',$v);

                    $where .= " AND t1.`cid` not in (SELECT `cid` FROM ( SELECT `cid` FROM fangfa_content WHERE attr like '%".$_para[0]."%' AND `catid` in (".$catid.") limit 0,".$_para[1].") AS t )";
                    
                }                                
            }


			$where .= ' AND t1.`catid` IN ('.$catid.')';
            $where .= ' AND t1.`status` = 9';
			$_c = D ( 'Contentext','front' );

			if ($isthumb) { //是否要求带有导图
				$where .= ' AND t1.`thumb` != "" ';
			}
			
			$pagesize && $limit = $pagesize;
			//查询结果
			$_c->auto_format = true;


			//TODO 添加对文档属性筛选的支持 条件有:与、或、非
			// 示例： attr="hot,top"(与) attr="hot|top"(或)  attr="!top"(非)
			if (!empty($attr)) {
				if (false !== strpos($attr, ',' )) {
					$attr = explode(',', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " AND FIND_IN_SET('{$v}',t1.attr) ";
					}
					$exp = implode(' AND ', $exp);
					$where  .= " (" .$exp .") ";
					unset($exp);
				} elseif (false !== strpos($attr, '|' )) {
					$attr = explode('|', $attr);
					$exp = array();
					foreach ($attr as $v) {
						$exp[] = " AND FIND_IN_SET('{$v}',t1.attr) ";
					}
					$exp = implode(' OR ', $exp);
					$where  .= " (" .$exp .") ";
					unset($exp);
				} elseif (false !== strpos($attr, '!' )) {
                     $attr = str_replace("!","",$attr);
					 $where .= " AND NOT FIND_IN_SET('{$attr}',t1.attr) ";
				} else {
					 $where .= " AND FIND_IN_SET('{$attr}',t1.attr) ";
                    
				}

				unset($attr);
			}


			if ($field) { //检查是否有扩展表中的字段，基础表字段自动全部查询
    		    $join = false;
    		    $fields = explode(',',$field);
    		    $_fields = array();
    		    if (is_array($fields)) {
    		        foreach ($fields as $t) {
    		            $t = trim(str_replace('`','',$t));
    		            if($t == 'hits' || $t == 'comments') {  //统计表字段
    		            	$_fields[] = "t3.`{$t}`";
    		            } elseif (!in_array($t,$base_field)) { //扩展表字段
    		                $join = true;
    		                $_fields[] = "t2.`{$t}`";
    		            }
    		        }

    		        $field = implode(',',$_fields).",";
    		    }
    		    unset($_fields,$fields);
    		}
			$field .= "t1.* ";  //基础字段默认全部查询
    		//处理排序
    		if ($order) {
    			$order = explode(',', $order);
    			$_order = '';
    			foreach ($order as $v) {
    				$o = explode(' ', $v);
    				if (count($o) == 2 && in_array(strtolower($o[1]), array('desc', 'asc'))) {
    					$o[0] = trim(str_replace('`','',$o[0]));
    					if (in_array($o[0], $base_field)) {  //主表
    						$_order .= "t1.{$v},";
    					} elseif ($o[0] == 'hits' || $o[0] == 'commments') {  //点击，或评论排序
    						$_order .= 't3.'.$v . ',';
    					} else {  //扩展表
    						$_order .= 't2.'.$v . ',';
    					}
    				}
    			}

    			$order = trim($_order, ',');
    		}

    		//额外SQL
    		if (!empty($ext_sql)) {
    			$join = true;
    			//$where['_string'] .= $ext_sql;
				$where.= $ext_sql;
    			unset($ext_sql);
    		}
			if ($join) {
			    if (is_string($catid)) {  // '1,2,3,4,5,6,7' 这类指定了多个栏目的，先获取其中一个栏目的modelid，如果是指定了单个栏目的，栏目信息已经在上面获取到了
			        $catids = explode(',',$catid);
			        $category_data = F ('category_'.$catids[0]);
			    }

			    //判断栏目属性是否是链接或单页
			    if ($category_data['type'] != 'normal') {
			    	foreach ($catids AS $c_id) {
			    		$category_data = F ('category_'.$c_id);
			    		if ($category_data['type'] == 'normal') break;
			    	}
			    }
			    if ($category_data['type'] != 'normal') return '您要获取的栏目或子栏目没有内容模型，请刷新缓存试试！';

			    $model_data = F ('model_'.$category_data['modelid']);
			    $extend_table = $db_pre.$model_data['tablename'];

			    $join = "{$extend_table} AS t2 ON ";
		        $join .= "t1.cid=t2.cid ";

		        if (strpos($field, 'hits') !== false || strpos($field, 'comments') !== false || strpos($order, 'hits') !== false || strpos($order, 'comments') !== false) {
		        	$data = $_c->table($table)->join($join)->join("{$db_pre}content_count AS t3 ON t3.cid=t1.cid")->where ( $where )->field ( $field )->order ( $order )->limit ( $limit )->findAll ();
		        } else {
		        	$data = $_c->table($table)->join($join)->where ( $where )->field ( $field )->order ( $order )->limit ( $limit )->findAll ();
		        }
			} else {
			    //查询结果
			    if (strpos($field, 'hits') !== false || strpos($field, 'comments') !== false || strpos($order, 'hits') !== false || strpos($order, 'comments') !== false) {
		        	$data = $_c->table($table)->join("{$db_pre}content_count AS t3 ON t3.cid=t1.cid")->where ( $where )->field ( $field )->order ( $order )->limit ( $limit )->findAll ();
		        } else {
		        	$data = $_c->table($table)->where ( $where )->field ( $field )->order ( $order )->limit ( $limit )->findAll ();
		        }
			}
			if (! empty ( $data )) {
				//数据格式化
				foreach ($data as $k=>$v) {
				    if ($url){
				        $wap_url = explode('/',$data[$k]['url']);
                        $data[$k]['url'] = $url.'/'.$wap_url[count($wap_url)-2].'/'.$wap_url[count($wap_url)-1]; 
				    }
					if ($tlength) $data[$k]['title'] = msubstr($v['title'],0,$tlength);
					if ($dlength) $data[$k]['description'] = msubstr($v['description'],0,$dlength);
					if (empty($v['thumb'])) $data[$k]['thumb'] = $default_thumb;  //默认缩略图
					$current_category_data = F ('category_'.$v['catid']);
					$data[$k]['category_name'] = $current_category_data['name'];
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