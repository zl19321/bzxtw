<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: function.ffoptions.php
// +----------------------------------------------------------------------
// | Date: 2013-01-30
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述:  
// +----------------------------------------------------------------------

/**
 * smarty模板对象
 * @var 
 * 
 */
function smarty_function_ffoptions($params, &$smarty) {
	//将参数导入到单独变量中
	@extract ( $params );
	! empty ( $options ) && parse_str ( $options );
	$html = '';
	if (!$dbname) return '未指定表名';
    if (!$dbkey) return '未指定表名key';
    if (!$dbvalue) return '未指定表名value';
	
	//生成代码
    //判断是单表还是副表
    if($istable != 1){//如果是副表 加上表前缀  否则默认初始
        $dbname = 'Sidetable_'.$dbname;
    }
    $_c = M($dbname);
    $data = $_c->field($dbkey.','.$dbvalue)->select();


    if($classstatus == 'radio'){
        foreach($data as $k=>$v){
            $check = '';
            if($v[$dbvalue] == $value){
                $check = ' checked="checked" ';
            }
            $html .= '<input class="'.$class.'" type="radio" name="'.$name.'" '.$check.' value="'.$v[$dbvalue].'" />'.$v[$dbkey];
        }   
    }elseif($classstatus == 'checkbox'){
        foreach($data as $k=>$v){
            $check = '';
            if($v[$dbvalue] == $value){
                $check = ' checked="checked" ';
            }
            $html .= '<input class="'.$class.'" type="checkbox" name="'.$name.'" '.$check.' value="'.$v[$dbvalue].'" />'.$v[$dbkey];
        }   
    }else{
        $html = '<select class="'.$class.'" name="'.$name.'" id="'.$id.'" '.$ext.' >';
        
        foreach($data as $k=>$v){
            $select = '';
            if($v[$dbvalue] == $value){
                $select = ' selected="selected" ';
            }
            $html .= '<option '.$select.' value="'.$v[$dbvalue].'">'.$v[$dbkey].'</option>';
        }   
        $html .= '</select>';
    }
    
    
	//if (!empty($to)) {
	//	$smarty->_tpl_vars[$to] = $data;
	//	$html = '';
	//}

	
	return $html;
}