<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffwapmenu.php
// +----------------------------------------------------------------------
// | Date: 2013-06-17
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述:  自定义手机wap栏目标签
// +----------------------------------------------------------------------

function smarty_function_ffwapmenu($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	!empty ( $options ) && parse_str ( $options );
	$html = '';

	$_mobilemenu = D ('Mobilemenu','admin');
    $sort = !empty($sort)?$sort:'';

    $data = array();
    $data = $_mobilemenu->getMobileMenu($id,$sort);
	//print_r($data);
	if (!empty($data)) {
	   
        foreach($data as $k=>$v){
            
            $data[$k] = $v;
            $data[$k]['url'] = __ROOT__.'/'.$v['url'];
            
        }
       
	    //格式化数据  
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
