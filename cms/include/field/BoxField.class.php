<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: BoxField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:03
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 选项字段操作
// +----------------------------------------------------------------------
class BoxField implements FieldInterface {
	/**
	 * 字段类型名称
	 * @var string
	 */
	public $name = '选项';
		
	
	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public function add($field,$value,$config) {
		$arr = $this->format($config['option']);
		$valueArr = array();
		if (is_array($arr)) {
			foreach ($arr as $v) {
				$valueArr[] = $v['value'];
			}
		}
		/*  
		if (!in_array($value, $valueArr)) {
			return '';
		} else {
			return $value;
		}*/
		$result = array();  //修改日期  2011-7-20 马东
		if (is_array($value)) {
			foreach ($value as $t ){
				if (in_array($t, $valueArr)) {
					$result[] = $t;
				}
			}
			return implode(",",$result);
		}else {
			if (!in_array($value, $valueArr)) {
				return '';
			} else {
				return $value;
			}
		}
	}
	
	private function format($option) {
		$setting_option = explode ( "\n", $option );
		$return = array();
		if (is_array ( $setting_option )) {
			//获取值
			foreach ( $setting_option as $k => $v ) {
				$v = ltrim($v);				
				list ( $return [$k] ['title'], $return [$k] ['value'] ) = explode ( '|', trim($v) );
			}
		}
		return $return;
	}
	
	/**
	 * 选项
	 * 
	 * @param array $data 字段信息
	 */
	public function form($data = '') {
		$html = '';
		//获取值
		$data ['setting'] ['option'] = $this->format($data ['setting'] ['option']);

		if ($data ['setting'] ['option'] && !empty($data ['setting'] ['option'])) {			
			$name = 'info[' . $data ['field'] . ']'; //字段名
			$value = isset($data ['value']) ? $data ['value'] : $data ['setting'] ['defaultvalue']; //选定值
            //fangfa 2013-1-2 下拉框副表副值
            if(empty($data['dbname'])){
			     $arr = &$data ['setting'] ['option']; //可选值,形如： array( array($title,$value),array($title,$value),array($title,$value) )
            }else{
                 $_sideTable = M($data['dbname']);
                 $returnHtml = $_sideTable->field("{$data['dbkey']},{$data['dbvalue']}")->select();
                 //echo $_sideTable->getLastSql();
                 foreach($returnHtml as $k=>$v){
                    $arr[$k]['value'] = $v[$data['dbvalue']];
                    $arr[$k]['title'] = $v[$data['dbkey']];
                 }
            }
           
//			$extra = &$data ['formattribute']; //元素额外属性
//			$extra .= ' class="'.$data['css'].' '.($data['required'] ? 'required' : "").'" ';
			$extra = &$data ['attribute']; //元素额外属性
//			$data['errortips'] && $extra .= ' title="'.$data['errortips'].'" ';
			//生成对应的html代码
			if ($data ['setting'] ['boxtype'] == 'radio') { //单选框组
				$html .= Html::radio_group ( $name, $arr, $value, $extra );
			}
			if ($data ['setting'] ['boxtype'] == 'checkbox') { //复选框	
				
				if(count($arr)>1){ //多个复选框组
					if (! is_array ( $data ['value'] )) {
						$data ['value'] = explode ( ',', $data ['value'] );
					}
					if (strstr($value,",")) {
						$value = explode(",",$value);
					}
					$html .= Html::checkbox_group ( $name, $arr, $value, $extra );
				}else if(count($arr) == 1){ //单个复选框
					if ($arr [0] ['value'] == $value) {
						$checked = true;
					} else {
						$checked = false;
					}
					$html .= Html::checkbox ( $name, $arr [0] ['value'], $checked, $arr [0] ['title'], $extra );
				}else{ //没有数据时
					$html .= "";
				}
				
// 				if (count ( $data ['setting'] ['option'] ) > 1) { //多个复选框组
// 					if (! is_array ( $data ['value'] )) {
// 						$data ['value'] = explode ( ',', $data ['value'] );
// 					}
// 					if (strstr($value,",")) {
// 						$value = explode(",",$value);
// 					}
// 					$html .= Html::checkbox_group ( $name, $arr, $value, $extra );
// 				} else if (count ( $data ['setting'] ['option'] ) == 1) { //单个复选框
// 					if ($arr [0] ['value'] == $value) {
// 						$checked = true;
// 					} else {
// 						$checked = false;
// 					}
// 					$html .= Html::checkbox ( $name, $arr [0] ['value'], $checked, $arr [0] ['title'], $extra );
// 				}
			}
			if ($data ['setting'] ['boxtype'] == 'select') { //单选下拉列表
				$html .= Html::select ( $name, $arr, $value, $extra );
			}
			if ($data ['setting'] ['boxtype'] == 'multiple') { //多选下拉列表
				if (! is_array ( $data ['value'] )) {
					$data ['value'] = explode ( ',', $data ['value'] );
				}
				$html .= Html::multiple ( $name, $arr, $value, $extra );
			}
			if (! empty ( $html ))
				return $html;
		}
		return false;
	}
	
	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		if ($config ['boxtype'] == 'radio') {
			$radio_checked = 'checked';
			$checkbox_checked = '';
			$select_checked = '';
			$multiple_checked = '';

		} else if ($config ['boxtype'] == 'checkbox') {
			$radio_checked = '';
			$checkbox_checked = 'checked';
			$select_checked = '';
			$multiple_checked = '';
		
		} else if ($config ['boxtype'] == 'select') {
			$radio_checked = '';
			$checkbox_checked = '';
			$select_checked = 'checked';
			$multiple_checked = '';
		
		} else if ($config ['boxtype'] == 'multiple') {
			$radio_checked = '';
			$checkbox_checked = '';
			$select_checked = '';
			$multiple_checked = 'checked';
		
		} else {			
			$config ['defaultvalue'] = '';
			
			//默认
			$radio_checked = 'checked';
			$checkbox_checked = '';
			$select_checked = '';
			$multiple_checked = '';
		}
        
        $_model = M('Model');
        $sidetable = $_model->where('tablename like "%sidetable_%"')->select();
        $modelHtml = '<option value="0">-选择副表-</option>';
        foreach($sidetable as $k=>$v){
            $modelHtml .= '<option value="'.$v['modelid'].'">'.$v['name'].'</option>';
        }
        //fangfa 2012-12-30 重写整个选项字段html生成，添加副表挂靠功能及预览功能
		${'fieldtype_'.$config['fieldtype']} = 'selected';
		$html = '
		  <table cellpadding="2" cellspacing="1" onclick="javascript:$(\'#minlength\').val(0);$(\'#maxlength\').val(255);">
            <script>
               function changesidetable(values){
                    if(values == 1){
                        $("#sidetablebox").show();
                        $("#options").removeClass("required");
                        $("#sidetablebox2").hide();
                        $("#sidetablebox3").hide();
                    }else if(values == 2){
                        $("#sidetablebox").hide();
                        $("#sidetablebox2").show();
                        $("#sidetablebox3").hide();
                    }else if(values == 3){
                        $("#sidetablebox").hide();    
                        $("#sidetablebox2").hide();
                        $("#options").removeClass("required");
                        $("#sidetablebox3").show();
                    }
               }
               
               function changemodelid(){
                
                    $.post("admin.php?m=fmodel&a=values",{modelid:$("#sidetabledb").val()},function(data){
                        
                        $("#sidetablevalue").html(data);
                        $("#sidetablekey").html(data);
                        
                    });
                    
                    
               }
               
               function previewSelect(i){
                
                    var selectClassName ; 
                
                    $("#selectclass input").each(function(){
                        
                        if($(this).attr("checked") == true){
                            selectClassName = $(this).val();
                        }
                    })
                
                    if(i == 1){
                        $.post("admin.php?m=fmodel&a=previewselect&choice="+i,{modelid:$("#sidetabledb").val(),dbkey:$("#sidetablekey").val(),dbvalue:$("#sidetablevalue").val(),classname:selectClassName},function(data){
                            $("#previewbox"+i).html(data);
                        });    
                    }else{
                        $.post("admin.php?m=fmodel&a=previewselect&choice="+i,{modelid:$("#sidetabledb1").val(),dbkey:$("#dbkey1").val(),dbvalue:$("#dbvalue1").val(),classname:selectClassName},function(data){
                            $("#previewbox"+i).html(data);
                        });   
                    }
                    
                
               }
               
            </script>
            <tr>
              <td>副表挂靠</td>
              <td>
                <input type="radio" name="sidetable" onclick="changesidetable(2);" checked="checked" value="0" >不挂靠副表<input type="radio" name="sidetable" onclick="changesidetable(1);" value="1" >挂靠副表<input type="radio" name="sidetable" onclick="changesidetable(3);" value="2" >指定表挂靠
              </td>
            </tr>     
            <tr id="sidetablebox" style="display:none;">
              <td>关联信息</td>
              <td>
                <table>
                    <tr>
                        <td width="150">挂靠副表表名：</td>
                        <td>
                            <select name="sidetabledb" id="sidetabledb" onchange="changemodelid();">'.$modelHtml.'</select>
                        </td>
                    </tr>
                    <tr>
                        <td>挂靠副表value：</td>
                        <td><select name="info[dbvalue]" id="sidetablevalue"></select></td>
                    </tr>
                    <tr>
                        <td>挂靠副表key：</td>
                        <td><select name="info[dbkey]" id="sidetablekey"></select></td>
                    </tr>
                    <tr>
                        <td><input type="button" value="预览" onclick="previewSelect(1);" /></td>
                        <td id="previewbox1"></td>
                    </tr>
                </table>
                
              </td>
            </tr>       
			<tr id="sidetablebox2"> 
		      <td>选项列表</td>
		      <td><textarea name="info[setting][option]" rows="2" cols="20" id="options" style="height:100px;width:200px;" class="required">' . $config ['option'] . '</textarea></td>
		    </tr>
            <tr id="sidetablebox3" style="display:none;">
              <td>关联信息</td>
              <td>
                <table>
                    <tr>
                        <td width="150">挂靠表表名：fangfa_</td>
                        <td>
                            <input name="sidetabledb1" id="sidetabledb1" class="input" />
                        </td>
                    </tr>
                    <tr>
                        <td>挂靠表value：</td>
                        <td><input name="info[dbvalue1]" class="input" id="dbvalue1" /></td>
                    </tr>
                    <tr>
                        <td>挂靠表key：</td>
                        <td><input name="info[dbkey1]" class="input" id="dbkey1" /></td>
                    </tr>
                    <tr>
                        <td><input type="button" value="预览" onclick="previewSelect(2);" /></td>
                        <td id="previewbox2"></td>
                    </tr>
                </table>
                
              </td>
            </tr>   
			<tr> 
		      <td>选项类型</td>
		      <td id="selectclass">
			  <input type="radio" name="info[setting][boxtype]" value="radio" ' . $radio_checked . ' /> 单选按钮 <br />
			  <input type="radio" name="info[setting][boxtype]" value="checkbox" ' . $checkbox_checked . ' /> 复选框 <br />
			  <input type="radio" name="info[setting][boxtype]" value="select" ' . $select_checked . '  /> 下拉框 <br />
			  <input type="radio" name="info[setting][boxtype]" value="multiple" ' . $multiple_checked . ' /> 多选列表框
			  </td>
		    </tr>
			<tr> 
		      <td>字段类型</td>
		      <td>
			  <select name="info[setting][fieldtype]">
			  <option value="CHAR" '.${'fieldtype_CHAR'}.'>定长字符 CHAR</option>
			  <option value="VARCHAR" '.${'fieldtype_VARCHAR'}.'>变长字符 VARCHAR</option>
			  <option value="TINYINT" '.${'fieldtype_TINYINT'}.'>整数 TINYINT(3)</option>
			  <option value="SMALLINT" '.${'fieldtype_SMALLINT'}.'>整数 SMALLINT(5)</option>
			  <option value="MEDIUMINT" '.${'fieldtype_MEDIUMINT'}.'>整数 MEDIUMINT(8)</option>
			  <option value="INT" '.${'fieldtype_INT'}.'>整数 INT(10)</option>
			  </select>
			  </td>
		    </tr>
			<tr> 
		      <td>默认值</td>
		      <td><input type="text" name="info[setting][defaultvalue]" value="' . $config ['defaultvalue'] . '" size="40"></td>
		    </tr>

		</table>
		';
		return $html;
	}
	
	/**
	 * 创建物理数据表字段，选项
	 * 
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		if (! $data ['setting'] ['maxlength'])
			$maxlength = 10;
		if ($data ['setting'] ['fieldtype'] == 'INT') {
			$data ['setting'] ['defaultvalue'] = intval ( $data ['setting'] ['defaultvalue'] );
			if ($data ['setting'] ['defaultvalue'] > 2147483647)
				$data ['setting'] ['defaultvalue'] = 0;
			if ($maxlength > 10)
				$maxlength = 11;
		} else if ($data ['setting'] ['fieldtype'] == 'MEDIUMINT') {
			$data ['setting'] ['defaultvalue'] = intval ( $data ['setting'] ['defaultvalue'] );
			if ($data ['setting'] ['defaultvalue'] > 8388607)
				$data ['setting'] ['defaultvalue'] = 0;
			if ($maxlength > 6)
				$maxlength = 7;
		} else if ($data ['setting'] ['fieldtype'] == 'SMALLINT') {
			$data ['setting'] ['defaultvalue'] = intval ( $data ['setting'] ['defaultvalue'] );
			if ($data ['setting'] ['defaultvalue'] > 32767)
				$data ['setting'] ['defaultvalue'] = 0;
			if ($maxlength > 4)
				$maxlength = 5;
		} else if ($data ['setting'] ['fieldtype'] == 'TINYINT') {
			$data ['setting'] ['defaultvalue'] = intval ( $data ['setting'] ['defaultvalue'] );
			if ($data ['setting'] ['defaultvalue'] > 127)
				$data ['setting'] ['defaultvalue'] = 0;
			if ($maxlength > 2)
				$maxlength = 3;
        //fangfa 2012-12-29 字段长度定义char和varchar分别设置长度供副表使用        
		} else if ($data ['setting'] ['fieldtype'] == 'CHAR') {
				$maxlength = 32;
		} else if ($data ['setting'] ['fieldtype'] == 'VARCHAR') {
				$maxlength = 64;
		}
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "` 
				ADD `{$data['field']}` {$data['setting']['fieldtype']}( $maxlength )  NULL 
				DEFAULT '{$data['setting']['defaultvalue']}'
				COMMENT '{$data['name']}'";    
		return $model->query ( $sql );
	}
}

?>