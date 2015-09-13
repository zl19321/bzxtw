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

function smarty_function_ffsite($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';

		if (! isset($catid)) {
			$html .= '缺少catid<br />';
		} else { 
			if(F("site_".$catid)) {
			   $data = F("site_".$catid);
			} else {
	        $cat = F("category_".$catid);
            $model = F("model_".$cat['modelid']);
			$tablename = $model['tablename'];
		    $data = D($tablename)->order("sort ASC,id ASC")->select();	
			F ( 'site_'.$catid, $data);
			}			
			if (! empty ( $data )) {				
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
	
	return $html;
}